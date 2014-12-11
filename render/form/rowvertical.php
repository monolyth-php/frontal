<?php

namespace monolyth\render\form;
$class = strtolower(get_class($row));
$class = substr($class, strrpos($class, '\\') + 1);

?>
<tbody class="<?=$class?> mg">
<?php if ($label = $row->getLabel()) { ?>
    <tr>
        <th colspan="2"><?=$label?></th>
    </tr>
<?php } ?>
    <tr>
        <td colspan="2"><?=$row?></td>
    </tr>
</tbody>

