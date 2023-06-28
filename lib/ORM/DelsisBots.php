<?php


namespace Delsis\SupportBot\ORM;


use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Catalog\Access\Install\Role\Salesman;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;

class DelsisBotsTable extends DataManager
{
    public static function getTableName()
    {
        return 'delsis_bots';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new StringField('DOMAIN'),
            new TextField('APP_SETTINGS'),
            new StringField('CLIENT_ID'),
            new StringField('CLIENT_SECRET'),
            //new StringField('APPLICATION_TOKEN'),
            new BooleanField('IS_MASTER'),
            new StringField('STATUS'),
            new DatetimeField('CREATED_AT'),
            new DatetimeField('LAST_USED_AT'),
        ];
    }
}
