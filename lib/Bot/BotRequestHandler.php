<?php

namespace Delsis\SupportBot\Bot;


use Bitrix\Main\Application;
use Delsis\SupportBot\BotManager;
use Delsis\SupportBot\Logger;

class BotRequestHandler
{
    public function handle(): void
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $botManager = new BotManager();
        $auth = $request->get('auth');

        if ($request->get('event') == 'ONAPPINSTALL') {
            RestSupportBot::register($auth, 'ru');
            return;
        }

        //Logger::create('bot')->debug('request', ['data' => $_REQUEST]);

        if ($auth['domain']) {
            $bot = $botManager->getBot($auth['domain']);
        } else {
            return;
        }

        if (!$bot) {
            return;
        }

        switch ($request->get('event'))
        {
            case 'ONIMBOTDELETE':
                $bot->unRegister();
                break;
            case 'ONIMBOTJOINCHAT':
                $bot->onJoinToChat();
                break;
            case 'ONIMBOTMESSAGEADD':
                $params = $request->get('data')['PARAMS'];
                $bot->onMessageAdd($params['MESSAGE'], $params['FROM_USER_ID'], $params['DIALOG_ID']);
                break;
        }
    }
}
