<?php

namespace monolyth\admin;
use monad\core\Model;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\adapter\sql\UpdateNone_Exception;

class Text_Model extends Model
{
    public $requires = ['monolyth_text', 'monolyth_text_i18n'];

    public function save(Text_Form $form)
    {
        $id = $this['id'];
        $text = [];
        $i18n = [];
        foreach ($form as $name => $value) {
            if (preg_match("@^(\w+)\[(\d+)\]$@", $name, $match)) {
                if (!isset($i18n[$match[2]])) {
                    $i18n[$match[2]] = [];
                }
                $i18n[$match[2]][$match[1]] = $value->value;
            } else {
                $text[$name] = $value->value;
            }
        }
        $changed = 0;
        if ($text) {
            try {
                if ($id) {
                    self::adapter()->update('monolyth_text', $text, compact('id'));
                } else {
                    self::adapter()->insert('monolyth_text', $text);
                    $id = self::adapter()->lastInsertId();
                }
                ++$changed;
                self::adapter()->update(
                    'monolyth_text',
                    ['usermodified' => self::user()->id()],
                    compact('id')
                );
            } catch (UpdateNone_Exception $e) {
            } catch (InsertNone_Exception $e) {
                return 'insert';
            }
        }
        foreach ($i18n as $language => $data) {
            if (!$data) {
                continue;
            }
            try {
                self::adapter()->update(
                    'monolyth_text_i18n',
                    $data,
                    compact('id', 'language')
                );
                ++$changed;
            } catch (UpdateNone_Exception $e) {
            }
        }
        return $changed ? null : 'nochange';
    }
}

