<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\Card;
use app\models\CardUsage;
use app\models\Service;
use app\models\Partner;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\db\Query;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;


class CardUsageController extends Controller
{

        public function behaviors()
          {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only'  => ['index','view','create','update','delete'],
                'rules' => [
                    // xem danh sách/chi tiết: cần đăng nhập
                    ['allow' => true, 'actions'=>['index','view'], 'roles' => ['@']],
                    // tạo/sửa: cần permission
                    ['allow' => true, 'actions'=>['create','update'], 'roles' => ['manageCard']],
                    // xoá: chỉ admin
                    ['allow' => true, 'actions'=>['delete'], 'roles' => ['admin']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /** Danh sách usage; có thể lọc theo card_id (tuỳ chọn) */
    public function actionIndex($card_id = null)
    {
        $query = CardUsage::find()->with(['card','partner','services'])
            ->orderBy(['used_at'=>SORT_DESC, 'id'=>SORT_DESC]);

        if ($card_id !== null) {
            $query->andWhere(['card_id' => (int)$card_id]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'card' => $card_id ? Card::findOne((int)$card_id) : null,
        ]);
    }

    /** Xem chi tiết một usage */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->render('view', ['model'=>$model]);
    }

    /**
     * Creates a new CardUsage model.
     * If creation is successful, the browser will be redirected to the 'view' page of the related Card.
     * @param int $card_id ID of the Card to which this usage belongs
     * @return mixed
     * @throws NotFoundHttpException if the related Card cannot be found
     */
    public function actionCreate($card_id)
    {
        $card = Card::findOne((int)$card_id);
        if (!$card) {
            throw new NotFoundHttpException('Không tìm thấy thẻ.');
        }

        $model = new CardUsage();
        $model->card_id    = $card->id;
        $model->used_at    = date('Y-m-d');
        $model->used_at_ui = date('d/m/Y');

        // CHỈ lấy đối tác & dịch vụ đã gắn với thẻ
        $partnerList = ArrayHelper::map($card->partners, 'id', 'name');
        $serviceList = ArrayHelper::map($card->services, 'id', 'name');

        // Map đối tác -> service_ids khả dụng (giao của dịch vụ thẻ & partner_service)
          $cardServiceIds = array_map('intval', array_keys($serviceList));
          $partnerIds     = array_map('intval', array_keys($partnerList));

          $rows = (new Query())
              ->select(['partner_id','service_id'])
              ->from('{{%partner_service}}')
              ->where(['partner_id' => $partnerIds])
              ->all();

          $partnerServiceMap = [];
          foreach ($rows as $r) {
              $pid = (int)$r['partner_id'];
              $sid = (int)$r['service_id'];
              if (in_array($sid, $cardServiceIds, true)) {
                  $partnerServiceMap[$pid][] = $sid;
              }
          }

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {

            $db = Yii::$app->db;
            $tx = $db->beginTransaction();
            try {
                // Khóa dòng thẻ để chống race condition
                $lockedCard = Card::findBySql(
                    'SELECT * FROM {{%card}} WHERE id=:id FOR UPDATE',
                    [':id' => $card->id]
                )->one();
                if (!$lockedCard) {
                    throw new NotFoundHttpException('Không tìm thấy thẻ.');
                }

                $available = max(0, (int)$lockedCard->value - (int)$lockedCard->used_value);
                if ((int)$model->amount > $available) {
                    $model->addError('amount', 'Số tiền sử dụng vượt quá số dư còn lại của thẻ.');
                    $tx->rollBack();
                    return $this->render('create', [
                        'card'        => $card,
                        'model'       => $model,
                        'partnerList' => $partnerList,
                        'serviceList' => $serviceList,
                        'partnerServiceMap'  => $partnerServiceMap,
                    ]);
                }

                // Lưu usage (model sẽ tự validate: ngày, partner/service thuộc thẻ, v.v.)
                if (!$model->save()) {
                    $tx->rollBack();
                    return $this->render('create', [
                        'card'        => $card,
                        'model'       => $model,
                        'partnerList' => $partnerList,
                        'serviceList' => $serviceList,
                        'partnerServiceMap'  => $partnerServiceMap,
                    ]);
                }

                // KHÔNG cập nhật Card ở đây nữa.
                // CardUsage::afterSave() đã cộng/trừ used_value & remaining_value trong cùng transaction.
                $tx->commit();

                Yii::$app->session->setFlash('success', 'Đã ghi sử dụng thẻ.');
                return $this->redirect(['/card/view', 'id' => $card->id]);

            } catch (\Throwable $e) {
                $tx->rollBack();
                throw $e;
            }
        }

        return $this->render('create', [
            'card'        => $card,
            'model'       => $model,
            'partnerList' => $partnerList,
            'serviceList' => $serviceList,
            'partnerServiceMap'  => $partnerServiceMap,
        ]);
    }

    /** Cập nhật usage (tính toán theo delta, chống race-condition giống create) */
    public function actionUpdate($id)
    {
        $model = CardUsage::findOne($id);
        if (!$model) throw new NotFoundHttpException('Không tìm thấy bản ghi.');

        $card = $model->card; // để lấy partner/service hợp lệ
        $partnerList = \yii\helpers\ArrayHelper::map($card->partners, 'id', 'name');
        $serviceList = \yii\helpers\ArrayHelper::map($card->services, 'id', 'name');

        // Map đối tác -> service_ids khả dụng (giao của dịch vụ thẻ & partner_service)
          $cardServiceIds = array_map('intval', array_keys($serviceList));
          $partnerIds     = array_map('intval', array_keys($partnerList));

          $rows = (new Query())
              ->select(['partner_id','service_id'])
              ->from('{{%partner_service}}')
              ->where(['partner_id' => $partnerIds])
              ->all();

          $partnerServiceMap = [];
          foreach ($rows as $r) {
              $pid = (int)$r['partner_id'];
              $sid = (int)$r['service_id'];
              if (in_array($sid, $cardServiceIds, true)) {
                  $partnerServiceMap[$pid][] = $sid;
              }
          }

        if ($model->load(\Yii::$app->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success','Đã cập nhật.');
            return $this->redirect(['/card/view','id'=>$card->id]);
        }

        return $this->render('update', compact('model','partnerList','serviceList','card','partnerServiceMap'));
    }

    /** Xoá usage (hoàn lại số dư theo afterDelete), bọc trong transaction + khoá thẻ */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $card  = $model->card;

        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            if ($card) {
                // Khoá thẻ trước khi xoá để afterDelete cập nhật an toàn
                $lockedCard = Card::findBySql(
                    'SELECT * FROM {{%card}} WHERE id=:id FOR UPDATE',
                    [':id' => $card->id]
                )->one();
                if (!$lockedCard) {
                    throw new NotFoundHttpException('Không tìm thấy thẻ.');
                }
            }

            $model->delete(); // CardUsage::afterDelete() sẽ hoàn lại used_value/remaining_value
            $tx->commit();

            Yii::$app->session->setFlash('success', 'Đã xoá ghi sử dụng.');
            return $this->redirect($card ? ['/card/view', 'id' => $card->id] : ['index']);

        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /** Tìm model dùng chung */
    protected function findModel($id): CardUsage
    {
        if (($m = CardUsage::find()->where(['id'=>(int)$id])->one()) !== null) {
            return $m;
        }
        throw new NotFoundHttpException('Bản ghi không tồn tại.');
    }
   
}
