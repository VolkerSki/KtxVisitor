<?php 
namespace humhub\modules\visitor;

use Yii;
use yii\helpers\Url;
use humhub\modules\visitor\widgets\VisitorWidget;
use humhub\modules\ui\menu\MenuLink;

class Module extends \humhub\components\Module

{
 
    public $sidebarSortOrder = 100;


    public function init()
    {

        parent::init();

    }

    public static function onSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $module = Yii::$app->getModule('visitor');
        $event->sender->addWidget(VisitorWidget::class, [], ['sortOrder' => $module->sidebarSortOrder]);
    }

    public static function onTopMenuInit($event): void
    {
        
        // /** @var TopMenu $topMenuWidget */
        // $topMenuWidget = $event->sender;
    
        // // Existierender Eintrag für Visitors
        // $topMenuWidget->addEntry(new MenuLink([
        //     'label' => Yii::t('base', 'KTX'),
        //     'icon' => 'wrench',
        //     'url' => ['/visitor/index'],
        //     'sortOrder' => 99999,
        //     'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'visitor' && Yii::$app->controller->id == 'index'),
        // ]));
    
    }   

    public function disable()
    {
        
        parent::disable();
        
    }

    public function uninstall()
    {

        Yii::$app->db->createCommand()->dropTable('visitors')->execute();
        $this->deleteDirectory(__DIR__);

    }

}
