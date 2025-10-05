<?php

use Itb\Core\Modules\Options\Schema;
use Itb\Core\Modules\Options\SchemaTab;

return Schema::make()
    ->tab('general', 'Общие', 'Главные настройки', function (SchemaTab $tab) {
        $tab->select('DATE_FORMAT_SITE', 'Формат даты на сайте', [
            '0' => 'd.m.Y',
        ], default: 0)
            ->checkbox('SWITH_CATALOG_TYPES', 'Разделение каталога на типы', default: true);
    })->tab('catalog', 'catalog', 'Настройки каталога', function (SchemaTab $tab) {
        $tab->select('name', 'help', ['options'], 'label', false, 0)
            ->input('name2', 'help', 'label', '20', false, 'default');
    });
