<?php

namespace monolyth\render\form;
$class = strtolower(get_class($row));
$class = substr($class, strrpos($class, '\\') + 1);

?>
<tr class="<?=$class?> mg">
    <td colspan="2"><?=$row?></td>
</tr>

