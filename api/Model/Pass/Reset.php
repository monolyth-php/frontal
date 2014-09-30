<?php

/**
 * @package monolyth
 * @copyright 2014
 * @subpackage api
 */

namespace monolyth\api;
use monolyth\core\Model;
use monolyth\User_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\Confirm_Model;
use monolyth\render\Email;
use monolyth\account\Forgot_Pass_Form;
use Project;

/**
 * Reset_Pass_Model, API-style. The first half of the confirm code is returned
 * by the API call, and hence only known to the requesting user. The second
 * half is sent to the email address supplied, but only if it matches an
 * existing account.
 */
class Reset_Pass_Model extends Model
{
    use User_Access;

    public function __invoke(Forgot_Pass_Form $form)
    {
        if (!($auth = $this->auth($form))) {
            return 'unknown';
        }
        self::adapter()->beginTransaction();
        $confirm = new Confirm_Model;
        if ($error = $this->confirm(
            $auth,
            $confirm->getFreeHash($auth['id'].$auth['name']),
            $this->generate()
        )) {
            self::adapter()->rollback();
            return $error;
        }
        self::adapter()->commit();
        return null;
    }

    protected function confirm($auth, $hash, $pwrand)
    {
        $siteurl = strtolower(Project::instance()['name']);
        try {
            $a = self::adapter();
            $hour = $a::HOUR;
            self::adapter()->insert(
                'monolyth_confirm',
                [
                    'owner' => $auth['id'],
                    'hash' => $hash,
                    'conditional' => "id = '%d'",
                    'tablename' => 'monolyth_auth',
                    'fieldname' => 'pass',
                    'operation' => '=',
                    'newvalue' => $pwrand,
                    'datevalid' => [
                         "NOW() + "
                        .self::adapter()->interval($hour, 1)
                    ],
                ]
            );
            $user = self::user();
            self::adapter()->insert(
                'monolyth_confirm',
                [
                    'owner' => $auth['id'],
                    'hash' => $hash,
                    'conditional' => "id = '%d'",
                    'tablename' => 'monolyth_auth',
                    'fieldname' => 'status',
                    'operation' => '|=',
                    'newvalue' => $user::STATUS_GENERATED_PASS,
                    'datevalid' => [
                         "NOW() + "
                        .self::adapter()->interval($hour, 1)
                    ],
                ]
            );
            $email = Email::instance();
            $email->setSource('monolyth\account\pass/reset')
                  ->setVariables([
                      'name' => $auth['name'],
                      'newpass' => $pwrand,
                      'code' => substr($hash, 3),
                  ])
                  ->headers(['Reply-to' => "noreply@$siteurl"])
                  ->send($auth['email']);
            $this['code'] = $hash;
            return null;
        } catch (InsertNone_Exception $e) {
            return 'error';
        }
    }

    protected function auth($form)
    {
        try {
            $where = ['LOWER(email)' => strtolower($form['email']->value)];
            if (isset($form['name'])) {
                $where += [
                    'LOWER(name)' => strtolower($form['name']->value),
                ];
            }
            return self::adapter()->row('monolyth_auth', '*', $where);
        } catch (NoResults_Exception $e) {
            return null;
        }
    }

    protected function generate()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz'
                .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                .'0123456789'
                .'!@#$%^&*()';
        $pwrand = '';
        while (strlen($pwrand) < 10) {
            $pwrand .= substr($chars, rand(0, strlen($chars) - 1), 1);
        }
        return $pwrand;
    }
}

