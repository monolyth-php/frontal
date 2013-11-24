<?php

namespace monolyth\render\form;
$class = strtolower(get_class($row));
$class = substr($class, strrpos($class, '\\') + 1);
if (!is_array($row)) {
    $row = [$row];
}

?>
<tr class="<?=$class?> mg">
<?php if (count($row) == 1 && $row[0] instanceof Checkbox) { ?>
    <td colspan="2"><?=$row[0]?></td>
<?php } else { ?>
    <th><?=$row[0]->getLabel()?></th>
    <td><?php

    foreach ($row as $i => $f) {
        if ($i) {
            echo $f->getLabel();
        }
        echo $f;
    }
}

?></td>
</tr>

