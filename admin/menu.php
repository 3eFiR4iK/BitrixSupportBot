<?php
$aMenu[] = [
    'parent_menu' => 'global_menu_settings',
    'sort' => 3000,
    'text' => 'Боты поддержки',
    'title' => 'Боты поддержки',
    'icon' => '',
    'page_icon' => '',
    'items_id' => 'menu_delsis_support_bots',
    'items' => [
        [
            'text' => 'Список ботов',
            'url' => 'delsis_support_bots.php?lang='.LANGUAGE_ID,
            'more_url' => [],
            'title' => 'Список процессов',
        ],
    ],
];

return $aMenu;
