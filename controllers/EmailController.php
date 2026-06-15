<?php

namespace humhub\modules\visitor\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\visitor\jobs\SendEmailJob;

class EmailController extends Controller
{
    public function actionSendEmailFlags($land, $supporter, $datum)
    {
        $email = 'flaggen@kautex-group.com'; // Beliebige E-Mail-Adresse, z. B. interner Verteiler
        $formattedDate = date('d.m.Y', strtotime($datum)) . ' bis 08:00h';

        $body = "Es wurde von " . $supporter . " ein Besuch aus dem Land - " . $land . " - gemeldet.<br><br>Bitte setzen Sie am " . $formattedDate . " die Flaggen für das Land -->" . $land . "";

        $title = "Visitor Flag announcement -am " . $formattedDate . "-";

        Yii::info('Die Funktion actionIndex wurde aufgerufen.', __METHOD__);

        // Job in die Queue stellen
        Yii::$app->queue->push(new SendEmailJob([
            'email' => $email,
            'body' => $body,
            'title' => $title,
        ]));

        Yii::$app->session->setFlash('success', 'Flaggen Auftrag erfolgreich in die Warteschlange gestellt.');

        return "Flaggen Auftrag erfolgreich in die Warteschlange gestellt.";
    }

    public function SendEmail($company, $supporter, $datum)
    {
        $email = "visitor.info@kautex-group.com";
        $link = "https://ktxin.kautex-group.com";
        
        // Datum in das gewünschte Format ändern
        $formattedDate = date('d.m.Y \u\m H:i\h', strtotime($datum));
        
        // HTML für den E-Mail-Body
        $body = "Es wurden Besucher von der Firma " . $company . " am " . $formattedDate . " von " . $supporter . " angemeldet.<br><br>Weitere Infos finden Sie in KTXinside:<br><br><a href='" . $link . "'>" . $link . "</a>";
        $title = "Visitor announcement -"  . $company . " am " . $formattedDate . "-";
    
        // Job in die Queue stellen
        Yii::$app->queue->push(new SendEmailJob([
            'email' => $email,
            'body' => $body,
            'title' => $title,
        ]));
    
        Yii::$app->session->setFlash('success', 'Test E-Mail-Auftrag erfolgreich in die Warteschlange gestellt.');
        return "Test E-Mail-Auftrag erfolgreich in die Warteschlange gestellt.";
    }
     
}