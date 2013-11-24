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

/**
 * Reset_Pass_Model, implementing core functionality and default invocation,
 * which is to confirm the new password by re-entering the account name.
 */
class Reset_Pass_Model extends Model implements Project_Access, User_Access
{
    use Url_Helper;

    public function __invoke(Forgot_Pass_Form $form)
    {
        if (!($auth = $this->auth($form))) {
            return 'unknown';
        }
        $this->adapter->beginTransaction();
        if ($error = $this->confirm(
            $auth,
            $this->config->passResetMail,
            $this->confirm->getFreeHash($auth['id'].$auth['name']),
            $this->generate()
        )) {
            $this->adapter->rollback();
            return $error;
        }
        $this->adapter->commit();
        return null;
    }

    protected function confirm($auth, $mail, $hash, $pwrand)
    {
        $siteurl = $this->project['url'];
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
            $a = $this->adapter;
            $week = $a::WEEK;
            if ($mail != 'monolyth\account\pass/send') {
                $this->adapter->insert(
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
                            .$this->adapter->interval($week, 1)
                        ],
                    ]
                );
                $user = $this->user;
                $this->adapter->insert(
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
                            .$this->adapter->interval($week, 1)
                        ],
                    ]
                );
            }
            $this->email->setSource($mail)
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
            return $this->adapter->row('monolyth_auth', '*', $where);
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

