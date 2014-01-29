<?php

/**
 * @package monolyth
 * @subpackage account
 */

namespace monolyth\account;
use monolyth\core\Model;
use monolyth\Project_Access;
use monolyth\User_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\adapter\sql\InsertNone_Exception;
use monolyth\render\Url_Helper;
use monolyth\Config;
use monolyth\Confirm_Model;
use monolyth\Language_Access;
use monolyth\render\Email_Access;

/**
 * Reset_Pass_Model, implementing core functionality and default invocation,
 * which is to confirm the new password by re-entering the account name.
 */
class Reset_Pass_Model extends Model
{
    use Url_Helper;
    use Project_Access;
    use User_Access;
    use Language_Access;
    use Email_Access;

    public function __construct()
    {
        parent::__construct();
    }

    public function __invoke(Forgot_Pass_Form $form)
    {
        if (!($auth = $this->auth($form))) {
            return 'unknown';
        }
        self::adapter()->beginTransaction();
        $config = Config::get('monolyth');
        $confirm = new Confirm_Model;
        if ($error = $this->confirm(
            $auth,
            $config->passResetMail,
            $confirm->getFreeHash($auth['id'].$auth['name']),
            $this->generate()
        )) {
            self::adapter()->rollback();
            return $error;
        }
        self::adapter()->commit();
        return null;
    }

    protected function confirm($auth, $mail, $hash, $pwrand)
    {
        $siteurl = self::project()['url'];
        if (!($url = $this->url(
            'monolyth/account/confirm_pass',
            ['id' => $auth['id'], 'hash' => $hash],
            true
        ))) {
            $url = $this->url(
                'monolyth/account/reset_pass',
                ['id' => $auth['id'], 'hash' => $hash],
                true
            );
        }
        try {
            $a = self::adapter();
            $week = $a::WEEK;
            if ($mail != 'monolyth\account\pass/send') {
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
                            .self::adapter()->interval($week, 1)
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
                            .self::adapter()->interval($week, 1)
                        ],
                    ]
                );
            }
            $email = self::email();
            $email->setSource($mail)
                  ->setVariables([
                      'name' => $auth['name'],
                      'url' => $url,
                      'newpass' => $pwrand,
                  ])
                  ->headers(['Reply-to' => "noreply@$siteurl"])
                  ->send($auth['email']);
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

