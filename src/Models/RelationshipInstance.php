<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2026
 *
 * @format
 */

namespace Emeraldion\EmeRails\Models;

use Emeraldion\EmeRails\Db;

/**
 * @class RelationshipInstance
 * @short Models a relationship instance between two models of the associated relationship
 * @details TBD.
 */
#[\AllowDynamicProperties]
class RelationshipInstance
{
    const SQL_COMMAND_INSERT = 'INSERT';
    const SQL_COMMAND_INSERT_IGNORE = 'INSERT IGNORE';
    const SQL_COMMAND_UPDATE = 'UPDATE';
    const SQL_COMMAND_UPDATE_IGNORE = 'UPDATE IGNORE';

    /**
     * @attr values
     * @short Array of values for the columns of a relationship table.
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
        if ($relationship->cardinality === Relationship::MANY_TO_MANY) {
            $this->values[$member->get_foreign_key_name()] = $member->{$member->get_primary_key_name()};
            $this->values[$other_member->get_foreign_key_name()] =
                $other_member->{$other_member->get_primary_key_name()};
        }

        $this->validate();
    }

    /**
     * @fn of($classname)
     * @short Returns the member of the relationship of the requested class
     */
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

    /**
     * @fn ignore
     * @short Convenience method to set the <code>_ignore</code> flag in a streaming style
     */
    public function ignore(): self
    {
        $this->_ignore = true;
        return $this;
    }

    public function save()
    {
        $conn = Db::get_connection();

        $member_pk = $this->member->get_primary_key_name();
        $member_fk = $this->member->get_foreign_key_name();

        $other_member_pk = $this->other_member->get_primary_key_name();
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
                    throw new \Exception(
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

                if ($conn->affected_rows() > 0) {
                    $ret = true;
                }
                break;

            case Relationship::MANY_TO_MANY:
                $columns = $this->relationship->get_column_names();
                $ret = false;
                $nonempty = [];

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
                        if ($i > 0) {
                            $query .= ', ';
                        }
                        $query .= "`{$nonempty[$i]}` = {$this->wrap_value_for_query(
                            $nonempty[$i],
                            $this->values[$nonempty[$i]],
                            $conn
                        )}";
                    }
                    $query .= " WHERE `{$this->relationship->get_primary_key_name()}` = '{2}' LIMIT 1";
                    $conn->prepare(
                        $query,
                        $this->relationship->get_table_name(),
                        $this->values[$this->relationship->get_primary_key_name()]
                    );
                    $conn->exec();
                    if ($conn->affected_rows() > 0) {
                        $ret = true;
                    }
                } else {
                    $query =
                        (isset($this->_ignore)
                            ? get_called_class()::SQL_COMMAND_INSERT_IGNORE
                            : get_called_class()::SQL_COMMAND_INSERT) . ' INTO `{1}` (';
                    for ($i = 0; $i < count($nonempty); $i++) {
                        if ($i > 0) {
                            $query .= ', ';
                        }
                        $query .= "`{$nonempty[$i]}`";
                    }
                    $query .= ') VALUES (';
                    for ($i = 0; $i < count($nonempty); $i++) {
                        if ($i > 0) {
                            $query .= ', ';
                        }
                        $query .= $this->wrap_value_for_query($nonempty[$i], $this->values[$nonempty[$i]], $conn);
                    }
                    $query .= ')';
                    $conn->prepare($query, $this->relationship->get_table_name());
                    $conn->exec();
                    $insert_id = $conn->insert_id();
                    if ($insert_id !== 0) {
                        $this->values[$this->relationship->get_primary_key_name()] = $insert_id;
                    }
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
                    $this->member->$other_member_collection = [
                        $this->other_member->$other_member_pk => $this->other_member
                    ];
                }
                if (is_array($this->other_member->$member_collection)) {
                    if (!array_key_exists($this->member->$member_pk, $this->other_member->$member_collection)) {
                        $this->other_member->$member_collection[$this->member->$member_pk] = $this->member;
                    }
                } else {
                    $this->other_member->$member_collection = [
                        $this->member->$member_pk => $this->member
                    ];
                }

                break;
        }

        Db::close_connection($conn);

        return $ret;
    }

    public function delete()
    {
        $conn = Db::get_connection();

        $member_pk = $this->member->get_primary_key_name();
        $member_fk = $this->member->get_foreign_key_name();

        $other_member_pk = $this->other_member->get_primary_key_name();
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
                    throw new \Exception(
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

                if ($conn->affected_rows() > 0) {
                    $ret = true;
                }
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

                if ($conn->affected_rows() > 0) {
                    $ret = true;
                }
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
        [, $type] = $matches;
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
        return $this->relationship->validate_field($key, $value, $raise);
    }

    /**
     * @fn __set($key, $value)
     * @short Magic method to set the value of a property.
     * @param key The key of the property.
     * @param value The value of the property.
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
     * @fn __get($key)
     * @short Magic method to get the value of a property.
     * @param key The key of the desired property.
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
     * @fn __isset($key)
     * @short Magic method to determine if a property exists.
     * @param key The key to test.
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
     * @fn __unset($key)
     * @short Magic method to unset a property.
     * @param key The key to unset.
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

    public function as_sql(string $command = self::SQL_COMMAND_INSERT): ?string
    {
        if ($this->relationship->cardinality !== Relationship::MANY_TO_MANY) {
            return null;
        }

        $conn = Db::get_connection();

        $columns = $this->relationship->get_column_names();
        $nonempty = [];

        for ($i = 0; $i < count($columns); $i++) {
            if (
                // Do not overwrite the primary key
                $columns[$i] != $this->relationship->get_primary_key_name() &&
                // Exclude empty columns
                $this->values &&
                array_key_exists($columns[$i], $this->values) &&
                (isset($this->values[$columns[$i]]) || is_null($this->values[$columns[$i]]))
            ) {
                $nonempty[] = $columns[$i];
            }
        }

        $ret = '';
        switch ($command) {
            case self::SQL_COMMAND_INSERT:
            case self::SQL_COMMAND_INSERT_IGNORE:
                $ret = "{$command} INTO `{$this->relationship->get_table_name()}` (";

                for ($i = 0; $i < count($nonempty); $i += 1) {
                    if ($i > 0) {
                        $ret .= ', ';
                    }
                    $ret .= '`' . $nonempty[$i] . '`';
                }
                $ret .= ') VALUES (';

                for ($i = 0; $i < count($nonempty); $i += 1) {
                    if ($i > 0) {
                        $ret .= ', ';
                    }
                    $ret .= $this->wrap_value_for_query($nonempty[$i], $this->values[$nonempty[$i]], $conn);
                }
                $ret .= ");\n";
                break;
            case self::SQL_COMMAND_UPDATE:
            case self::SQL_COMMAND_UPDATE_IGNORE:
                $ret = $command . ' `' . $this->relationship->get_table_name() . '` SET ';

                for ($i = 0; $i < count($nonempty); $i += 1) {
                    if ($i > 0) {
                        $ret .= ', ';
                    }
                    $ret .= "`{$nonempty[$i]}` = {$this->wrap_value_for_query(
                        $nonempty[$i],
                        $this->values[$nonempty[$i]],
                        $conn
                    )}";
                }
                $ret .= " WHERE `{$this->relationship->get_primary_key_name()}` = {$this->wrap_value_for_query(
                    $this->relationship->get_primary_key_name(),
                    $this->values[$this->relationship->get_primary_key_name()],
                    $conn
                )};\n";
                break;
        }

        Db::close_connection($conn);

        return $ret;
    }
}
