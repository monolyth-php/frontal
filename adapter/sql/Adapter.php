<?php

/**
 * A generic class all database classes involving SQL should extend.
 *
 * @package monolyth
 * @subpackage adapter
 * @subpackage sql
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright MonoMelodies 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014
 */

namespace monolyth\adapter\sql;
use PDO;
use PDOException;
use Exception;
use PDOStatement;
use ArrayObject;
use monolyth;
use monolyth\Logger_Access;

abstract class Adapter implements monolyth\adapter\Adapter
{
    use Logger_Access;

    protected $cache = [];
    protected $querytime = 0;
    protected $prepared = [];
    protected $translevel = 0;
    protected $index;
    public $pdo = null;
    private $settings;

    public function __construct($dsn, $user = null, $pass = null,
        array $options = [])
    {
        $this->settings = compact('dsn', 'user', 'pass', 'options');
    }

    protected function connect()
    {
        if (!is_null($this->pdo)) {
            return;
        }
        try {
            extract($this->settings);
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new ConnectionFailed_Exception($e->getMessage());
        }
        self::logger()->log('Initialised database connection');
    }

    public function reconnect()
    {
        $this->pdo = null;
        $this->connect();
    }

    /**
     * Internal helper to correctly formate error messages.
     */
    protected function error($msg, array $bind = [])
    {
        foreach ($bind as $key => $value) {
            if (is_null($value)) {
                $value = 'NULL';
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $msg .= "$key => $value\n";
        }
        return $msg;
    }

    public function stats()
    {
        return ['total' => $this->cache, 'time' => $this->querytime];
    }

    public function beginTransaction()
    {
        if (!$this->translevel++) {
            $this->connect();
            return $this->pdo->beginTransaction();
        }
    }

    public function commit()
    {
        if ($this->translevel-- == 1) {
            $this->connect();
            return $this->pdo->commit();
        }
    }

    public function rollback()
    {
        if ($this->translevel-- == 1) {
            $this->connect();
            return $this->pdo->rollback();
        }
    }

    /**
     * A 'bindable' version of PDO::query.
     *
     * @param string $sql The SQL to execute.
     * @param array $bind Optional values to bind.
     * @return PDOStatement A PDO result statement.
     * @throw NoResults_Exception if no rows were found.
     */
    public function query($sql, array $bind = [])
    {
        static $statements = [];
        if (!isset($this->index)) {
            $this->index = spl_object_hash($this);
        }
        $i = $this->index;
        $start = microtime(true);
        if ($bind) {
            if (!isset($statements[$i][$sql])) {
                $statements[$sql][$i] = $this->prepare($sql);
            }
            $statements[$sql][$i]->execute($bind);
            $q = $statements[$sql][$i]->fetchAll(PDO::FETCH_ASSOC);
            $sql = $statements[$sql][$i]->queryString;
        } else {
            $this->connect();
            $q = $this->pdo->query($sql);
        }
        self::logger()->log($sql, $start);
        if (!($q && count($q))) {
            throw new NoResults_Exception($sql, $bind, 'No results.');
        }
        return $q;
    }

    public function flush()
    {
        $this->connect();
        $this->cache = [];
    }

    private function _get(array $params, $function)
    {
        static $counter = 0;
        list($table, $field, $where, $options) = $params;
        switch ($function) {
            case 'fetchColumn':
                $args = [0];
                $fn = $function;
                break;
            case 'fetchObject':
                $fn = 'fetch';
            case 'fetchObjects':
                $o = array_pop(func_get_args());
                if (is_object($o)) {
                    $args = [self::FETCH_INTO, $o];
                } else {
                    $args = [self::FETCH_CLASS, $o];
                }
                $fn = isset($fn) ? $fn : 'fetchAll';
                break;
            default:
                $args = [PDO::FETCH_ASSOC];
                $fn = $function;
        }
        $start = microtime(true);
        $statement = $this->select($table, $field, $where, $options);
        $dummy = [];
        try {
            $key = spl_object_hash($statement).
                serialize([$field, $where, $options]);
            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }
        } catch (Exception $e) {
            $key = ++$counter;
        }
        call_user_func_array([$statement, 'setFetchMode'], $args);
        self::logger()->log($statement->queryString, $start);
        if (false !== ($result = $statement->$fn())
            and $result !== []
        ) {
            $this->cache[$key] = $result;
            return $result;
        }
        throw new NoResults_Exception($statement->queryString, $where);
    }

