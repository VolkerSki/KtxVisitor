<?php

namespace humhub\modules\visitor\models;

use Yii;
use yii\db\ActiveRecord;

class Visitor extends ActiveRecord
{
    public static function tableName()
    {
        return 'ktx_visitors';
    }

    // Regeln für die Validierung
    public function rules()
    {
        return [
            [['company', 'supervisor', 'visitors', 'start', 'location'], 'required'],
            [['user_id'], 'integer'],
            [['visitors', 'remarks'], 'string'],
            [['start', 'end'], 'safe'],
            [['company', 'supervisor', 'country', 'location'], 'string', 'max' => 255],
            [['user_id'], 'default', 'value' => Yii::$app->user->id],
        ];
    }

    // Labels für Attribute anpassen
    public function attributeLabels()
    {
        return [
            'company' => Yii::t('VisitorModule.base', 'Company') . $this->getRequiredStar('company'),
            'supervisor' => Yii::t('VisitorModule.base', 'Supervisor') . $this->getRequiredStar('supervisor'),
            'visitors' => Yii::t('VisitorModule.base', 'Visitors') . $this->getRequiredStar('visitors'),
            'start' => Yii::t('VisitorModule.base', 'Start') . $this->getRequiredStar('start'),
            'location' => Yii::t('VisitorModule.base', 'Location') . $this->getRequiredStar('location'),
            'country' => Yii::t('VisitorModule.base', 'Country'),
            'remarks' => Yii::t('VisitorModule.base', 'Remarks'),
            'end' => Yii::t('VisitorModule.base', 'End'),
        ];
    }
    
    private function getRequiredStar($attribute)
    {
        return $this->isAttributeRequired($attribute) ? ' *' : '';
    }
    

    // Vor dem Speichern die user_id setzen
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->user_id = Yii::$app->user->id;
            }
            return true;
        } else {
            return false;
        }
    }
}
?>
