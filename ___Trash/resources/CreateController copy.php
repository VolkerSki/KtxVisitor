<?php

namespace humhub\modules\visitor\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\visitor\models\Visitor;
use humhub\modules\visitor\controller\EmailController;


class CreateController extends Controller
{
    public function actionIndex()
    {
        $model = new Visitor();
        $emailController = new EmailController();

        if (Yii::$app->request->isGet) {
            Yii::$app->session->set('previousUrl', Yii::$app->request->referrer);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Besucher erfolgreich hinzugefügt!');
            $previousUrl = Yii::$app->session->get('previousUrl', ['index']);
            return $this->redirect($previousUrl);

            if (!empty($model->land)) {
                // $emailController = new EmailController('email', Yii::$app->module);
                $emailController->actionSendEmailFlags($model->land, $model->supervisor);
            }

        } else {
            Yii::$app->session->setFlash('error', 'Fehler beim Speichern: ' . json_encode($model->errors));
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionModal()
    {
        $model = new Visitor(); 
        $emailController = new EmailController();
    
        if (Yii::$app->request->isGet) {
            Yii::$app->session->set('previousUrl', Yii::$app->request->referrer);
        }
    
        if ($model->load(Yii::$app->request->post())) {
            Yii::debug('POST-Daten: ' . json_encode(Yii::$app->request->post()));
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Besucher erfolgreich gespeichert!');
    
                if (!empty($model->land)) {
                    // $emailController = new EmailController('email', Yii::$app->module);
                    $emailController->actionSendEmailFlags($model->land, $model->supervisor);
                }
    
                return $this->redirect(Yii::$app->session->get('previousUrl', ['index']));
            } else {
                Yii::$app->session->setFlash('error', 'Fehler beim Speichern: ' . json_encode($model->errors));
            }
        } else {
            Yii::$app->session->setFlash('error', 'Fehler beim Laden der Daten: ' . json_encode(Yii::$app->request->post()));
        }
    
        return $this->render('createModal', [
            'model' => $model,
        ]);
    }    
          
    public function actionUpdate($id)
    {
        $model = Visitor::findOne($id);

        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Der Besucher wurde nicht gefunden.");
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Besucher erfolgreich aktualisiert!');
            return $this->redirect(Yii::$app->session->get('previousUrl', ['index']));
        } else {
            Yii::$app->session->setFlash('error', 'Fehler beim Aktualisieren: ' . json_encode($model->errors));
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = Visitor::findOne($id);

        if (!$model) {
            throw new \yii\web\NotFoundHttpException("Der Besucher wurde nicht gefunden.");
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Besucher erfolgreich gelöscht!');
        } else {
            Yii::$app->session->setFlash('error', 'Fehler beim Löschen.');
        }

        return $this->redirect(['/dashboard']);
    }

    public function actionView($id)
    {
        $model = Visitor::findOne($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
