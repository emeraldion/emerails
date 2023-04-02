<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2023
 *
 * @format
 */

require_once __DIR__ . '/../include/common.inc.php';

use Emeraldion\EmeRails\Db;

/**
 *  @class Relationship
 *  @short Models a relationship between two model classes
 *  @details TBD.
 */
class Relationship
{
    /**
     *  @const ONE_TO_ONE
     *  @short Relationship type one-to-one.
     */
    const ONE_TO_ONE = 'one_to_one';

    /**
     *  @const ONE_TO_MANY
     *  @short Relationship type one-to-many.
     */
    const ONE_TO_MANY = 'one_to_many';

    /**
     *  @const MANY_TO_MANY
     *  @short Relationship type many-to-many.
     */
    const MANY_TO_MANY = 'many_to_many';

    /**
     *  @attr class_initialized
     *  @short Array containing initialization information for subclasses.
     */
    private static $class_initialized = array();

    /**
     *  @attr actual_primary_key_names
     *  @short Name of the actual primary key column for the bound table.
     *  @details This is a dictionary of class name to actual primary key column name.
     *  The class property is read-only and it is set to the actual primary key of the
     *  ActiveRecord subclass when introspecting columns of the bound table.
     */
    protected static $actual_primary_key_names = array();

    /**
     *  @attr columns
     *  @short Array of columns for the relationship table.
     */
    static $columns = array();

    /**
     *  @attr column_info
     *  @short Array of column info for the model object.
     */
    static $column_info = array();

    /**
     *  @attr primary_key_name
     *  @short Name of the primary key column for the bound table.
     *  @details Set this attribute only when the primary key of the bound table is not the canonical <tt>id</tt>.
     */
    protected static $primary_key_name = null;

    /**
     *  @fn get_primary_key_name
     *  @short Returns the name of the primary key for this class.
     *  @details This method returns the name of the primary key in the table bound to this class.
     *  By default, ActiveRecord considers as primary key a column named <tt>id</tt>. Of course you can override
     *  this behavior by setting explicitly the value of <tt>$primary_key</tt> in the declaration of your class.
     */
    public function get_primary_key_name()
    {
        $ret = 'id';
        // Set to static primary_key_name member (new way)
        if ($this::$primary_key_name) {
            $ret = $this::$primary_key_name;
        }
        return $ret;
    }

    public static function one_to_one($classname, $other_classname)
    {
        return new self($classname, $other_classname, self::ONE_TO_ONE);
    }

    public static function one_to_many($classname, $other_classname)
    {
        return new self($classname, $other_classname, self::ONE_TO_MANY);
    }

    public static function many_to_many($classname, $other_classname)
    {
        return new self($classname, $other_classname, self::MANY_TO_MANY);
    }

    private function __construct($classname, $other_classname, $cardinality = self::ONE_TO_ONE)
    {
        $this->classname = $classname;
        $this->other_classname = $other_classname;
        $this->cardinality = $cardinality;

        if ($cardinality == self::MANY_TO_MANY) {
            $relationship_name = 'r_' . $this->get_table_name();

            $initialized = self::_is_initialized($relationship_name);
            if (!$initialized) {
                $conn = Db::get_connection();

                $conn->prepare('DESCRIBE `{1}`', $this->get_table_name());
                $conn->exec();
                $columns = array();
                $column_info = array();
                while ($row = $conn->fetch_assoc()) {
                    $columns[] = $row['Field'];
                    $column_info[] = $row;
                    if ($row['Key'] == 'PRI') {
                        self::$actual_primary_key_names[$relationship_name] = $row['Field'];
                    }
                }
                self::_set_columns($relationship_name, $columns);
                self::_set_column_info($relationship_name, $column_info);
                self::_set_initialized($relationship_name, true);

                Db::close_connection($conn);
            }
        }
    }

    /**
     *  @fn _is_initialized($classname)
     *  @short Tells whether the class <tt>classname</tt> has already been initialized.
     *  @param classname The name of the class that you want to inspect.
     *  @return <tt>TRUE</tt> if the class has been initialized, <tt>FALSE</tt> otherwise.
     */
    private static function _is_initialized($classname)
    {
        if (!isset(self::$class_initialized[$classname])) {
            return false;
        }
        return self::$class_initialized[$classname];
    }

    /**
     *  @fn _set_columns($classname, $cols)
     *  @short Stores the columns for the desired class.
     *  @param classname Name of the class for the desired object.
     *  @param cols The columns of the relationship table.
     */
    private static function _set_columns($classname, $cols)
    {
        self::$columns[$classname] = $cols;
    }

