<?php

/**
 * @package monolyth
 */

namespace monolyth;

class Confirm_Model extends core\Model
{
    use render\Url_Helper;
    use User_Access;

    public function getFreeHash($seed = null)
    {
        if (!isset($seed)) {
            $seed = implode('', $_SERVER);
        }
        $hash = null;
        try {
            while (!isset($hash)) {
                $hash = $this->hash(
                    // Concetanate these; that should be unique enough.
                    $seed.rand(10000, 99999).time()
                );
                self::adapter()->field(
                    'monolyth_confirm',
                    1,
                    ['hash' => $hash]
                );
                $hash = null;
            }
        } catch (adapter\sql\NoResults_Exception $e) {
            // Ok, found a free hash.
            return $hash;
        }
    }

    public function process($hash, $id = null)
    {
        if (!isset($id) && self::user()->id()) {
            $id = self::user()->id();
        }
        self::adapter()->beginTransaction();
        try {
            $o = self::adapter()->rows(
                'monolyth_confirm',
                '*',
                ['owner' => $id, 'hash' => $hash]
            );
        } catch (adapter\sql\NoResults_Exception $e) {
            self::adapter()->rollback();
            return 'unknown';
        }
        foreach ($o as $m) {
            // Cancel whole operation if one of the entries has expired.
            if (strtotime($m['datevalid']) < time()) {
                return $this->cancel('outdated', $hash);
            }

            // Parse entry and apply.
            $fn = 'update';
            $fields = [$m['fieldname'] => ''];
            $value =& $fields[$m['fieldname']];
            $where = [sprintf($m['conditional'], $m['owner'])];

            // The following operations are supported:
            // = : simply set the field.
            // DEL : do a delete where conditional matches
            // |= : perform field |= value on bitfields.
            // &~ : perform field = field & ~value on bitfields.
            switch (strtolower($m['operation'])) {
                case '=': $value = $m['newvalue']; break;
                case 'del': $fn = 'delete'; break;
                case '|=':
                    $value = [sprintf(
                        "%s | '%d'",
                        $m['fieldname'],
                        $m['newvalue']
                    )];
                    break;
                case '&~':
                    $value = [sprintf(
                        "%s & ~%d",
                        $m['fieldname'],
                        $m['newvalue']
                    )];
                    break;
            }

            try {
                switch ($fn) {
                    case 'delete':
                        self::adapter()->delete($m['tablename'], $where);
                        break;
                    case 'update':
                        self::adapter()->update(
                            $m['tablename'],
                            $fields,
                            $where
                        );
                        break;
                }
            } catch (adapter\sql\UpdateNone_Exception $e) {
                // 't Is been done already.
            } catch (adapter\sql\Exception $e) {
                // Generic database error; assume everything's invalid.
                self::adapter()->rollback();
                return 'database';
            }
        }
        self::adapter()->delete(
            'monolyth_confirm',
            ['hash' => $hash, 'owner' => $id]
        );
        self::adapter()->commit();
        return null;
    }

    private function cancel($msg, $hash)
    {
        self::adapter()->rollback();
        self::adapter()->beginTransaction();
        // Remove invalidated entry
        try {
            self::adapter()->delete(
                'monolyth_confirm',
                ['hash' => $hash, 'owner' => self::user()->id()]
            );
        } catch (adapter\sql\DeleteNone_Exception $e) {
            // Catch this just in case.
        }
        // Cleanup as well
        try {
            self::adapter()->delete(
                'monolyth_confirm',
                ['datevalid' => ['<' => self::adapter()->now()]]
            );
        } catch (adapter\sql\DeleteNone_Exception $e) {
            // That's okay, we're simply clean.
        }
        self::adapter()->commit();
        return $msg;
    }
}

