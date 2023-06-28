<?php


namespace Delsis\SupportBot\ORM;


use Bitrix\Bizproc\WorkflowTemplateTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;

class DelsisBotsChatTable extends DataManager
{
    public static function getTableName()
    {
        return 'delsis_bots_chat';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new IntegerField('BOT_ID'),
            new IntegerField('MASTER_BOT_ID'),
            new StringField('INTERNAL_CHAT_ID'),
            new StringField('EXTERNAL_CHAT_ID'),
            new StringField('INVITE_CODE'),

            new ReferenceField('EXT_BOT', DelsisBotsTable::class, [
                'this.BOT_ID' => 'refs.ID'
            ]),
            new ReferenceField('MASTER_BOT', DelsisBotsTable::class, [
                'this.MASTER_BOT_ID' => 'refs.ID'
            ])
        ];
    }
}
