<?php

namespace monolyth\render;
$string = '';
switch ($code = isset($code) ? $code : '200') {
    case '200': $string = 'OK'; break;
    case '401': $string = 'Unauthorized'; break;
    case '404': $string = 'Not Found'; break;
    case '500': $string = 'Internal Server Error'; break;
}
header($string ? "HTTP/1.1 $code $string" : '', true, $code);
header("Content-type: application/json", true);
header("Access-Control-Allow-Origin: {$project['http']}");
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
$flags = JSON_NUMERIC_CHECK;
if (defined("JSON_PRETTY_PRINT")) {
    $flags |= JSON_PRETTY_PRINT;
}
$out = json_encode($data, $flags);
if (!defined("JSON_PRETTY_PRINT")) {
    $h = new utils\JSON_Helper();
    $out = $h($out)->format();
}
echo $out;

