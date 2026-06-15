<?php

namespace humhub\modules\visitor\widgets;

use Yii;
use humhub\components\Widget;
// use yii\helpers\Html; // Hinzugefügt, um HTML-Helper zu verwenden
use humhub\modules\visitor\models\Visitor; // Hinzugefügt, um das Visitor Model zu verwenden

class VisitorWidget extends \yii\base\Widget
{
    public function run()
    {
        $title = "Visitors expected";
        // Daten aus der Datenbank holen
        $visitors = Visitor::find()->asArray()->all();
        
        return $this->render('showVisitors', [
            'title' => $title,
            'visitors' => $visitors,
        ]);
    }
}