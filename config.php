<?php

// use humhub\modules\admin\permissions\ManageModules;
use humhub\modules\visitors\Module;
use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\ui\menu\MenuLink;
use humhub\widgets\TopMenu;

return [
    'id' => 'visitor',
    'class' => 'humhub\modules\visitor\Module',
    'namespace' => 'humhub\modules\visitor',
    'events' => [
        ['class' => TopMenu::class, 'event' => TopMenu::EVENT_INIT, 'callback' => ['humhub\modules\visitor\Module', 'onTopMenuInit']],
        ['class' => Sidebar::class, 'event' => Sidebar::EVENT_INIT, 'callback' => ['humhub\modules\visitor\Module', 'onSidebarInit']],
    ],
];
