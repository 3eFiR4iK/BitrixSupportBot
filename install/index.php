<?php

use Bitrix\Main\ModuleManager;

class Delsis_Supportbot extends CModule
{
    /** @var string Для загрузки в маркет */
    var $MODULE_ID = 'delsis.supportbot';

    function __construct()
    {
        $this->MODULE_ID = 'delsis.supportbot';
        $this->setVersionData();

        $this->MODULE_NAME = "Delsis Support Bot";
        $this->MODULE_DESCRIPTION = "Delsis Support Bot";

        $this->PARTNER_NAME = "DELSIS";
        $this->PARTNER_URI = "https://delsis.online/";

        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    private function setVersionData()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
    }

    function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        CModule::IncludeModule('delsis.supportbot');

        try {
            $connection = \Bitrix\Main\Application::getConnection();
            $root = \Bitrix\Main\Application::getDocumentRoot();
            CopyDirFiles(__DIR__ . '/bitrix', $root.'/bitrix', true, true);
            CopyDirFiles(__DIR__ . '/public', $root, true, true);
            $connection->startTransaction();
            if ($connection->isTableExists(\Delsis\SupportBot\ORM\DelsisBotsTable::getTableName())) {
                $connection->dropTable(\Delsis\SupportBot\ORM\DelsisBotsTable::getTableName());
            }

            if ($connection->isTableExists(\Delsis\SupportBot\ORM\DelsisBotsChatTable::getTableName())) {
                $connection->dropTable(\Delsis\SupportBot\ORM\DelsisBotsChatTable::getTableName());
            }

            \Delsis\SupportBot\ORM\DelsisBotsTable::getEntity()->createDbTable();
            \Delsis\SupportBot\ORM\DelsisBotsChatTable::getEntity()->createDbTable();

            $connection->commitTransaction();
        } catch (Exception $exception) {
            $connection->rollbackTransaction();

            echo $exception->getMessage() . "\n";
            return  false;
        }

        return true;
    }

    function DoUninstall()
    {
        CModule::IncludeModule('delsis.supportbot');
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }
}