    /**
     * Retrieve a single value from a single column.
     *
     * @param string $table The table(s) to query.
     * @param string $field The field (column) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return mixed A scalar containing the result, or null.
     * @throw NoResults_Exception when no rows were found.
     */
    public function field($table, $field, $where = null, $options = null)
    {
        $this->connect();
        $options['limit'] = 1;
        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }
        return $this->_get(
            [$table, $field, $where, $options],
            'fetchColumn'
        );
    }

    /**
     * Retrieve a single row from the database.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return array An array containing the result.
     * @throw NoResults_Exception when no rows were found.
     */
    public function row($table, $fields, $where = null, $options = [])
    {
        $this->connect();
        $options['limit'] = 1;
        if (!isset($options['offset'])) {
            $options['offset'] = 0;
        }
        return $this->_get(
            [$table, $fields, $where, $options],
            'fetch'
        );
    }

    /**
     * Retrieve a resultset from the database.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return array An array of results.
     * @throw NoResults_Exception when no rows were found.
     */
    public function rows($table, $fields, $where = null, $options = [])
    {
        $this->connect();
        return $this->_get(
            [$table, $fields, $where, $options],
            'fetchAll'
        );
    }

    /**
     * Retrieve a resultset from the database, and load it in the supplied
     * models.
     *
     * @param mixed $model A Model or classname.
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return array An array of Models.
     * @throw NoResults_Exception when no rows were found.
     */
    public function models($model, $table, $fields, array $where = [],
        array $options = []
    ) {
        $this->connect();
        $rows = $this->rows($table, $fields, $where, $options);
        $model = is_string($model) ? new $model : $model;
        $return = [];
        foreach ($rows as $row) {
            $m = clone $model;
            $return[] = $m->load($row);
        }
        return $return;
    }

    /**
     * Identical to Adapter::models, but for a single row.
     *
     * @param mixed $model A Model or classname.
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return monolyth\core\Model A resulting Model.
     * @throw NoResults_Exception when no rows were found.
     */
    public function model($model, $table, $fields, array $where = [],
        array $options = []
    ) {
        return array_shift($this->models(
            $model,
            $table,
            $fields,
            $where,
            $options
        ));
    }

    /**
     * Retrieve a paginated resultset from the database.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @param string|object $output Optional classname or object to select into.
     * @return mixed An array or resultsets, or null.
     */
    public function pages($table, $fields, $where = null,
        $options = null, $output = null
    )
    {
        $limit = null;
        if (isset($options['limit'])) {
            $limit = $options['limit'];
        }
        $offset = 0;
        if (isset($options['offset'])) {
            $offset = $options['offset'];
        }
        $bind = [];
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s %s",
            implode(', ', $fields),
            $table,
            $this->where($where, $bind),
            $this->options($options, $bind)
        );
        if (!isset($this->prepared[$sql])) {
            $this->connect();
            $this->prepared[$sql] = $this->pdo->prepare($sql);
        }
        if (!$this->prepared[$sql]->execute($bind)) {
        }
        return new Resultset(
            $this,
            $this->prepared[$sql],
            $bind,
            $limit,
            $offset,
            $output
        );
    }

    /**
     * Retrieve a resultset from the database, and group rows using the first
     * field specified. This is handy to instantly get a list where the index
     * is important, e.g. the id.
     *
     * @param string $table The table(s) to query.
     * @param string|array $fields The field(s) (column(s)) to query. Field with
     *                             index 0 is used to group by.
     * @param array $where An SQL where-array.
     * @param array $options Array of options.
     * @return mixed An array or resultsets, or null.
     * @throw NoResults_Exception when no rows were found.
     */
    public function indexed($table, $fields, array $where = [],
        array $options = []
    )
    {
        $this->connect();
        $rows = $this->rows($table, $fields, $where, $options);
        $return = [];
        $idx = array_shift(array_keys($rows[0]));
        foreach ($rows as $row) {
            $return[$row[$idx]] = $row;
        }
        return $return;
    }

    /**
     * Generate a select statement.
     *
     * @param string $table The table(s) to query.
     * @param string $field The field (column) to query.
     * @param array $where The where-clause.
     * @param array $options The options (limit, offset etc.).
     * @return string A string of SQL.
     */
    public function select($table, $fields, $where, $options)
    {
        $bind = [];
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $sql = sprintf(
            "SELECT %s FROM %s WHERE %s %s",
            implode(', ', $fields),
            $table,
            $this->where($where, $bind),
            $this->options($options, $bind)
        );
        try {
            $key = serialize([$sql, $bind]);
            if (isset($this->cache[$key])) {
                return $this->cache[$key];
            }
        } catch (PDOException $e) {
        }
        if (!isset($this->prepared[$sql])) {
            $this->connect();
            $this->prepared[$sql] = $this->pdo->prepare($sql);
        }
        try {
            if (!$this->prepared[$sql]->execute($bind)) {
                throw new namespace\Exception($this->error(
                    "Couldn't call execute on prepared statement: $sql",
                    $bind
                ));
            }
            $this->cache[$key] = $this->prepared[$sql];
            return $this->prepared[$sql];
        } catch (PDOException $e) {
            throw new namespace\Exception(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                1,
                $e
            );
        }
    }

    /**
     * Insert a row into the database.
     *
     * @param string $table The table to insert into.
     * @param array $fields Array of Field => value pairs to insert.
     * @return mixed The last inserted serial, or 0 or true if none found.
     * @throw monolyth\adapter\sql\InsertNone_Exception if no rows were inserted.
     */
    public function insert($table, array $fields)
    {
        $bind = [];
        $use = [];
        foreach ($fields as $name => $field) {
            if (is_null($field)) {
                continue;
            }
            $use[$name] = $field;
        }
        $fields = $use;
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            str_replace("'", '', implode(', ', array_keys($fields))),
            implode(', ', $this->values($fields, $bind))
        );
        $start = microtime(true);
        $this->connect();
        $statement = $this->pdo->prepare($sql);
        try {
            $statement->execute($bind);
            self::logger()->log($sql, $start);
        } catch (PDOException $e) {
            throw new InsertNone_Exception(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                2,
                $e
            );
        }
        if (!($affectedRows = $statement->rowCount() and $affectedRows)) {
            $info = $statement->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new InsertNone_Exception($this->error($msg, $bind));
        }
    }
    
    /**
     * Update one or more rows in the database.
     *
     * @param string $table The table to update.
     * @param array $fields Array Field => value pairs to update.
     * @param array $where Array of where statements to limit updates.
     * @return integer The number of affected (updated) rows.
     * @throw UpdateNone_Exception if no rows were updated.
     */
    public function update($table, array $fields, $where, $options = null)
    {
        $bind = [];
        foreach ($fields as $key => &$value) {
            if (is_array($value)) {
                $value = call_user_func(function($value) {
                    $new = 0;
                    foreach ($value as $val) {
                        if (strlen($val) && !is_numeric($val)) {
                            return $value;
                        }
                        $new |= $val;
                    }
                    return $new;
                }, $value);
            }
            if (!is_numeric($key)) {
                $value = $key.' = '.$this->value($value, $bind);
            }
        }
        $start = microtime(true);
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s %s",
            $table,
            implode(', ', $fields),
            $this->where($where, $bind),
            $this->options($options, $bind)
        );
        try {
            $this->connect();
            $statement = $this->pdo->prepare($sql);
            $statement->execute($bind);
            self::logger()->log($sql, $start);
        } catch (PDOException $e) {
            throw new Exception(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                3,
                $e
            );
        }
        if (!($affectedRows = $statement->rowCount() and $affectedRows)) {
            $info = $statement->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new UpdateNone_Exception($this->error($msg, $bind), 1);
        }
        return $affectedRows;
    }

    public function upsert($table, array $fields)
    {
        $this->connect();
        try {
            $this->delete($table, $fields);
        } catch (DeleteNone_Exception $e) {
        }
        try {
            return $this->insert($table, $fields);
        } catch (InsertNone_Exception $e) {
            $info = $statement->errorInfo();
            $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
            throw new UpsertNone_Exception($msg);
        }
    }

    /**
     * Delete a row from the database.
     *
     * @param string $table The table to delete from.
     * @array $where Array of where statements to limit deletes.
     * @return int The number of deleted rows.
     * @throw DeleteNone_Exception if no rows were deleted.
     */
    public function delete($table, array $where)
    {
        try {
            $bind = [];
            $sql = sprintf(
                "DELETE FROM %s WHERE %s",
                $table,
                $this->where($where, $bind)
            );
            $start = microtime(true);
            $this->connect();
            $statement = $this->pdo->prepare($sql);
            $statement->execute($bind);
            self::logger()->log($sql, $start);
            $affected = $statement->rowCount();
            if (!$affected) {
                $info = $statement->errorInfo();
                $msg = "{$info[0]} / {$info[1]}: {$info[2]} - $sql";
                throw new DeleteNone_Exception($this->error($msg, $bind));
            }
            return $affected;
        } catch (PDOException $e) {
            throw new Exception(
                $this->error(
                    "Error in $sql: {$e->errorInfo[2]}\n\nParamaters:\n",
                    $bind
                ),
                4,
                $e
            );
        }
    }

    public function truncate($table)
    {
        $this->connect();
        if (!($result = $this->exec(sprintf(
            "TRUNCATE TABLE %s",
            $table
        )))) {
            return false;
        }
        if (method_exists($this, 'affectedRows')) {
            return $this->affectedRows($result);
        }
        return true;
    }

    public function numRowsTotal(PDOStatement $result, &$bind)
    {
        $this->connect();
        $sql = $result->queryString;
        $sql = preg_replace('/SELECT.*?FROM/si', 'SELECT COUNT(*) FROM', $sql);
        $sql = preg_replace('/(LIMIT|OFFSET)\s+\d+/si', '', $sql);
        $sql = preg_replace('/ORDER\s+BY.*?$/si', '', $sql);
        $statement = $this->prepare($sql);
        $statement->execute($bind);
        return $statement->fetchColumn();
    }

    public function now($string = false)
    {
        if (!$string) {
            return ['NOW()'];
        }
        return 'NOW()';
    }

    public function datenull()
    {
        return null;
    }

    public function values($array, &$bind)
    {
        foreach ($array as &$value) {
            $value = $this->value($value, $bind);
        }
        return $array;
    }

    public function value($value, &$bind)
    {
        if (is_null($value)) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value instanceof ArrayObject) {
            $value = (array)$value;
        }
        if (is_array($value)) { // literal
            return array_shift($value);
        }
        if (is_object($value)) {
            $value = "$value";
        }
        $bind[] = $value;
        return '?';
    }

    public function where($array, array &$bind, $seperator = 'AND')
    {
        $this->connect();
        if (!$array) {
            return '(1=1)';
        }
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $array[$key] = $this->where(
                    $value,
                    $bind,
                    $seperator == 'AND' ? 'OR' : 'AND'
                );
            } elseif (is_array($value)) {
                $keys = array_keys($value);
                $mod = array_shift($keys);
                switch (strtoupper($mod)) {
                    case 'IN':
                    case 'NOT IN':
                        $array[$key] = $this->in(
                            $key,
                            $value[$mod],
                            strtoupper($mod),
                            $bind
                        );
                        break;
                    case 'ANY':
                        $array[$key] = $this->any(
                            $key,
                            array_unique($value[$mod]),
                            $bind
                        );
                        break;
                    case 'LIKE':
                        $array[$key] = sprintf(
                            "(%s LIKE %s OR %s LIKE %s OR %s LIKE %s)",
                            $key,
                            $this->quote("%{$value[$mod]}"),
                            $key,
                            $this->quote("{$value[$mod]}%"),
                            $key,
                            $this->quote("%{$value[$mod]}%")
                        );
                        break;
                    default:
                        $val = array_shift($value);
                        $array[$key] = sprintf(
                            '%s %s %s',
                            $key,
                            $this->operator($val, $mod),
                            $this->value($val, $bind)
                        );
                }
            } else {
                $array[$key] = sprintf(
                    '%s %s %s',
                    $key,
                    $this->operator($value),
                    $this->value($value, $bind)
                );
            }
        }
        return '('.implode(" $seperator ", $array).')';
    }

    public function in($key, $values, $operator, &$bind)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        $value = array_unique($values);
        return sprintf(
            '%s %s (%s)',
            $key,
            $operator,
            implode(', ', $this->values($values, $bind))
        );      
    }

    public function any($field, $value, &$bind)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        // return (a = 1 or a = 2 or ...) as fallback
    /*
        $o = DB::load();
        if (method_exists($o, 'any')) {
            return $o->any($field, $value);
        }

        print 'bla';
    */
    }

    public function operator($value, $operator = '=')
    {
        if ($value === null) {
            return $operator == '=' ? 'IS' : 'IS NOT';
        }
        if ($operator === '!') {
            return '<>';
        }
        if (is_numeric($operator)) {
            return '=';
        }
        return $operator;
    }
    
    public function options($myoptions, &$bind)
    {
        if (!$myoptions) {
            return '';
        }
        $options = [];
        foreach ($myoptions as $key => $value) {
            $options[strtoupper($key)] = $value;
        }
        $myoptions = [];
        if (isset($options['GROUP'])) {
            if (!is_array($options['GROUP'])) {
                $options['GROUP'] = [$options['GROUP']];
            }
            $myoptions[] = sprintf(
                "GROUP BY %s",
                implode(', ', $options['GROUP'])
            );
        }
        if (isset($options['HAVING'])) {
            $myoptions[] = sprintf(
                "HAVING %s",
                $this->where($options['HAVING'], $bind)
            );
        }
        if (isset($options['ORDER'])) {
            $tmp = [];
            if (!is_array($options['ORDER'])) {
                $myoptions[] = "ORDER BY {$options['ORDER']}";
            } else {
                foreach ($options['ORDER'] as $order) {
                    if (!is_array($order)) {
                        $tmp[] = $order;
                        continue;
                    }
                    $dir = array_shift($dir = array_keys($order));
                    $col = array_shift($order);
                    if (!is_array($col)) {
                        $col = [$col];
                    }
                    foreach ($col as $onecol) {
                        $tmp[] = sprintf(
                            '%s %s',
                            $onecol,
                            strtoupper($dir)
                        );
                    }
                }
                $myoptions[] = sprintf(
                    "ORDER BY %s",
                    implode(', ', $tmp)
                );
            }
        }
        if (isset($options['LIMIT'])) {
            $myoptions[] = sprintf(
                "LIMIT %s",
                $options['LIMIT']
            );
        }
        if (isset($options['OFFSET'])) {
            $myoptions[] = sprintf(
                "OFFSET %d",
                $options['OFFSET']
            );
        }
        return implode(' ', $myoptions);
    }

    public function __call($fn, $args)
    {
        $this->connect();
        return call_user_func_array([$this->pdo, $fn], $args);
    }
}

