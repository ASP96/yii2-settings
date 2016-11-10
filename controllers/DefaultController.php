<?php

namespace yii2mod\settings\controllers;

use Yii;
use yii2mod\editable\EditableAction;
use yii2mod\settings\models\SettingModel;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * Class SettingController
 * @package yii2mod\settings\controllers
 */
class DefaultController extends Controller
{
    /**
     * @var string path to index view file, which is used in admin panel
     */
    public $indexView = '@vendor/yii2mod/yii2-settings/views/default/index';

    /**
     * @var string path to create view file, which is used in admin panel
     */
    public $createView = '@vendor/yii2mod/yii2-settings/views/default/create';

    /**
     * @var string path to update view file, which is used in admin panel
     */
    public $updateView = '@vendor/yii2mod/yii2-settings/views/default/update';

    /**
     * @var string search class name for settings search
     */
    public $settingSearchClass = 'yii2mod\settings\models\search\SettingSearch';

    /**
     * @var string settings model class name for CRUD operations
     */
    public $settingModelClass = 'yii2mod\settings\models\SettingModel';

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'post'],
                    'delete' => ['post']
                ]
            ]
        ];
    }

    /**
     * Return a list of actions
     *
     * @return array
     */
    public function actions()
    {
        return [
            'edit-setting' => [
                'class' => EditableAction::className(),
                'modelClass' => SettingModel::className(),
                'forceCreate' => false
            ]
        ];
    }

    /**
     * Lists all Settings.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = Yii::createObject($this->settingSearchClass);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render($this->indexView, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Setting.
     *
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = Yii::createObject($this->settingModelClass);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('yii2mod.settings', 'Setting has been created.'));
            return $this->redirect(['index']);
        } else {
            return $this->render($this->createView, [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Setting.
     *
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('yii2mod.settings', 'Setting has been updated.'));
            return $this->redirect(['index']);
        } else {
            return $this->render($this->updateView, [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Setting.
     *
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', Yii::t('yii2mod.settings', 'Setting has been deleted.'));
        return $this->redirect(['index']);
    }

    /**
     * Finds a Setting model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return SettingModel the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $settingModelClass = $this->settingModelClass;

        if (($model = $settingModelClass::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('yii2mod.settings', 'The requested page does not exist.'));
        }
    }
}