<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
$APPLICATION->SetTitle('Список процессов');
\Bitrix\Main\UI\Extension::load(["ui.buttons.icons", 'popup']);

CModule::IncludeModule('delsis.supportbot');
?>
    <style>
        .ui-ctl {
            margin-left: 0 !important;
        }

        .process-form {
            margin: 20px;
        }

        .process-form-field {
            display: flex;
            flex-direction: row;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .process-form form {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .process-form-field--params .ui-ctl-block {
            display: flex !important;
            flex-direction: row;
            gap: 10px;
        }

        .ui-ctl-block--multiple {
            display: flex !important;
            flex-direction: column;
            gap: 10px;
        }

        .ui-ctl-block--multiple .params{
            display: flex !important;
            flex-direction: column;
            gap: 10px;
        }
    </style>

<?php

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$manager = new \Delsis\SupportBot\BotManager();

if ($request->getRequestMethod() == 'POST' && $request->get('type') == 'changeCreds') {
    $arRequest = $request->toArray();
    $manager->changeCreds(
        $arRequest['id'],
        $arRequest['clientId'],
        $arRequest['clientSecret']
    );
}

if ($request->getRequestMethod() == 'POST' && $request->get('type') == 'delete') {
    $manager->deleteBot($request->get('id'));
}

if ($request->getRequestMethod() == 'POST' && $request->get('type') == 'changeStatus') {
    $manager->changeStatus($request->get('id'), $request->get('status'));
}


$botList = $manager->getBotsList();
foreach ($botList as $bot) {
    $actions = [
        [
            'text' => 'Список чатов',
            'onclick' => 'editPopup('. json_encode($bot) .')'
        ],
        [
            'text' => 'Изменить ключи',
            'onclick' => 'editPopup('. json_encode($bot) .')'
        ],
        [
            'text' => 'Удалить',
            'onclick' => 'deleteRow('. $bot['ID'] .')'
        ]
    ];

    if ($bot['STATUS'] !== 'WAIT_CREDS') {
        $actions[] = [
            'text' => $bot['STATUS'] == 'ACTIVE' ? 'Деактивировать' : 'Активировать',
            'onclick' => sprintf('changeStatus(%s, \'%s\')', $bot['ID'], $bot['STATUS'] == 'ACTIVE' ? 'INACTIVE' : 'ACTIVE')
        ];
    }

    switch ($bot['STATUS']) {
        case 'WAIT_CREDS':
            $bot['STATUS'] = '<span class="adm-lamp adm-lamp-in-list adm-lamp-yellow"></span>';
            break;
        case 'ACTIVE':
            $bot['STATUS'] = '<span class="adm-lamp adm-lamp-in-list adm-lamp-green"></span>';
            break;
        case 'INACTIVE':
            $bot['STATUS'] = '<span class="adm-lamp adm-lamp-in-list adm-lamp-red"></span>';
            break;
    }

    if ($bot['IS_MASTER']) {
        $bot['IS_MASTER'] = 'Да';
    } else {
        $bot['IS_MASTER'] = 'Нет';
    }

    $rows[$bot['ID']] = [
        'id' => $bot['ID'],
        'columns' => $bot,
        'actions' => $actions,
    ];
}

?>

<? if ($APPLICATION->LAST_ERROR): ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title">Ошибка</div>
                <?=$APPLICATION->LAST_ERROR?>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
<? endif;?>

<?php

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => 'MY_GRID_ID',
        'COLUMNS' => [
            [
                'id' => 'ID',
                'name' => 'ID',
                'default' => true,
            ],
            [
                'id' => 'DOMAIN',
                'name' => 'Домен',
                'default' => true,
            ],
            [
                'id' => 'CLIENT_ID',
                'name' => 'Код приложения (client_id)',
                'default' => true,
            ],
            [
                'id' => 'CLIENT_SECRET',
                'name' => 'Ключ приложения (client_secret)',
                'default' => true,
            ],
            [
                'id' => 'IS_MASTER',
                'name' => 'Мастер',
                'default' => true,
            ],
            [
                'id' => 'CREATED_AT',
                'name' => 'Дата создания',
                'default' => true,
            ],
            [
                'id' => 'LAST_USED_AT',
                'name' => 'Дата последнего использования',
                'default' => true,
            ],
            [
                'id' => 'STATUS',
                'name' => 'Статус',
                'default' => true,
            ],
        ],
        'ROWS' => $rows,
        'AJAX_MODE' => 'Y',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
    ]
);

