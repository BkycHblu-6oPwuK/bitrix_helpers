<?php

use App\User\User;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if(!User::current()->isAuthorized()){
    LocalRedirect('/');
}

$arDefaultUrlTemplates404 = [
    'index' => 'index.php',
];
$arDefaultVariableAliases404 = [];
$arDefaultVariableAliases = [];
$arComponentVariables = [];
$SEF_FOLDER = '';
$arUrlTemplates = [];

if ($arParams['SEF_MODE'] == 'Y') {
    $arVariables = [];
    $arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates(
        $arDefaultUrlTemplates404,
        $arParams['SEF_URL_TEMPLATES']
    );
    
    $arVariableAliases = CComponentEngine::MakeComponentVariableAliases(
        $arDefaultVariableAliases404,
        $arParams['VARIABLE_ALIASES']
    );
    
    $componentPage = CComponentEngine::ParseComponentPath(
        $arParams['SEF_FOLDER'],
        $arUrlTemplates,
        $arVariables
    );
    
    if (strlen($componentPage) <= 0) {
        $componentPage = 'index';
    }
    
    CComponentEngine::InitComponentVariables(
        $componentPage,
        $arComponentVariables,
        $arVariableAliases,
        $arVariables
    );
    $SEF_FOLDER = $arParams['SEF_FOLDER'];
} else {
    $SEF_FOLDER = '';
    $componentPage = 'index';
}

$arResult = [
    'FOLDER'        => $SEF_FOLDER,
    'URL_TEMPLATES' => $arUrlTemplates,
    'VARIABLES'     => $arVariables,
    'ALIASES'       => $arVariableAliases,
];

$this->IncludeComponentTemplate($componentPage);
