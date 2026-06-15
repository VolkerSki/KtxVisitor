<?php

namespace humhub\modules\visitor\controllers;

use Yii;

class VisitorController extends Controller
{

    public function actionIndex()
    {
        $visitors = Visitor::find()->all();
        return $this->render('index', []);
    }
}