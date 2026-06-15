<?php

namespace humhub\modules\visitor\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\visitor\models\Visitor;


class CreateController extends Controller
{
    public function actionIndex()
    {
        $model = new Visitor();

        if (Yii::$app->request->isGet) {
            Yii::$app->session->set('previousUrl', Yii::$app->request->referrer);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Besucher erfolgreich hinzugefügt!');
            // $previousUrl = Yii::$app->session->get('previousUrl', ['dashboard']);

            if (!empty($model->land)) {
                $this->sendEmail();
            }

                        return $this->redirect(['/dashboard']);

        } else {
            Yii::$app->session->setFlash('error', 'Fehler beim Speichern: ' . json_encode($model->errors));
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function actionModal()
    {
    
        if (Yii::$app->request->isGet) {
            Yii::$app->session->set('previousUrl', Yii::$app->request->referrer);
        }
    
        if ($model->load(Yii::$app->request->post())) {
            Yii::debug('POST-Daten: ' . json_encode(Yii::$app->request->post()));
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Besucher erfolgreich gespeichert!');
    
                if (!empty($model->land)) {
                    $this->sendEmail();
                }
    
                // return $this->redirect(Yii::$app->session->get('previousUrl', ['dashboard']));
                return $this->redirect(['/dashboard']);

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

    private function sendEmail()
    {
        Yii::$app->mailer->compose()
            ->setTo('volker@klumski.net')
            ->setFrom(['admin@klumski.net' => 'Test Email'])
            ->setSubject('Test E-Mail')
            ->setTextBody('Dies ist ein Test.')
            ->send();
        echo "E-Mail gesendet";
    }
}
