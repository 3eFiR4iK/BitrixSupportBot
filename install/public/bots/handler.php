<?php
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
define("NO_KEEP_STATISTIC", true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();

CModule::IncludeModule('delsis.supportbot');

try {
    $botHandler = new \Delsis\SupportBot\Bot\BotRequestHandler();
    $botHandler->handle();
} catch (Throwable $e) {
    \Delsis\SupportBot\Logger::create('bot')->error('Ошибка', ['data' => [$e->getMessage(), $e->getTrace()]]);
}



