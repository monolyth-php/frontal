<?php

namespace monolyth\render;

$children = function($rows, $id, $field = 'parent') {
    foreach ($rows as $row) {
        if ($row[$field] == $id) {
            return true;
        }
    }
    return false;
};
$defaults = [
    'id' => null,
    'field' => 'parent',
    'access' => function() { return true; },
];
foreach ($defaults as $name => $default) {
    if (!isset($$name)) {
        $$name = $default;
    }
};

$active = $hasactive = false;
$parse = function(
    $rows,
    $parent = null,
    $field = 'parent',
    $level = 0
) use(&$parse, $children, $url, $access, &$active, &$hasactive) {
    $out = '';
    $hasactive = false;
    foreach ($rows as $row) {
        if (!$access($row)) {
            continue;
        }
        if ($row[$field] != $parent) {
            continue;
        }
        $parts = explode('/', $row->uri);
        $class = ''; 
        while (!strlen($class) && $parts) {
            $class = array_pop($parts);
        }
        $active = false;
        if ($row->uri == $url('')) {
            if (explode('?', $_SERVER['REQUEST_URI'])[0] == $url('')) {
                $active = true;
                $hasactive = true;
            }
        } elseif ($row->uri
            && strpos($_SERVER['REQUEST_URI'], $row->uri) === 0
        ) {
            $active = true;
            $hasactive = true;
        }
        $cdata = '';
        if ($kids = $children($rows, $row['id'])) {
            $cdata = $parse($rows, $row['id'], $field, $level + 1);
        }
        $out .= sprintf(
            '<li class="%s%s">',
            $class ? "menu-$class" : "menu",
            $active || ($cdata && $hasactive) ? ' active': ''
        );
        if ($cdata) {
            $hasactive = false;
        }
        if ($kids) {
            $hlevel = $level + 2;
            $out .= "<h$hlevel>";
            if ($row->uri) {
                $out .= sprintf('<a href="%s">', $row->uri);
            }
            $out .= $row->txt;
            if ($row->uri) {
                $out .= '</a>';
            }
            $out .= "</h$hlevel>\n";
            $out .= $cdata;
        } else {
            $out .= sprintf(
                '<a href="%s"%s>%s</a>'."\n",
                $row->uri,
                $row->target,
                $row->txt
            );
        }
        $out .= "</li>\n";
    }
    if (!strlen($out)) {
        return;
    }
    return sprintf(
        <<<EOT
<ul class="%smenu">
    %s
</ul>
EOT
        ,
        str_repeat('sub', $level),
        $out
    );
};

?>
<nav<?=isset($id) ? ' id="'.$id.'"' : ''?>><?=$parse($items, null, $field)?></nav>

