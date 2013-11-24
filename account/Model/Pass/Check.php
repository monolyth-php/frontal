<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Model;
use monolyth\User_Access;

class Check_Pass_Model extends Model
{
    public function __invoke($input, $compare, $salt = null, $salted = false)
    {
        if (!$salted) {
            try {
                list($hash, $pass) = explode(':', $compare, 2);
                $input = "$hash:".hash(
                    $hash,
                    $input.($salt ? $salt : '')
                );
            } catch (\ErrorException $e) {
                // Ok, no hash enabled or defined.
            }
        }
        return $input && $input == $compare ? null : 'nomatch';
    }
}

