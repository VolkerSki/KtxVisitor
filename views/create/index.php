<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
// use yii\jui\DatePicker;

?>

<style>
    .visitor-form {
        padding: 20px;
        margin: 20px;
    }
</style>

<div class="container">
<div class="visitor-form">

<?php $form = ActiveForm::begin([
    'options' => [
        'class' => 'form-horizontal',
        'data-pjax' => true,
    ],
    'enableAjaxValidation' => true,
    'validationUrl' => ['create/validate'], // Optional: Separate Validierungsmethode
]); ?>


    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'company')->textInput(['maxlength' => true])?>
            <?= $form->field($model, 'supervisor')->textInput(['maxlength' => true])?>
            <?= $form->field($model, 'location')->textInput(['maxlength' => true])?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'country')->textInput(['maxlength' => true])?>
            <?= $form->field($model, 'start')->textInput(['type' => 'datetime-local'])?>
            <?= $form->field($model, 'end')->textInput(['type' => 'datetime-local'])?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">    
            <?= $form->field($model, 'visitors')->textarea(['rows' => 6])?>
        </div>
        <div class="col-md-6">    
            <?= $form->field($model, 'remarks')->textarea(['rows' => 6])?>
        </div>
    </div>

    <div class="form-group">
        <div class="col-md-12">
            <?= Html::submitButton(Yii::t('VisitorModule.base', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
</div>