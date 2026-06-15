<?php

namespace humhub\modules\visitor\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\visitor\models\Visitor;
use humhub\modules\visitor\controllers\EmailController;


class CreateController extends Controller
{
    public function actionModal()
    {
        $model = new Visitor();
    
        if (Yii::$app->request->isGet) {
            Yii::$app->session->set('previousUrl', Yii::$app->request->referrer);
        }
    
        if ($model->load(Yii::$app->request->post())) {
            Yii::debug('POST-Daten: ' . json_encode(Yii::$app->request->post()));
    
            // Validierung durchführen
            if ($model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Besucher erfolgreich gespeichert!');
    
                    // E-Mail Versand
                    $emailController = new EmailController('email', Yii::$app->module);
                    $emailController->SendEmail($model->company, $model->supervisor, $model->start);
    
                    if (!empty($model->country)) {
                        $emailController->actionSendEmailFlags($model->country, $model->supervisor, $model->start);
                    }
    
                    return $this->redirect(['/dashboard']);

                } else {
                    Yii::$app->session->setFlash('error', 'Fehler beim Speichern.');
                }
                
            } else {
                // Validierungsfehler: Rückgabe von JSON, wenn es sich um einen AJAX-Request handelt
                if (Yii::$app->request->isAjax) {
                    return $this->asJson([
                        'success' => false,
                        'errors' => $model->getErrors(),
                    ]);
                }
                Yii::$app->session->setFlash('error', 'Bitte füllen Sie alle Pflichtfelder aus.');
            }
        }
    
        return $this->renderAjax('createModal', [
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
            return $this->redirect(['/dashboard']);
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

    public function actionValidate()
    {
        $model = new Visitor();
    
        // Prüfen, ob es sich um einen POST-Request handelt und ob die Daten geladen werden können
        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            // Response-Format auf JSON setzen
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    
            // Yii stellt ActiveForm::validate bereit, um die Validierungsfehler zurückzugeben
            return \yii\widgets\ActiveForm::validate($model);
        }
    }

}