    /**
     *  @fn _get_columns($classname)
     *  @short Returns the columns for the desired class.
     *  @param classname Name of the class for the desired object.
     */
    private static function _get_columns($classname)
    {
        if (!isset(self::$class_initialized[$classname])) {
            return null;
        }
        return self::$columns[$classname];
    }

    /**
     *  @fn _set_column_info($classname, $info)
     *  @short Stores column info for the desired class.
     *  @param classname Name of the class for the desired object.
     *  @param info The info of the model object.
     */
    private static function _set_column_info($classname, $info)
    {
        self::$column_info[$classname] = $info;
    }

    /**
     *  @fn _get_column_info($classname)
     *  @short Returns column info for the desired class.
     *  @param classname Name of the class for the desired object.
     */
    private static function _get_column_info($classname)
    {
        if (!isset(self::$class_initialized[$classname])) {
            return null;
        }
        return self::$column_info[$classname];
    }

    /**
     *  @fn has_column($key)
     *  @short Verifies the existence of a column named <tt>key</tt> in the bound table.
     *  @param key The name of the column to check.
     */
    public function has_column($key)
    {
        $relationship_name = 'r_' . $this->get_table_name();
        $columns = self::_get_columns($relationship_name);
        return in_array($key, $columns);
    }

    public function get_column_names()
    {
        $relationship_name = 'r_' . $this->get_table_name();
        return self::_get_columns($relationship_name);
    }

    public function get_column_info()
    {
        $relationship_name = 'r_' . $this->get_table_name();
        return self::_get_column_info($relationship_name);
    }

    /**
     *  @fn _set_initialized($classname, $initialized)
     *  @short Marks the class <tt>classname</tt> as initialized.
     *  @details This method allows ActiveRecord to keep track of what subclasses have already been
     *  initialized by inspectioning the bound database table schema, whithout the need for a per-class
     *  initialization method.
     *  @param classname The name of the class that should be marked as initialized
     *  @param initialized <tt>TRUE</tt> if the class should be considered initialized, <tt>FALSE</tt> otherwise.
     */
    private static function _set_initialized($classname, $initialized)
    {
        self::$class_initialized[$classname] = $initialized;
    }

    public function get_table_name()
    {
        if (!$this->cardinality == self::MANY_TO_MANY) {
            return null;
        }

        if (!isset($this->table_name)) {
            switch ($this->cardinality) {
                case self::MANY_TO_MANY:
                    $parts = explode('\\', $this->classname);
                    $classname = $parts[count($parts) - 1];

                    $other_parts = explode('\\', $this->other_classname);
                    $other_classname = $other_parts[count($other_parts) - 1];

                    $table_names = array(
                        (new $classname())->get_relationship_table_half_name(),
                        (new $other_classname())->get_relationship_table_half_name()
                    );
                    sort($table_names);

                    $this->table_name = implode('_', $table_names);
                    break;
                default:
                    $this->table_name = null;
                    break;
            }
        }
        return $this->table_name;
    }

    public function between($member, $other_member, $params = array())
    {
        $classes = array($this->classname, $this->other_classname);

        if (!in_array(get_class($member), $classes)) {
            throw new Exception(
                sprintf(
                    "Argument 1 expected of class '%s' or '%s', but got '%s' instead.",
                    $this->classname,
                    $this->other_classname,
                    get_class($member)
                )
            );
        }

        if (!in_array(get_class($other_member), $classes)) {
            throw new Exception(
                sprintf(
                    "Argument 2 expected of class '%s' or '%s', but got '%s' instead.",
                    $this->classname,
                    $this->other_classname,
                    get_class($other_member)
                )
            );
        }

        return new RelationshipInstance($member, $other_member, $this, $params);
    }

    public function among($members, $other_members, $params = array())
    {
        if ($this->cardinality == self::ONE_TO_ONE) {
            throw new Exception('This relationship has cardinality one to one.');
        }

        $classes = array($this->classname, $this->other_classname);

        $member = array_find($members, function ($member) use ($classes) {
            return !in_array(get_class($member), $classes);
        });
        if ($member) {
            throw new Exception(
                sprintf(
                    "Argument 1 expected of class '%s' or '%s', but got '%s' instead.",
                    $this->classname,
                    $this->other_classname,
                    get_class($member)
                )
            );
        }

        $other_member = array_find($other_members, function ($other_member) use ($classes) {
            return !in_array(get_class($other_member), $classes);
        });
        if ($other_member) {
            throw new Exception(
                sprintf(
                    "Argument 2 expected of class '%s' or '%s', but got '%s' instead.",
                    $this->classname,
                    $this->other_classname,
                    get_class($other_member)
                )
            );
        }

        $instances = array();
        $member_pk = first($members)->get_primary_key();
        $other_member_pk = first($other_members)->get_primary_key();
        foreach ($members as $member) {
            $member_dict = array_key_exists($member->$member_pk, $params) ? $params[$member->$member_pk] : array();
            $instances[$member->$member_pk] = array();
            foreach ($other_members as $other_member) {
                $dict = array_key_exists($other_member->$other_member_pk, $member_dict)
                    ? $member_dict[$other_member->$other_member_pk]
                    : array();
                $instances[$member->$member_pk][$other_member->$other_member_pk] = new RelationshipInstance(
                    $member,
                    $other_member,
                    $this,
                    $dict
                );
            }
        }
        return $instances;
    }
}

