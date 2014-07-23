<?php

namespace monolyth\render;
$string = '';
switch ($code = isset($code) ? $code : '200') {
    case '200': $string = 'OK'; break;
    case '401': $string = 'Unauthorized'; break;
    case '404': $string = 'Not Found'; break;
    case '500': $string = 'Internal Server Error'; break;
}
mail('marijn@sensimedia.nl', 'debug', print_r($_SERVER, true).print_r($project, true));
header($string ? "HTTP/1.1 $code $string" : '', true, $code);
header("Content-type: application/json", true);
if (isset($_SERVER['HTTP_ORIGIN'])
    && in_array($_SERVER['HTTP_ORIGIN'], $project['origins'])
) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
} else {
    header("Access-Control-Allow-Origin: {$project['http']}");
}
header("Access-Control-Allow-Headers: X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
echo json_encode($data, JSON_NUMERIC_CHECK);

