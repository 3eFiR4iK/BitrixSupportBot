<?php

namespace Delsis\SupportBot;

use Bitrix\Main\Type\DateTime;
use Delsis\SupportBot\Bot\RestSupportBot;
use Delsis\SupportBot\ORM\DelsisBotsChatTable;
use Delsis\SupportBot\ORM\DelsisBotsTable;

class BotManager
{
    public function getBotById(int $id): ?RestSupportBot
    {
        $botConfig = DelsisBotsTable::getList([
            'filter' => [
                'STATUS' => 'ACTIVE',
                'ID' => $id
            ]
        ])->fetch();

        return $botConfig ? new RestSupportBot($botConfig) : null;
    }

    public function getBot(string $domain): ?RestSupportBot
    {
        $botConfig = DelsisBotsTable::getList([
            'filter' => [
                'STATUS' => 'ACTIVE',
                'DOMAIN' => $domain
            ]
        ])->fetch();

        return $botConfig ? new RestSupportBot($botConfig) : null;
    }

    public function getMasterBot(): RestSupportBot
    {
        $botConfig = DelsisBotsTable::getList([
            'filter' => [
                'STATUS' => 'ACTIVE',
                'IS_MASTER' => true
            ]
        ])->fetch();

        return new RestSupportBot($botConfig);
    }

    public function saveConfig(array $config)
    {
        

        if ($bot = $this->getBot($config['DOMAIN'])) {
            return DelsisBotsTable::update($bot->getId(), $config);
        } else {
            $config['CREATED_AT'] = new DateTime();
            return DelsisBotsTable::add($config);
        }
    }

    public function changeCreds(int $botId, string $clientId, string $clientSecret)
    {
        DelsisBotsTable::update($botId, [
            'CLIENT_ID' => $clientId,
            'CLIENT_SECRET' => $clientSecret,
            'STATUS' => 'ACTIVE'
        ]);
    }

    public function changeStatus(int $botId, string $status)
    {
        DelsisBotsTable::update($botId, [
            'STATUS' => $status
        ]);
    }

    public function getBotsList(): array
    {
        return DelsisBotsTable::getList(['order' => ['ID' => 'DESC']])->fetchAll() ?? [];
    }

    public function deleteBot(int $botId)
    {
        $chats = DelsisBotsChatTable::getList([
            'filter' => [
                'BOT_ID' => $botId,
            ]
        ])->fetchAll();

        foreach ($chats as $chat) {
            DelsisBotsChatTable::delete($chat['ID']);
        }

        DelsisBotsTable::delete($botId);
    }

    public function getRepeatChatID(int $botId, $currentChatId, bool $isMaster): ?string
    {
        if ($isMaster) {
            $filter['MASTER_BOT_ID'] = $botId;
            $filter['INTERNAL_CHAT_ID'] = $currentChatId;
        } else {
            $filter['BOT_ID'] = $botId;
            $filter['EXTERNAL_CHAT_ID'] = $currentChatId;
        }

        $chats = DelsisBotsChatTable::getList([
            'filter' => $filter
        ])->fetch() ?? [];

        return $isMaster ? $chats['EXTERNAL_CHAT_ID'] : $chats['INTERNAL_CHAT_ID'];
    }

    public function subscribeChat($inviteCode, $currentChatId): bool
    {
        $chat = DelsisBotsChatTable::getList([
            'filter' => ['INVITE_CODE' => $inviteCode]
        ])->fetch();

        if (!$chat) {
            return false;
        }

        return DelsisBotsChatTable::update($chat['ID'], ['INTERNAL_CHAT_ID' => $currentChatId])->isSuccess();
    }

    public function addInviteChat(array $params)
    {
        $params['MASTER_BOT_ID'] = $this->getMasterBot()->getId();

        return DelsisBotsChatTable::add($params);
    }

    public function getRepeatBot(int $botId, $chatId, bool $isMaster): ?array
    {
        if ($isMaster) {
            $params = [
                'filter' => [
                    'MASTER_BOT_ID' => $botId,
                    'INTERNAL_CHAT_ID' => $chatId,
                ],
                'select' => ['REPEAT_BOT_ID' => 'BOT_ID']
            ];
        } else {
            $params = [
                'filter' => [
                    'BOT_ID' => $botId,
                    'EXTERNAL_CHAT_ID' => $chatId,
                ],
                'select' => ['REPEAT_BOT_ID' => 'MASTER_BOT_ID']
            ];
        }

        $bots = DelsisBotsChatTable::getList($params)->fetchAll();

        if (!$bots) {
            return null;
        }

        $res = [];
        $botIds = array_column($bots, 'REPEAT_BOT_ID');

        $botsData = DelsisBotsTable::getList(['filter' => ['ID' => $botIds]])->fetchAll();

        foreach ($botsData as $botData) {
            $res[] = new RestSupportBot($botData);
        }

        return $res;
    }

    public function updateTime(int $botId)
    {
        return DelsisBotsTable::update($botId, ['LAST_USED_AT' => new DateTime()]);
    }
}
