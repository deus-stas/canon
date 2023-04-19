<?php

// Очистка кеша инфоблоков
$eventManager = \Bitrix\Main\EventManager::getInstance();
$methodRun = '\ALS\Project\CacheController::processingEvent';

$eventManager->addEventHandler('iblock', 'OnAfterIBlockUpdate',         $methodRun);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockPropertyAdd',    $methodRun);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockPropertyUpdate', $methodRun);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd',    $methodRun);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', $methodRun);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', $methodRun);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionAdd',    $methodRun);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionUpdate', $methodRun);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockSectionDelete', $methodRun);

$eventManager->addEventHandler('main', 'OnAdminContextMenuShow', '\ALS\Project\Base::adminViewButton');
$eventManager->addEventHandler('main', 'OnAfterUserLogin', '\ALS\Project\Base::checkUserOptions');

$eventManager->addEventHandler('main', 'OnBeforePhpMail', 'removeToFromMailHeaders');

function removeToFromMailHeaders($event) {
    $args = $event->getParameter('arguments');

    $headers = [];

    foreach (explode(PHP_EOL, $args->additional_headers) as $line) {
        list($name, $value) = explode(':', $line, 2);

        if ($name !== 'To') {
            $headers[] = $name . ':' . $value;
        }
    }

    $args->additional_headers = implode(PHP_EOL, $headers);

    $event->setParameter('arguments', $args);
}

