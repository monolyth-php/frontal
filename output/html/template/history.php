<?php

namespace monolyth;
header("Content-type: application/javascript", true);
$template = new render\View('\template/body.php', $self);
$template($content);
extract($template->data());
$title = isset($title) ? $title : [];
$title = is_array($title) ? $title : [$title];
$historyStatus = isset($historyStatus) ? $historyStatus : null;
echo json_encode([
    'body' => base64_encode($content),
    'title' => utf8_encode(strip_tags(implode(' - ', $title))),
    'styles' => "$Css",
    'scripts' => ($scripts = $Script->getArrayCopy()) ? $scripts : [],
    'status' => $historyStatus,
]);

