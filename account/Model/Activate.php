<?php

/**
 * @package monolyth
 * @subpackage account;
 */

namespace monolyth\account;
use monolyth\core\Form;
use monolyth\core\Model;
use monolyth\User_Access;
use monolyth\Project_Access;
use monolyth\adapter\sql\NoResults_Exception;
use monolyth\render\Url_Helper;
use Exception;

class Activate_Model extends Model implements User_Access, Project_Access
{
    use Url_Helper;

    public function __invoke(Form $form)
    {
        $this->adapter->beginTransaction();
        if ($form['id']->value != $this->user->id()) {
            return 'mismatch';
        }
        $this->user->status($this->adapter->field(
            'monolyth_auth',
            'status',
            ['id' => $this->user->id()]
        ));
        try {
            if ($result = $this->confirm->process($form['hash']->value)) {
                $this->adapter->rollBack();
                return $result;
            }
            $this->adapter->flush();
            $this->user->status($this->adapter->field(
                'monolyth_auth',
                'status',
                ['id' => $this->user->id()]
            ));
            $this->adapter->commit();
            return null;
        } catch (Exception $e) {
            $this->adapter->rollback();
            return 'generic';
        }
    }

    public function request($id)
    {
        $auth = $this->adapter->row('monolyth_auth', '*', compact('id'));
        $hash = $this->confirm->getFreeHash($auth['id'].$auth['name']);
        $website = $this->project['url'];
        $siteurl = $this->url('', [], true);
        $user = $this->user;
        $uri = $this->url(
            $user->status() & $user::STATUS_REACTIVATE ?
                'monolyth/account/do_re_activate' :
                'monolyth/account/do_activate',
            ['id' => $auth['id'], 'hash' => $hash],
            true
        );
        try {
            $tmp = $this->adapter->row(
                'monolyth_confirm',
                'hash',
                [
                    'owner' => $auth['id'],
                    'tablename' => 'monolyth_auth',
                ]
            );
            $this->adapter->delete(
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
        $db = $this->adapter;
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
        $this->email->setSource("monolyth\\account\\$source")
                    ->setVariables([
                        'name' => $auth['name'],
                        'url' => $uri,
                    ])
                    ->headers(['Reply-to' => "noreply@$website"])
                    ->send($auth['email']);
        return null;
    }
}

