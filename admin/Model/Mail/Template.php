<?php

namespace monolyth\admin;
use monad\core\Model;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;
use monolyth\adapter\sql\DeleteNone_Exception;
use monolyth\render\form\Info;

class Template_Mail_Model extends Model
{
    public $requires = ['monolyth_mail_template'];

    public function save(Template_Mail_Form $form)
    {
        $id = isset($this['id']) ? $this['id'].$this['language'] : null;
        $data = [];
        foreach ($form as $key => $value) {
            if ($value instanceof Info) {
                continue;
            }
            $data[$key] = $value->value;
        }
        if (!$data) {
            return null;
        }
        try {
            if ($id) {
                $this->adapter->update(
                    'monolyth_mail_template',
                    $data,
                    ['CONCAT(id, language)' => $id]
                );
            } else {
                $this->adapter->insert('monolyth_mail_template', $data);
            }
        } catch (InsertNone_Exception $e) {
            return 'insert';
        } catch (UpdateNone_Exception $e) {
            return 'nochange';
        }
        return null;
    }

    public function delete()
    {
        try {
            $this->adapter->delete(
                'monolyth_mail_template',
                ['CONCAT(id, language)' => $this['id'].$this['language']]
            );
            return null;
        } catch (DeleteNone_Exception $e) {
            return 'delete';
        }
    }
}

