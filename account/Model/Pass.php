<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Model;
use monolyth\User_Access;

class Pass_Model extends Model
{
    use User_Access;

    public function update($pass, $id = null)
    {
        $user = self::user();
        if (!isset($id)) {
            $id = $user->id();
        }
        try {
            $fields = compact('pass');
            $q = self::adapter()->row(
                'monolyth_auth',
                '*',
                compact('id')
            );
            $fields['salt'] = null;
            if ($hash = $this->hash()) {
                $fields['salt'] = $this->salt();
                $fields['pass'] = "$hash:".hash(
                    $hash,
                    $fields['pass'].$fields['salt']
                );
            }
            $fields[] = sprintf(
                "status = status & ~%d",
                $user::STATUS_GENERATED_PASS
            );
            self::adapter()->update(
                'monolyth_auth',
                $fields,
                compact('id')
            );
            $user->status($user->status() & ~$user::STATUS_GENERATED_PASS);
            $user->pass($fields['pass']);
            $user->salt($fields['salt']);
            return null;
        } catch (adapter\sql\UpdateNone_Exception $e) {
            return 'updating';
        }
    }

    public function hash()
    {
        $hashes = hash_algos();
        foreach (['whirlpool', 'sha512', 'sha1', 'md5'] as $hash) {
            if (in_array($hash, $hashes)) {
                return $hash;
            }
        }
        return null;
    }

    public function salt()
    {
        $setting = '255:abcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        $parts = explode(':', $setting, 2);
        $salt = '';
        for ($i = 0; $i < $parts[0]; $i++) {
            $salt .= substr($parts[1], rand(0, strlen($parts[1]) - 1), 1);
        }
        return $salt;
    }
}

