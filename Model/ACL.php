<?php

/**
 * Permissions in MonoLyth are handled via Access Control Lists (ACLs).
 * An ACL-entry contains, for a certain object and/or action,
 * the user and/or group. These requests can be chained, e.g.:
 * <code>
 * <?php
 *
 * $acl = monolyth\User::acl();
 * $permission = $acl->group('Administrators')->can('Edit', 'ModelAuth');
 * // $permission now evaluates to true or false, after calling __toString().
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2009, 2010, 2011
 *
 * ?>
 * </code>
 */

namespace monolyth;
use monolyth\adapter\sql\NoResults_Exception;

/**
 * The default ACL model. You're not meant to extend this.
 *
 * Access control lists are maintained on an 'as needed' basis. This means
 * that stuff won't get loaded until you actually query for it. The idea here
 * is that most permissions will be completely irrelevant on your average page.
 */
final class ACL_Model implements adapter\Access, Session_Access
{
    /** Can create a resource of this type. */
    const CREATE = 1;
    /** Can update a resource of this type. */
    const UPDATE = 2;
    /** Can delete a resource of this type. */
    const DELETE = 4;
    /** Can read/view a resource of this type. */
    const READ = 8;
    /** Can own a resource of this type. */
    const OWN = 16;
    /**
     * Can 'execute' a resource of this type.
     * 'Execute' is treated rather liberally as it usually wouldn't make much
     * sense in a web-app. However, MonoLyth uses it as a sort-of "special"
     * bit, meaning 'can do something really special'. Hence, its exact meaning
     * will vary from resource to resource.
     */
    const EXECUTE = 32;

    /** Internal variable specifying the final return value. */
    private $last = true;
    /** Internal variable for caching queries. */
    private $cache = [];
    /** Internal variable specifying the current resource. */
    private $resource = null;

    /** The user this instance refers to. */
    private $user;
    /** The group(s) this instance refers to. */
    private $group;

    public function __construct(User_Model $model)
    {
        $this->model = $model;
    }

    /**
     * "Constructor" setting user and group(s). Multiple groups can be passed as
     * an array or as a space-delimited string. If called without arguments, the
     * currently logged-in user is assumed.
     *
     * If called without arguments and the user isn't logged in, an exception
     * is thrown.
     *
     * @param int|string $user The user's ID or unique name.
     * @param int|string|array $group The group's ID or unique name, a
     *                                space-delimited list of them or an array
     *                                of ID's/names.
     */
    public function init($user = null, $group = null)
    {
        $this->user = isset($user) ? $user : $this->model->id();
        $this->group = isset($group) ? $group : $this->model->group();
        if (!isset($this->user, $this->group) && !$this->model->loggedIn()) {
            return $this;
        }
        if (isset($this->user) && !is_numeric($this->user)) {
            try {
                $this->user = $this->adapter->field(
                    'monolyth_auth',
                    'id',
                    ['name' => $user]
                );
            } catch (NoResults_Exception $e) {
                return $this;
            }
        } elseif ((!isset($this->user, $this->group)
                or $this->user = $this->model->id()
            )
            && $this->model->loggedIn()
        ) {
            $this->user = $this->model->id();
            $this->group = $this->model->group();
        } else {
            return $this;
        }
        $this->cache = $this->session->get('Acl');

        if (isset($this->group)) {
            $try = [];
            if (is_string($this->group)) {
                $group = explode(' ', $this->group);
            }
            if (!is_array($this->group)) {
                $this->group = [$this->group];
            }
            foreach ($this->group as $key => $id) {
                if (!is_numeric($id)) {
                    $try[] = $id;
                    unset($this->group[$key]);
                }
            }
            if ($try) {
                try {
                    foreach ($this->adapter->rows(
                        'monolyth_auth_group',
                        'id',
                        ['name' => ["IN" => $try]]
                    ) as $row) {
                        $this->group[] = $row['id'];
                    }
                } catch (NoResults_Exception $e) {
                }
            }
        }
        return $this;
    }