class RelationshipInstance
{
    /**
     *  @attr values
     *  @short Array of values for the columns of a relationship table.
     */
    private $values;

    private $member;

    private $other_member;

    private $relationship;

    public function __construct($member, $other_member, $relationship, $params)
    {
        $this->member = $member;
        $this->other_member = $other_member;
        $this->relationship = $relationship;
        $this->values = $params;

        $this->validate();
    }

    public function of(string $classname)
    {
        if (get_class($this->member) == $classname) {
            return $this->member;
        }
        if (get_class($this->other_member) == $classname) {
            return $this->other_member;
        }
        // Throw?
        return null;
    }

    public function save()
    {
        $conn = Db::get_connection();

        $member_pk = $this->member->get_primary_key();
        $member_fk = $this->member->get_foreign_key_name();

        $other_member_pk = $this->other_member->get_primary_key();
        $other_member_fk = $this->other_member->get_foreign_key_name();

        $ret = false;

        switch ($this->relationship->cardinality) {
            case Relationship::ONE_TO_ONE:
            case Relationship::ONE_TO_MANY:
                if ($this->member->has_column($other_member_fk)) {
                    $child = $this->member;
                    $child_pk = $member_pk;

                    $parent = $this->other_member;
                    $parent_pk = $other_member_pk;
                    $parent_fk = $other_member_fk;
                } elseif ($this->other_member->has_column($member_fk)) {
                    $child = $this->other_member;
                    $child_pk = $other_member_pk;

                    $parent = $this->member;
                    $parent_pk = $member_pk;
                    $parent_fk = $member_fk;
                } else {
                    throw new Exception(
                        sprintf(
                            "Cannot find a column '%s' in table '%s' or a column '%s' in table '%s'.",
                            $member_fk,
                            $this->other_member->get_table_name(),
                            $other_member_fk,
                            $this->member->get_table_name()
                        )
                    );
                }
                $conn->prepare(
                    "UPDATE `{1}` SET `{2}` = '{3}' WHERE `{4}` = '{5}'",
                    $child->get_table_name(),
                    $parent_fk,
                    $parent->$parent_pk,
                    $child_pk,
                    $child->$child_pk
                );
                $conn->exec();

                // Update the model to avoid a reload from DB
                $child->$parent_fk = $parent->$parent_pk;

                $ret = true;
                break;

            case Relationship::MANY_TO_MANY:
                $columns = $this->relationship->get_column_names();
                $ret = false;
                $nonempty = array();

                $this->validate(true);

                for ($i = 0; $i < count($columns); $i++) {
                    if (
                        // Do not set the primary key
                        $columns[$i] != $this->relationship->get_primary_key_name() &&
                        // Exclude empty columns
                        $this->values &&
                        array_key_exists($columns[$i], $this->values) &&
                        (isset($this->values[$columns[$i]]) || is_null($this->values[$columns[$i]]))
                    ) {
                        $nonempty[] = $columns[$i];
                    }
                }
                if (!empty($this->values[$this->relationship->get_primary_key_name()])) {
                    $query = 'UPDATE `{1}` SET ';
                    for ($i = 0; $i < count($nonempty); $i++) {
                        $query .= "`{$nonempty[$i]}` = {$this->wrap_value_for_query(
                            $nonempty[$i],
                            $this->values[$nonempty[$i]],
                            $conn
                        )}";
                        if ($i < count($nonempty) - 1) {
                            $query .= ', ';
                        }
                    }
                    $query .= " WHERE `{$this->relationship->get_primary_key_name()}` = '{2}' LIMIT 1";
                    $conn->prepare(
                        $query,
                        $this->relationship->get_table_name(),
                        $this->values[$this->relationship->get_primary_key_name()]
                    );
                    $conn->exec();
                    $ret = true;
                } else {
                    $query = (isset($this->_ignore) ? 'INSERT IGNORE' : 'INSERT') . ' INTO `{1}` (`{2}`, `{3}`';
                    for ($i = 0; $i < count($nonempty); $i++) {
                        $query .= ", `{$nonempty[$i]}`";
                    }
                    $query .= ") VALUES ('{4}', '{5}'";
                    for ($i = 0; $i < count($nonempty); $i++) {
                        $query .=
                            ', ' . $this->wrap_value_for_query($nonempty[$i], $this->values[$nonempty[$i]], $conn);
                    }
                    $query .= ')';
                    $conn->prepare(
                        $query,
                        $this->relationship->get_table_name(),
                        $member_fk,
                        $other_member_fk,
                        $this->member->$member_pk,
                        $this->other_member->$other_member_pk
                    );
                    $conn->exec();
                    $ret = true;
                }

