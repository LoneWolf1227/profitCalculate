<?php

namespace Core;

abstract class Core{
    public static function BT24($method, array $params, $url){

        $queryUrl = $url.'/'.$method.'.json/';
        $queryData = http_build_query($params);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ));

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }

    public static function sendToBot($method,array $parameters, $token,$proxy = '',$proxyauth = '')
    {
        $url = "https://api.telegram.org/bot".$token. "/" . $method;

        if (!$curld = curl_init()) {
            exit;
        }
        //curl_setopt($curld, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        //curl_setopt($curld, CURLOPT_PROXY, $proxy);
        //curl_setopt($curld, CURLOPT_PROXYUSERPWD, $proxyauth);
        curl_setopt($curld, CURLOPT_POST, true);
        curl_setopt($curld, CURLOPT_POSTFIELDS, $parameters);
        curl_setopt($curld, CURLOPT_URL, $url);
        curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curld, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curld, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curld, CURLOPT_HEADER, 0);
        $output = curl_exec($curld);
        curl_close($curld);
        return $output;
    }

    public static function WLog($data, $title = '', $fileName = 'log.txt', $filePath = '/' )
    {
        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s") . "\n";
        $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents(getcwd() . $filePath.$fileName, $log, FILE_APPEND);
        return true;
    }

    public static function WriteVariableToFile($varName, $varContent)
    {
        $fileName = $varName.'.php';
        file_put_contents(
            $fileName,
            '<?php' . PHP_EOL . '$'.$varName.' = ' . var_export($varContent, true) . ';',
            LOCK_EX
        );
        return true;
    }
}
