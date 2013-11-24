<?php

namespace monolyth\monad;
use monad;
use Language_Settings;
use monolyth\render\Url_Helper;
$url = new Url_Helper();

if ($form['status']->value == 0) {
    unset($self->admin->editors);
    $old = $form['content']->value;
    $form['content'] = new \monolyth\ui\Textarea('content');
    $form['content']->value = $old;
}
$h = new monad\Box_Helper();
$txt = $self->text('scaffold/back', $self->database);
$uri = isset($_GET['redir']) ? base64_decode($_GET['redir']) : $url(
    'monad/scaffold',
    [
        'module' => 'monolyth',
        'table' => 'text',
        'database' => $self->database,
    ]
);
$title = 'Update';
echo $h->using($title)
       ->icons('<a href="'.$uri.'" class="up" title="'
            .htmlentities(strip_tags($txt)).'">'.$txt.'</a>'
        )
       ->head();

echo $self->view('monolyth\ui\slice/table', compact('form'))
          ->chain('monolyth\ui\slice/form');
echo $h->using('')->foot();

if (isset($admin->editors) && $admin->editors):

?>
<script>
$(document).ready(function() {
<?php

foreach ($admin->editors as $id => $editor):
    $editor['contentsCss'] = "/monad/style/wysiwyg/"
                            ."{$module}_{$table}"
                            ."/$id.css";
    if ($scaffold):
        foreach ($scaffold as $data):
            if (isset($data['slug'])):
                $editor['contentsCss'] = "/monad/style/wysiwyg/"
                                        ."{$module}_{$table}"
                                        ."/$id/{$data['slug']}.css";
                break;
            endif;
        endforeach;
    endif;

?>
    $('#<?php echo "{$module}_{$table}-$id"
        ?>').monadEditor('<?php echo $language ?>', <?php
        echo json_encode($editor) ?>);
<?php   endforeach ?>
});
</script>
<?php

endif;
return compact('title');

