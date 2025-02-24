<?php
// routes.php
return [
    '' => 'MessageController@index',

    'api/notifications' => 'MessageController@notification',
    'api/notifications/{id}' => 'MessageController@notification',
    'api/notifications/store' => 'MessageController@store',
    'api/notifications/update/{id}' => 'MessageController@update',
    'api/notifications/delete/{id}' => 'MessageController@delete',
    'api/notifications/toggle/{id}' => 'MessageController@toggleStatus',

    'log-messages' => 'messages-log',
    'api/log-messages' => 'LogMessageController@index',      // GET: daftar log messages (dipakai oleh DataTables)
    'api/log-messages/{id}/resend' => 'LogMessageController@resend',     // POST: resend log message berdasarkan ID
    'api/log-messages/store' => 'LogMessageController@store',      // POST: simpan log message baru

    'bot-settings' => 'bot-settings',
    'demo' => 'BotController@testDemo',
    'api/bot/updateToken' => 'BotController@updateToken',
    'api/bot/newestTransaction' => 'BotController@newestTransaction'
];
