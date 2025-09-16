<?php

namespace app\controllers;

use Yii;
use app\models\Card;
use app\models\CardSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Url;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

use yii\filters\AccessControl;


/**
 * CardController implements the CRUD actions for Card model.
 */
class CardController extends Controller
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * Lists all Card models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CardSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Card model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Card model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Card();
        // Luôn khoá used_value khi tạo mới
        $model->used_value = 0;
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Card model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $oldCode = $model->card_code;
        $oldUsed = (int)$model->used_value;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

          $model->card_code  = $oldCode;     // không cho đổi mã thẻ
          // KHÔNG cho form thay đổi used_value
          $model->used_value = $oldUsed;
          return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Card model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Card model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Card the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Card::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionShow($code = null, $id = null)
    {
        $this->layout = 'public';

        // TẮT CACHE cho nội dung động
        $res = \Yii::$app->response;
        $res->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $res->headers->add('Cache-Control', 'post-check=0, pre-check=0');
        $res->headers->set('Pragma', 'no-cache');

        $q = \app\models\Card::find()->with(['services','partners','partners.servicesAvailable','referral']);
        $model = $code ? $q->andWhere(['card_code'=>$code])->one()
                      : ($id ? $q->andWhere(['id'=>(int)$id])->one() : null);
        if (!$model) throw new \yii\web\NotFoundHttpException('Không tìm thấy thẻ.');
        return $this->render('show', ['model'=>$model]);
    }


    public function actionQr($code, $download = 0)
    {
        $absUrl = Url::to(['card/show','code'=>$code], true);
        $qr = QrCode::create($absUrl)->setEncoding(new Encoding('UTF-8'))->setSize(800)->setMargin(16);
        $png = (new PngWriter())->write($qr)->getString();

        \Yii::$app->response->format = Response::FORMAT_RAW;
        \Yii::$app->response->headers->set('Content-Type','image/png');
        \Yii::$app->response->headers->set('Content-Disposition', ($download?'attachment':'inline').'; filename="card-'.$code.'.png"');
        return $png;
    }

    public function actionQrPdf($code)
    {
        $absUrl = Url::to(['card/show','code'=>$code], true);
        $qr = QrCode::create($absUrl)->setEncoding(new Encoding('UTF-8'))->setSize(600)->setMargin(12);
        $pngB64 = base64_encode((new PngWriter())->write($qr)->getString());

        $html = '
        <div style="text-align:center;font-family:sans-serif;">
          <img src="data:image/png;base64,'.$pngB64.'" style="width:60mm;height:60mm;object-fit:contain;" />
          <div style="margin-top:8px;font-size:12pt;"><strong>Card: '.htmlspecialchars($code).'</strong></div>
          <div style="font-size:9pt;color:#555">'.$absUrl.'</div>
        </div>';

        $tmpDir = \Yii::getAlias('@runtime/mpdf');
        if (!is_dir($tmpDir)) @mkdir($tmpDir,0775,true);

        $mpdf = new Mpdf(['format'=>'A7','margin_top'=>6,'margin_bottom'=>6,'margin_left'=>6,'margin_right'=>6,'tempDir'=>$tmpDir]);
        $mpdf->WriteHTML($html);
        return $mpdf->Output('card-'.$code.'.pdf', Destination::DOWNLOAD);
    }
}
