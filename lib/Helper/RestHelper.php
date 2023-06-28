<?php

namespace Delsis\SupportBot\Helper;


class RestHelper
{
    public static function sendRestCommand($method, array $params = array(), array $auth = array())
    {
        $queryUrl  = 'https://' . $auth['domain'] . '/rest/' . $method;
        $queryData = http_build_query(array_merge($params, array('auth' => $auth['access_token'])));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_POST           => 1,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $queryUrl,
            CURLOPT_POSTFIELDS     => $queryData,
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);
        return $result;
    }
}
