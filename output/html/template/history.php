<?php

namespace monolyth;
header("Content-type: application/javascript", true);
ob_start();
extract(include 'output/html/template/body.php');
ob_end_clean();
$title = isset($title) ? $title : [];
$title = is_array($title) ? $title : [$title];
$historyStatus = isset($historyStatus) ? $historyStatus : null;
echo json_encode([
    'body' => base64_encode($content),
    'title' => utf8_encode(strip_tags(implode(' - ', $title))),
    'styles' => "$Css",
    'scripts' => $Script->getArrayCopy(),
    'status' => $historyStatus,
]);