ob_start();
?>

<div class="process-form">
    <form action="" method="POST" id="main-process-form">
        <div class="process-form-field">
            <label for="">Код приложения (client_id) <span style="color: red">*</span></label>
            <div class="ui-ctl ui-ctl-textbox ui-ctl-block">
                <input name="clientId" required type="text" class="ui-ctl-element">
            </div>
        </div>
        <div class="process-form-field">
            <label for="">Ключ приложения (client_secret) <span style="color: red">*</span></label>
            <div class="ui-ctl ui-ctl-textbox ui-ctl-block">
                <input name="clientSecret" required type="text" class="ui-ctl-element">
            </div>
        </div>

        <input type="hidden" name="type" value="save">
        <input type="hidden" name="id" value="0">
    </form>
</div>

<?php
$form = ob_get_clean();
?>
    <script>
        function createPopup() {
            const popup = new BX.PopupWindow('add-timline-process',  window.body, {
                content: '<?=CUtil::JSEscape($form)?>',
                offsetTop : 1,
                offsetLeft : 0,
                lightShadow : true,
                closeIcon : false,
                closeByEsc : false,
                overlay: {
                    backgroundColor: 'grey', opacity: '80'
                },
                buttons: [
                    new BX.PopupWindowButton({
                        text: "Сохранить",
                        className: "popup-window-button-accept",
                        events: {click: function() {
                                if (document.getElementById('main-process-form').checkValidity()) {
                                    document.getElementById('main-process-form').submit()
                                } else {
                                    alert('Заполните обязательные поля')
                                }
                            }}
                    }),
                    new BX.PopupWindowButton({
                        text: "Отменить",
                        className: "webform-button-link-cancel",
                        events: {click: function() {
                            this.popupWindow.destroy() // закрытие окна
                        }}
                    })
                ]
            })

            popup.show()
        }

        function addRow(paramName = '', value = '') {
            const paramsNode = document.querySelector('.process-form-field--params .params')
            const node = document.createElement('div')
            const count = document.querySelectorAll('.ui-ctl-block--multiple .params .ui-ctl-textbox').length
            node.classList = 'ui-ctl ui-ctl-textbox ui-ctl-block'
            node.innerHTML = '<input name="params['+ count +'][name]" type="text" value="'+ paramName +'" placeholder="имя парамса" class="ui-ctl-element">' +
                '<input name="params['+ count +'][value]" type="text" value="'+ value +'" placeholder="значение" class="ui-ctl-element">'

            paramsNode.append(node)
        }

        function editPopup(entity) {
            createPopup()

            document.querySelector('#main-process-form input[name="type"]').value = 'changeCreds'
            document.querySelector('#main-process-form input[name="id"]').value = entity.ID
            document.querySelector('#main-process-form input[name="clientId"]').value = entity.CLIENT_ID
            document.querySelector('#main-process-form input[name="clientSecret"]').value = entity.CLIENT_SECRET
        }

        function deleteRow(id) {
            const form = document.createElement("form");
            const idInput = document.createElement("input");
            const typeInput = document.createElement("input");

            form.method = "POST";

            idInput.value = id;
            idInput.name = "id";
            typeInput.value = 'delete';
            typeInput.name = "type";

            form.appendChild(idInput);
            form.appendChild(typeInput);

            document.body.appendChild(form);

            form.submit();
        }

        function changeStatus(id, status) {
            const form = document.createElement("form");
            const idInput = document.createElement("input");
            const typeInput = document.createElement("input");
            const statusInput = document.createElement("input");

            form.method = "POST";

            idInput.value = id;
            idInput.name = "id";
            typeInput.value = 'changeStatus';
            typeInput.name = "type";
            statusInput.value = status;
            statusInput.name = "status";

            form.appendChild(idInput);
            form.appendChild(typeInput);
            form.appendChild(statusInput);

            document.body.appendChild(form);

            form.submit();
        }
    </script>

<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