    /**
     * Define what resource to check on.
     *
     * The resource can be passed as an object or as a string. In the latter case,
     * when using a Model-name, the optional second argument defines the primary
     * key for a specific instance. This can be passed as either a "where-array"
     * or as a serialized, base64_encoded string.
     *
     * If an instance of a Model is passed that has been "load"'ed the primary
     * keys for that instance will be used (unless $pks overrides this).
     *
     * @param string|object $what The resource to check on.
     * @param string|array $pks Optional primary keys to use.
     */
    public function using($what, $pks = null)
    {
        if (is_object($what)) {
            $name = get_class($what);
            if ($what instanceof Model && !isset($pks)) {
                $pks = $what->getPrimaryKeys();
            }
        } else {
            $name = $what;
        }
        if (isset($pks) and is_array($pks)) {
            $pks = base64_encode(serialize($pks));
        }
        $this->resource = [$name, $pks];
        return $this;
    }

    /**
     * Check if the current user/group is allowed to do something on the
     * currently used resource.
     *
     * @param int $action The action we want to check.
     * @return bool True if we can, false if we can't (orly?).
     */
    public function can($action)
    {
        if (!isset($this->cache[$this->resource[0]][$this->resource[1]])) {
            /** Query ACL-tables to try to find a match. */
            $where = [[['owner' => $this->user]]];
            $parts = array_reverse(explode('.', $this->resource[0]));
            if ($this->group) {
                $where[0][] = ['auth_group' => ['IN' => $this->group]];
            }
            if (isset($this->resource[1])) {
                $where[1] = ['pk' => $this->resource[1]];
            }
            $where[2] = [
                ['name' => ['IN' => $parts]],
                ['name' => '*'],
            ];
            $order = [];
            foreach ($parts as $part) {
                $order[] = ['DESC' => "name = '$part'"];
            }
            $options = [];
            try {
                $q = $this->adapter->rows(
                    "monolyth_acl JOIN monolyth_acl_resource
                        ON acl_resource = monolyth_acl_resource.id",
                    '*',
                    $where,
                    ['order' => $order]
                );
                foreach ($q as $row) {
                    $options[$row['name']] = $row;
                }
            } catch (NoResults_Exception $e) {
                // No rights at all, that's fine.
            }

            // Insert global permissions at the highest possible level.
            foreach (array_reverse($parts) as $part) {
                if (!isset($options[$part]) && isset($options['*'])) {
                    $options[$part] = $options['*'];
                    break;
                }
            }

            // Store permissions ordered by specificness.
            $name = '';
            $parts = array_reverse($parts);
            foreach ($parts as $i => $part) {
                if (!isset($options[$part])) {
                    try {
                        $options[$part] = $options[$parts[$i - 1]];
                    } catch (\ErrorException $e) {
                        $options[$part] = [
                            'action' => 0,
                            'pk' => null,
                        ];
                    }
                }
                $name .= ($i ? '.' : '').$part;
                $options[$part]['name'] = $name;
            }
            foreach ($options as $option) {
                $this->cache[$option['name']][$option['pk']] = $option['action'];
            }
        }
        $this->session->set('cache', $this->cache);
        try {
            return $this->cache[$this->resource[0]][$this->resource[1]]
                & $action;
        } catch (\ErrorException $e) {
            return null;
        }
    }

    public function set($action, $right)
    {
        $parts = explode('.', $action);
        foreach ($parts as $part) {
            try {
                $n = $o;
            } catch (ErrorException $e) {
                $n = null;
            }
            $o = $this->resourceModel;
            try {
                $o->load(['name' => $part]);
            } catch (NoResults_Exception $e) {
                $o['name'] = $part;
                $o['parent'] = isset($n) ? $n['id'] : null;
            }
            $o->save();
        }
        $r = $this->aclModel;
        try {
            $r->load(['acl_resource' => $o['id'], 'owner' => $this->user]);
        } catch (NoResults_Exception $e) {
            $r['acl_resource'] = $o['id'];
            $r['owner'] = $this->user;
        }
        $r->action = $right;
        $r->save();
    }

    /**
     * Flush ACL's cache - it gets outdated when we login and a call has been
     * done earlier in the (parent) controller.
     */
    public function flush()
    {
        $this->cache = [];
        $this->session->set('cache', []);
        $this->group = $this->model->group();
    }
}