                // Update models to avoid a reload from DB
                $member_collection = singularize($this->member->get_table_name());
                $other_member_collection = singularize($this->other_member->get_table_name());
                if (is_array($this->member->$other_member_collection)) {
                    if (
                        !array_key_exists(
                            $this->other_member->$other_member_pk,
                            $this->member->$other_member_collection
                        )
                    ) {
                        $this->member->$other_member_collection[$this->other_member->$other_member_pk] =
                            $this->other_member;
                    }
                } else {
                    $this->member->$other_member_collection = array(
                        $this->other_member->$other_member_pk => $this->other_member
                    );
                }
                if (is_array($this->other_member->$member_collection)) {
                    if (!array_key_exists($this->member->$member_pk, $this->other_member->$member_collection)) {
                        $this->other_member->$member_collection[$this->member->$member_pk] = $this->member;
                    }
                } else {
                    $this->other_member->$member_collection = array(
                        $this->member->$member_pk => $this->member
                    );
                }

                break;
        }

        Db::close_connection($conn);

        return $ret;
    }

    public function delete()
    {
        $conn = Db::get_connection();

        $member_pk = $this->member->get_primary_key();
        $member_fk = $this->member->get_foreign_key_name();

        $other_member_pk = $this->other_member->get_primary_key();
        $other_member_fk = $this->other_member->get_foreign_key_name();

        $ret = false;

        switch ($this->relationship->cardinality) {
            case Relationship::ONE_TO_ONE:
            case Relationship::ONE_TO_MANY:
                if ($this->member->has_column($other_member_fk)) {
                    $child = $this->member;
                    $child_pk = $member_pk;

                    $parent = $this->other_member;
                    $parent_fk = $other_member_fk;
                } elseif ($this->other_member->has_column($member_fk)) {
                    $child = $this->other_member;
                    $child_pk = $other_member_pk;

                    $parent = $this->member;
                    $parent_fk = $member_fk;
                } else {
                    throw new Exception(
                        sprint(
                            "Cannot find a column '%s' in table '%s' or a column '%s' in table '%s'.",
                            $member_fk,
                            $this->other_member->get_table_name(),
                            $other_member_fk,
                            $this->member->get_table_name()
                        )
                    );
                }
                $conn->prepare(
                    "UPDATE `{1}` SET `{2}` = NULL WHERE `{3}` = '{4}'",
                    $child->get_table_name(),
                    $parent_fk,
                    $child_pk,
                    $child->$child_pk
                );
                $conn->exec();
                // TODO: $child->reload() ?
                $child->find_by_id($child->$child_pk);
                break;

            case Relationship::MANY_TO_MANY:
                $conn->prepare(
                    "DELETE FROM `{1}` WHERE `{2}` = '{3}' AND `{4}` = '{5}'",
                    $this->relationship->get_table_name(),
                    $member_fk,
                    $this->member->$member_pk,
                    $other_member_fk,
                    $this->other_member->$other_member_pk
                );
                $conn->exec();

                $ret = true;
                break;
        }

        Db::close_connection($conn);

        return $ret;
    }

    protected function wrap_value_for_query($key, $value, $conn)
    {
        if (is_null($value)) {
            return 'NULL';
        }
        $column_info = $this->relationship->get_column_info();
        $info = array_find($column_info, function ($info) use ($key) {
            return $info['Field'] === $key;
        });
        preg_match('/([a-z]+)(\((\d+)\))?/', $info['Type'], $matches);
        list(, $type) = $matches;
        switch ($type) {
            case 'int':
            case 'tinyint':
            case 'smallint':
                return $conn->escape($value);
        }
        return "'{$conn->escape($value)}'";
    }

    protected function validate($raise = false)
    {
        if (!$this->values) {
            return;
        }

        $columns = $this->relationship->get_column_names();

        foreach ($columns as $column) {
            if (
                // Do not set the primary key
                $column != $this->relationship->get_primary_key_name() &&
                // Exclude empty columns
                $this->values &&
                array_key_exists($column, $this->values) &&
                (isset($this->values[$column]) || is_null($this->values[$column]))
            ) {
                $this->values[$column] = $this->validate_field($column, $this->values[$column], $raise);
            }
        }
    }

    protected function validate_field($key, $value, $raise = false)
    {
        $column_info = $this->relationship->get_column_info();
        $info = array_find($column_info, function ($info) use ($key) {
            return $info['Field'] === $key;
        });

        $type = 'unknown';
        $ret = null;
        if (!$info) {
            // No info, return value as-is
            $ret = $value;
        } else {
            $nullable = $info['Null'] === 'YES';

            if (is_null($value) && !$nullable) {
                if ($raise) {
                    throw new Exception(sprintf("Attempt to null the field '%s' but it is not nullable", $key));
                }
                $ret = null;
            } else {
                preg_match('/([a-z]+)(\((.+)\))?/', $info['Type'], $matches);
                list(, $type) = $matches;

                switch ($type) {
                    case 'enum':
                        $possible_values = array_map(function ($value) {
                            return trim($value, '\'');
                        }, explode(',', $matches[3]));
                        if (!(is_null($value) || in_array($value, $possible_values))) {
                            if ($raise) {
                                throw new Exception(
                                    sprintf(
                                        "Field '%s' has the wrong type. Expected '%s(%s)' but found: '%s'",
                                        $key,
                                        $type,
                                        $matches[3],
                                        gettype($value)
                                    )
                                );
                            }
                            $ret = null;
                        } else {
                            $ret = $value;
                        }
                        break;
                    case 'int':
                    case 'tinyint':
                        $max_length = (int) $matches[3];
                        if ($raise && !is_null($value) && !is_int($value)) {
                            throw new Exception(
                                sprintf(
                                    "Attempt to set the field '%s' to a value with incorrect type. Expected '%s(%d)' but found: '%s'",
                                    $key,
                                    $type,
                                    $max_length,
                                    gettype($value)
                                )
                            );
                        }
                        $ret = is_null($value) ? null : (int) $value;
                        break;
                    case 'float':
                        if ($raise && !is_null($value) && !is_float($value)) {
                            throw new Exception(
                                sprintf(
                                    "Attempt to set the field '%s' to a value with incorrect type. Expected 'float' but found: '%s'",
                                    $key,
                                    gettype($value)
                                )
                            );
                        }
                        $ret = is_null($value) ? null : (float) $value;
                        break;
                    default:
                        $ret = $value;
                }
            }
        }
        if ($type != 'enum' && $ret != $value) {
            trigger_error(
                sprintf(
                    "Expected %s::%s('%s', '%s') to return: '%s' of type '%s' but got: %s.",
                    get_class($this),
                    __FUNCTION__,
                    $key,
                    $value,
                    $value,
                    $type,
                    var_export($ret, true)
                ),
                E_USER_NOTICE
            );
        }

        return $ret;
    }

    /**
     *  @fn __set($key, $value)
     *  @short Magic method to set the value of a property.
     *  @param key The key of the property.
     *  @param value The value of the property.
     */
    public function __set($key, $value)
    {
        if ($this->relationship->has_column($key)) {
            $this->values[$key] = $this->validate_field($key, $value, true);
        } else {
            $this->$key = $value;
        }
    }

    /**
     *  @fn __get($key)
     *  @short Magic method to get the value of a property.
     *  @param key The key of the desired property.
     */
    public function __get($key)
    {
        if ($this->values !== null && array_key_exists($key, $this->values)) {
            $value = $this->values[$key];
        } elseif (property_exists($this, $key)) {
            $value = $this->$key;
        } else {
            $value = null;
        }
        return $this->validate_field($key, $value);
    }

    /**
     *  @fn __isset($key)
     *  @short Magic method to determine if a property exists.
     *  @param key The key to test.
     */
    public function __isset($key)
    {
        if (!(isset($this->values) && !empty($this->values))) {
            return false;
        }
        if (array_key_exists($key, $this->values)) {
            return true;
        }
        if (property_exists($this, $key)) {
            return true;
        }
        return false;
    }

    /**
     *      @fn __unset($key)
     *      @short Magic method to unset a property.
     *      @param key The key to unset.
     */
    public function __unset($key)
    {
        if (!(isset($this->values) && !empty($this->values))) {
            return;
        }
        if (array_key_exists($key, $this->values)) {
            unset($this->values[$key]);
        } elseif (property_exists($this, $key)) {
            unset($this->key);
        }
    }
}
?>
