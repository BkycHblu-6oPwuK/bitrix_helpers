<?php
use App\Http\GlobalResult;

GlobalResult::addPageData([
    'items' => $arResult['ITEMS'],
    'pagination' => $arResult['PAGINATION'],
], 'catalogSection');