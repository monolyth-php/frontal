<?php

/**
 * @package monolyth
 * @subpackage account;
 */

namespace monolyth\account;
use monolyth\core\Form;
use monolyth\core\Model;
use monolyth\Confirm_Model;
use monolyth\User_Access;
use monolyth\Project_Access;
use monolyth\Language_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\render\Url_Helper;
use monolyth\render\Email;
use Exception;

class Activate_Model extends Model
{
    use Url_Helper;
    use User_Access;
    use Project_Access;
    use Language_Access;

    public function __invoke(Form $form)
    {
        self::adapter()->beginTransaction();
        if ($form['id']->value != self::user()->id()) {
            return 'mismatch';
        }
        self::user()->status(self::adapter()->field(
            'monolyth_auth',
            'status',
            ['id' => self::user()->id()]
        ));
        try {
            if ($result = $this->confirm->process($form['hash']->value)) {
                self::adapter()->rollBack();
                return $result;
            }
            self::adapter()->flush();
            self::user()->status(self::adapter()->field(
                'monolyth_auth',
                'status',
                ['id' => self::user()->id()]
            ));
            self::adapter()->commit();
            return null;
        } catch (Exception $e) {
            self::adapter()->rollback();
            return 'generic';
        }
    }

    public function request($id)
    {
        $auth = self::adapter()->row('monolyth_auth', '*', compact('id'));
        $confirm = new Confirm_Model;
        $hash = $confirm->getFreeHash($auth['id'].$auth['name']);
        $website = self::project()['url'];
        $siteurl = $this->url('', [], true);
        $user = self::user();
        $uri = $this->url(
            $user->status() & $user::STATUS_REACTIVATE ?
                'monolyth/account/do_re_activate' :
                'monolyth/account/do_activate',
            ['id' => $auth['id'], 'hash' => $hash],
            true
        );
        try {
            $tmp = self::adapter()->row(
                'monolyth_confirm',
                'hash',
                [
                    'owner' => $auth['id'],
                    'tablename' => 'monolyth_auth',
                ]
            );
            self::adapter()->delete(
                'monolyth_confirm',
                [
                    'owner' => $auth['id'],
                    'hash' => $tmp['hash'],
                ]
            );
        } catch (NoResults_Exception $e) {
        }
        $source = $user->status() & $user::STATUS_REACTIVATE ?
            'reactivate' :
            'activate';
        $db = self::adapter();
        foreach ([
            '&~' => $user::STATUS_ACTIVATE | $user::STATUS_REACTIVATE |
                $user::STATUS_EMAIL_UNCONFIRMED,
        ] as $op => $value) {
            $db->insert(
                'monolyth_confirm',
                [
                    'owner' => $auth['id'],
                    'hash' => $hash,
                    'conditional' => "id = '%d'",
                    'tablename' => 'monolyth_auth',
                    'fieldname' => 'status',
                    'operation' => $op,
                    'newvalue' => $value,
                    'datevalid' => [
                        $db->now(true).' + '.$db->interval($db::WEEK, 1),
                    ],
                ]
            );
        }
        $email = new Email;
        $email->setSource("monolyth\\account\\$source")
              ->setVariables([
                  'name' => $auth['name'],
                  'url' => $uri,
              ])
              ->headers(['Reply-to' => "noreply@$website"])
              ->send($auth['email']);
        return null;
    }
}

