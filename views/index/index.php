
<?php

use humhub\widgets\Button;
use Yii;
use yii\helpers\Html;

$displayName = (Yii::$app->user->isGuest) ? Yii::t('VisitorModule.base', 'Guest') : Yii::$app->user->getIdentity()->displayName;

?>
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong><?= Yii::t('VisitorModule.base', 'KTX Tools') ?></strong> </div>
                <div class="panel-body">
                    <?= Button::primary(Yii::t('VisitorModule.base', 'Create Visitor'))
                        ->loader(false)
                        ->link(['/visitor/create'], true)
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>