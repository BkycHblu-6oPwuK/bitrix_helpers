<?php

use Beeralex\Core\Config\Schema;
use Beeralex\Core\Config\SchemaTab;

return Schema::make()
    ->tab('general', 'Общие', 'Главные настройки', function (SchemaTab $tab) {
        $tab->select('DATE_FORMAT_SITE', 'Формат даты на сайте', [
            '0' => 'd.m.Y',
        ], default: 0);
    })->tab('catalog', 'catalog', 'Настройки каталога', function (SchemaTab $tab) {
        $tab->select('name', 'help', ['options'], 'label', false, 0)
            ->input('name2', 'help', 'label', '20', false, 'default');
    });
