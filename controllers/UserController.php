<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                // optional: chỉ áp cho các action sau (đỡ intercept các route khác):
                'only' => ['index','view','create','update','delete','change-password'],
                'denyCallback' => function () {
                    throw new \yii\web\ForbiddenHttpException('Bạn không có quyền thực hiện thao tác này.');
                },
                'rules' => [
                    // 1) Cho phép mọi user đã đăng nhập đổi mật khẩu
                    [
                        'allow' => true,
                        'actions' => ['change-password'],
                        'roles' => ['@'],
                    ],
                    // 2) Toàn bộ CRUD user chỉ cho admin
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
   public function actionIndex()
    {
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->render('index', compact('searchModel','dataProvider'));
    }

    /**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';                // bắt buộc password khi tạo
        $model->status   = User::STATUS_ACTIVE;     // mặc định kích hoạt

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view','id'=>$model->id]);
        }
        $model->loadDefaultValues();
        return $this->render('create', ['model'=>$model]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view','id'=>$model->id]);
        }
        return $this->render('update', ['model'=>$model]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Chặn tự xoá chính mình & chặn xoá admin gốc (id=1 tuỳ chính sách)
        if ((int)$model->id === (int)Yii::$app->user->id) {
            Yii::$app->session->setFlash('error','Không thể xoá tài khoản đang đăng nhập.');
            return $this->redirect(['index']);
        }
        if ((int)$model->id === 1) {
            Yii::$app->session->setFlash('error','Không thể xoá tài khoản admin gốc.');
            return $this->redirect(['index']);
        }

        // Thu hồi toàn bộ role trước khi xoá
        $auth = Yii::$app->authManager;
        $auth->revokeAll($model->id);

        $model->delete();
        Yii::$app->session->setFlash('success','Đã xoá người dùng.');
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id'=>$id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionChangePassword()
    {
        $model = new \app\models\ChangePasswordForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->change()) {
            Yii::$app->session->setFlash('success','Đã đổi mật khẩu.');
            return $this->goHome();
        }
        return $this->render('change-password',['model'=>$model]);
    }

}
