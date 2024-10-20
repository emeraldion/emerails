<?php
/**
 *                                   _ __
 *   ___  ____ ___  ___  _________ _(_) /____
 *  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
 * /  __/ / / / / /  __/ /  / /_/ / / (__  )
 * \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
 *
 * (c) Claudio Procida 2008-2024
 *
 * @format
 */

require_once __DIR__ . '/../include/common.inc.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;
use Emeraldion\EmeRails\Models\Relationship;

/**
 *  @class ActiveRecord
 *  @short The abstract base class for DB-backed model objects.
 *  @details Every object of a subclass of ActiveRecord are mapped 1:1 to records of a table in
 *  a relational Database. Naming conventions assume that if the ActiveRecord subclass is called
 *  <tt>MyProduct</tt>, database records are stored in a table called <tt>my_products</tt>.
 *  Conversely, if an object of another class is in a relation with your object, it is assumed that a
 *  foreign key called <tt>my_product_id</tt> exists in the other table. Of course this is overridable
 *  by setting explicitly <tt>$table_name</tt> and <tt>$foreign_key</tt> to a value of your choice.
 */
#[\AllowDynamicProperties]
abstract class ActiveRecord
{
    const DEFAULT_PK_COLUMN_NAME = 'id';
    const PARAM_AS = 'as';
    const PARAM_JOIN = 'join';
    const PARAM_ORDER_BY = 'order_by';
    const PARAM_START = 'start';
    const PARAM_STRICT = 'strict';
    const PARAM_LIMIT = 'limit';
    const PARAM_WHERE_CLAUSE = 'where_clause';
    // key function used to index relationship pairs
    const PARAM_KEY_FN = 'key_fn';

    /**
     *  @const READONLY_COLUMNS
     *  @short Array of read-only columns that must not be written by us.
     *  @details This is a list of columns that must not be written by models, cause
     *  they're typically set or updated by DB triggers.
     */
    const READONLY_COLUMNS = ['created_at', 'updated_at'];

    /**
     *  @const WRITEONLY_COLUMNS
     *  @short Array of write-only columns that must not be printed.
     *  @details This is a list of columns representing potentially sensitive data,
     *  e.g. password hashes, that must never be printed.
     */
    const WRITEONLY_COLUMNS = ['password'];

    /**
     *  @attr columns
     *  @short Array of columns for the model object.
     */
    static $columns = [];

    /**
     *  @attr column_info
     *  @short Array of column info for the model object.
     */
    static $column_info = [];

    /**
     *  @attr class_initialized
     *  @short Array containing initialization information for subclasses.
     */
    static $class_initialized = [];

    /**
     *  @attr belongs_to_classes
     *  @short Array containing information on parent tables for subclasses.
     */
    static $belongs_to_classes = [];

    /**
     *  @attr has_many_classes
     *  @short Array containing information on child tables for subclasses.
     */
    static $has_many_classes = [];

    /**
     *  @attr object_pool
     *  @short Pool of objects already fetched from database.
     */
    static $object_pool = [];

    /**
     *  @attr table_name
     *  @short Name of the table bound to this model class.
     */
    protected $table_name;

    /**
     *  @attr primary_key
     *  @short Name of the primary key column for the bound table.
     *  @details Set this attribute only when the primary key of the bound table is not the canonical <tt>id</tt>.
     */
    protected $primary_key = self::DEFAULT_PK_COLUMN_NAME;

    /**
     *  @attr primary_key_name
     *  @short Name of the primary key column for the bound table.
     *  @details Set this attribute only when the primary key of the bound table is not the canonical <tt>id</tt>.
     */
    protected static $primary_key_name = null;

    /**
     *  @attr actual_primary_key_names
     *  @short Name of the actual primary key column for the bound table.
     *  @details This is a dictionary of class name to actual primary key column name.
     *  The class property is read-only and it is set to the actual primary key of the
     *  ActiveRecord subclass when introspecting columns of the bound table.
     */
    protected static $actual_primary_key_names = [];

    /**
     *  @attr foreign_key_name
     *  @short Used to create the name of foreign key column in tables that are in a relationship with the bound table.
     *  @details Set this attribute only when the foreign key that references objects of this class
     *  is not the canonical name (e.g. 'product' for class Product).
     */
    protected $foreign_key_name;

    /**
     *  @attr values
     *  @short Array of values for the columns of model object.
     */
    private $values;

    /**
     *  @attr volatile
     *  @short Marks the object as a volatile model
     *  @details Volatile model objects cannot be persisted, i.e. saving and deleting won't
     *  work, and will trigger an <tt>E_USER_NOTICE</tt> error.
     */
    public $volatile = false;

    /**
     *  @fn __construct($_values)
     *  @short Constructs and initializes an ActiveRecord object.
     *  @details Due to the lack of a static class initialization method,
     *  the default constructor is in charge of gathering information about
     *  the bound table columns the first time an object is created. Subsequent
     *  creations will use the values stored in static class variables.
     *  Subclassers don't need to override the constructor. They can in turn
     *  override the <tt>init</tt> method in order to perform custom initialization.
     *  @param values Column values to initialize the object.
     */
    function __construct($_values = null)
    {
        $conn = Db::get_connection();

        $classname = get_class($this);
        $initialized = self::_is_initialized($classname);
        if (!$initialized) {
            $conn->prepare('DESCRIBE `{1}`', $this->get_table_name());
            $conn->exec();
            $columns = [];
            $column_info = [];
            while ($row = $conn->fetch_assoc()) {
                $columns[] = $row['Field'];
                $column_info[] = $row;
                if ($row['Key'] == 'PRI') {
                    self::$actual_primary_key_names[get_called_class()] = $row['Field'];
                }
            }
            self::_set_columns($classname, $columns);
            self::_set_column_info($classname, $column_info);
            self::_set_initialized($classname, true);
        }
        $columns = self::_get_columns($classname);
        if (!empty($_values)) {
            $this->values = [];
            foreach ($_values as $key => $val) {
                if (in_array($key, $columns)) {
                    // We can't be strict here as the data read from DB is untyped :(
                    $this->values[$key] = $this->validate_field($key, $val);
                }
            }
        }
        $this->init($_values);

        Db::close_connection($conn);
    }

    /**
     *  @fn init($values)
     *  @short Performs specialized initialization tasks.
     *  @details Subclassers will use this method to perform custom initialization.
     *  @note The default implementation simply does nothing.
     *  @param values An array of column-value pairs to initialize the receiver.
     */
    protected function init($values)
    {
        $this->primary_key = $this::$primary_key_name ?? $this->primary_key;
    }

    /**
     *  @fn get_table_name
     *  @short Returns the name of the table bound to this class.
     *  @details This method returns the name of the table which contains
     *  data for objects of this class. If the ActiveRecord subclass is called <tt>MyRecord</tt>,
     *  the table name will be <tt>my_records</tt>. Of course you can override this behavior by
     *  setting explicitly the value of <tt>$table_name</tt> in the declaration of your class.
     */
    public function get_table_name()
    {
        if (!$this->table_name) {
            $classname = get_class($this);
            $parts = explode('\\', $classname);
            $classname = $parts[count($parts) - 1];
            $this->table_name = class_name_to_table_name($classname);
        }
        return $this->table_name;
    }

    /**
     *  @fn get_relationship_table_half_name
     *  @short Returns the half name of the relationship table for this class.
     *  @details This method returns the half name of the table which represents
     *  relationship data for objects of this class when in a many to many relationship with another
     *  class. If the ActiveRecord subclass is called <tt>Dog</tt>, the table half name will be <tt>dogs</tt>.
     *
     *  When in a relationship with another class, e.g. <tt>Owner</tt>, the resulting relationship table will be obtained
     *  by joining the two halves, sorted alphabetically, i.e. <tt>dogs_owners</tt>.
     *
     *  Subclassers can set a custom half name by setting explicitly the value of <tt>$relationship_table_half_name</tt>
     *  in the declaration of the class. If not overridden, this defaults to the return value of <tt>get_table_name()</tt>
     */
    public function get_relationship_table_half_name()
    {
        if (isset($this->relationship_table_half_name)) {
            return $this->relationship_table_half_name;
        }
        return $this->get_table_name();
    }

    /**
     *  @fn get_foreign_key_name
     *  @short Returns the name of the foreign key for this class.
     *  @details This method returns the name of the column to lookup when considering relations
     *  with objects of this class. If the ActiveRecord subclass is called <tt>MyRecord</tt>,
     *  the foreign key name will be <tt>my_record_id</tt>. Of course you can override this behavior by
     *  setting explicitly the value of <tt>$foreign_key_name</tt> in the declaration of your class.
     */
    public function get_foreign_key_name()
    {
        if (empty($this->foreign_key_name)) {
            $classname = get_class($this);
            $this->foreign_key_name = class_name_to_foreign_key($classname);
        }
        return $this->foreign_key_name;
    }

    /**
     *  @fn get_primary_key
     *  @short Returns the name of the primary key for this class.
     *  @details This method returns the name of the primary key in the table bound to this class.
     *  By default, ActiveRecord considers as primary key a column named <tt>id</tt>. Of course you can override
     *  this behavior by setting explicitly the value of <tt>$primary_key</tt> in the declaration of your class.
     */
    public function get_primary_key()
    {
        // Set to primary_key member for backwards compatibility
        $ret = $this->primary_key;
        // Set to static primary_key_name member (new way)
        if ($this::$primary_key_name) {
            $ret = $this::$primary_key_name;
        }
        return $ret;
    }

    /**
     *  @fn has_column($key)
     *  @short Verifies the existence of a column named <tt>key</tt> in the bound table.
     *  @param key The name of the column to check.
     */
    public function has_column($key)
    {
        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        return in_array($key, $columns);
    }

    /**
     *  @fn get_column_names()
     *  @short Returns a list of column names in the bound table, equivalent to the object's fields
     */
    public function get_column_names()
    {
        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        return $columns;
    }

    /**
     *  @fn get_column_names_for_query($with_prefix = false)
     *  @short Returns the list of column names for a SELECT query
     *  @details This method can be used to return a list of columns for a query. Additionally, the caller
     *  can request the column names to be aliased for multiplexing in a multi-table query, e.g. a JOIN.
     *  @param with_prefix Set to true to create aliases with the table name as a prefix
     */
    public function get_column_names_for_query($with_prefix = false)
    {
        $columns = array_map(function ($c) use ($with_prefix) {
            return $with_prefix
                ? sprintf('`%s`.`%s` AS `%s:%s`', $this->get_table_name(), $c, $this->get_table_name(), $c)
                : sprintf('`%s`.`%s`', $this->get_table_name(), $c);
        }, $this->get_column_names());
        return $columns;
    }

    /**
     *  @fn demux_column_names($columns)
     *  @short Demuxes a list of prefixed columns to intercept values of interest
     *  @details This method can be used to filter a list of columns returned by a multi-table query, capturing
     *  only those of interest to the receiving object.
     *  @param columns The list of columns to filter
     *  @param validate Validates the values of the columns
     */
    public function demux_column_names($columns, $validate = false)
    {
        $ret = [];
        foreach ($columns as $muxed_key => $val) {
            if (strpos($muxed_key, $this->get_table_name()) === 0) {
                $key = last(explode(':', $muxed_key));
                $ret[$key] = $validate ? $this->validate_field($key, $val) : $val;
            }
        }
        return $ret;
    }

    /**
     *  @fn belongs_to($class_or_table_name)
     *  @short Loads the parent of the receiver in a one-to-many relationship.
     *  @details This method tries to find the owner of the current object via its foreign key. The method
     *  returns early if the fk column is null.
     *  @param class_or_table_name The name of the parent class or table.
     *  @param params An array of conditions. For the semantics, see find_all
     *  @return ret Handle to the relationship between the receiver class and the parent class
     */
    public function belongs_to($class_or_table_name, $params = [])
    {
        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        try {
            // Assume class name and obtain table name
            $ownerclass = $class_or_table_name;
            $owner = new $ownerclass();
            $table_name = $owner->get_table_name();
        } catch (Throwable $t) {
            // Assume table name and infer class name
            $table_name = $class_or_table_name;
            $ownerclass = table_name_to_class_name($table_name);
            $owner = new $ownerclass();
            trigger_error(
                sprintf(
                    '%s::%s was invoked with a table name instead of a class name. This behavior is deprecated and will be removed in a future milestone. Please refactor your code to use class names.',
                    get_class($this),
                    __FUNCTION__
                ),
                E_USER_DEPRECATED
            );
        }
        $fk = null;
        if (in_array(table_name_to_foreign_key($table_name), $columns)) {
            $fk = $this->values[table_name_to_foreign_key($table_name)];
        } elseif (in_array($owner->get_foreign_key_name(), $columns)) {
            $fk = $this->values[$owner->get_foreign_key_name()];
        }
        $ret = false;
        if ($fk) {
            $ret = $owner->find_by_id($fk);
        }
        $owner_member_name = isset($params[self::PARAM_AS])
            ? $params[self::PARAM_AS]
            : camel_case_to_joined_lower($ownerclass);
        if ($ret) {
            $this->values[$owner_member_name] = $owner;
            $owner->values[camel_case_to_joined_lower(get_class($this))] = $this;

            return Relationship::one_to_one(get_called_class(), $ownerclass)->between($this, $owner);
        }
        // Unset previously set value
        unset($this->values[$owner_member_name]);

        return $ret;
    }

    /**
     *  @fn has_many($class_or_table_name, $params)
     *  @short Loads the children of the receiver in a one-to-many relationship.
     *  @param class_or_table_name The name of the child class or table.
     *  @param params An array of conditions. For the semantics, see find_all
     *  @return ret Handle to the relationship between the receiver class and the child class
     *  @see find_all
     */
    public function has_many($class_or_table_name, $params = [])
    {
        try {
            // Assume class name and obtain table name
            $childclass = $class_or_table_name;
            $child = new $childclass();
            $table_name = $child->get_table_name();
        } catch (Throwable $t) {
            // Assume table name and infer class name
            $table_name = $class_or_table_name;
            $childclass = table_name_to_class_name($table_name);
            $child = new $childclass();
            trigger_error(
                sprintf(
                    '%s::%s was invoked with a table name instead of a class name. This behavior is deprecated and will be removed in a future milestone. Please refactor your code to use class names.',
                    get_class($this),
                    __FUNCTION__
                ),
                E_USER_DEPRECATED
            );
        }
        $fkey = $this->get_foreign_key_name();
        if (isset($params[self::PARAM_WHERE_CLAUSE])) {
            $params[
                self::PARAM_WHERE_CLAUSE
            ] = "({$params[self::PARAM_WHERE_CLAUSE]}) AND `{$fkey}` = '{$this->values[$this->get_primary_key()]}' ";
        } else {
            $params[self::PARAM_WHERE_CLAUSE] = "`{$fkey}` = '{$this->values[$this->get_primary_key()]}' ";
        }
        $children = $child->find_all($params);
        $child_pk = $child->get_primary_key();
        $child_member_name = isset($params[self::PARAM_AS])
            ? $params[self::PARAM_AS]
            : pluralize(camel_case_to_joined_lower($childclass));
        if (is_array($children) && count($children) > 0) {
            $dict = [];
            foreach ($children as $child) {
                $child_key =
                    isset($params[self::PARAM_KEY_FN]) && is_callable($params[self::PARAM_KEY_FN])
                        ? $params[self::PARAM_KEY_FN]($child)
                        : $child->$child_pk;
                $child->values[camel_case_to_joined_lower(get_class($this))] = $this;
                $dict[$child_key] = $child;
            }
            $this->values[$child_member_name] = $dict;

            return Relationship::one_to_many(get_called_class(), $childclass)->among([$this], array_values($dict));
        } else {
            // Unset previously set value
            unset($this->values[$child_member_name]);
        }
        return false;
    }

    /**
     *  @fn has_and_belongs_to_many($class_or_table_name, $params)
     *  @short Loads the object network the receiver belongs to in a many-to-many relationship.
     *  @param class_or_table_name The name of the peer class or table.
     *  @param params An array of conditions. For the semantics, see find_all
     *  @return ret Handle to the relationship between the receiver class and the peer class
     *  @see find_all
     */
    public function has_and_belongs_to_many($class_or_table_name, $params = [])
    {
        $conn = Db::get_connection();

        try {
            // Assume class name and obtain table name
            $peerclass = $class_or_table_name;
            $peer = new $peerclass();
            $table_name = $peer->get_table_name();
        } catch (Throwable $t) {
            // Assume table name and infer class name
            $table_name = $class_or_table_name;
            $peerclass = table_name_to_class_name($table_name);
            $peer = new $peerclass();
            trigger_error(
                sprintf(
                    '%s::%s was invoked with a table name instead of a class name. This behavior is deprecated and will be removed in a future milestone. Please refactor your code to use class names.',
                    get_class($this),
                    __FUNCTION__
                ),
                E_USER_DEPRECATED
            );
        }

        $pkey = $this->get_primary_key();
        $fkey = $this->get_foreign_key_name();
        $peer_pk = $peer->get_primary_key();
        $peer_fkey = $peer->get_foreign_key_name();

        $r = Relationship::many_to_many(get_called_class(), $peerclass);
        $relation_table = $r->get_table_name();

        $query = 'SELECT `{2}`.*, {12} {14} {13} FROM `{1}` JOIN `{2}` ON `{1}`.`{3}` = `{2}`.`{4}`';
        if (!empty($params[self::PARAM_JOIN])) {
            $has_join = true;
            $joined_classname = $params[self::PARAM_JOIN];
            $joined_obj = new $joined_classname();

            if ($joined_obj->has_column($peer_fkey)) {
                $query .= ' JOIN `{9}` ON `{2}`.`{4}` = `{9}`.`{3}`';
            } elseif ($peer->has_column($joined_obj->get_foreign_key_name())) {
                $query .= ' JOIN `{9}` ON `{2}`.`{11}` = `{9}`.`{10}`';
            }
        } else {
            $has_join = false;
        }

        $query .=
            " WHERE (`{1}`.`{5}` = '{6}' AND " .
            ($params[self::PARAM_WHERE_CLAUSE] ?? '1') .
            ') ' .
            'ORDER BY ' .
            ($params[self::PARAM_ORDER_BY] ?? '`{5}` ASC') .
            ' LIMIT {7}, {8}';

        $conn->prepare(
            $query,
            $relation_table, // 1
            $table_name, // 2
            $peer_fkey, // 3
            $peer_pk, // 4
            $fkey, // 5
            $this->values[$pkey], // 6
            $params[self::PARAM_START] ?? 0, // 7
            $params[self::PARAM_LIMIT] ?? 9999, // 8
            $has_join ? $joined_obj->get_table_name() : null, // 9
            $has_join ? $joined_obj->get_primary_key() : null, // 10
            $has_join ? $joined_obj->get_foreign_key_name() : null, // 11
            implode(',', $peer->get_column_names_for_query(true)), // 12
            ',' . implode(',', $r->get_column_names_for_query(true)), // 13
            $has_join ? ',' . implode(',', $joined_obj->get_column_names_for_query(true)) : '' // 14
        );
        $conn->exec();

        $ret = false;
        $peer_member_name = isset($params[self::PARAM_AS])
            ? $params[self::PARAM_AS]
            : pluralize(camel_case_to_joined_lower($peerclass));
        if ($conn->num_rows() > 0) {
            $this->values[$peer_member_name] = [];
            $dict = [];
            while ($row = $conn->fetch_assoc()) {
                // Fetch peer fields
                $peer_row = $peer->demux_column_names($row);
                // Fetch relationship fields with validation
                $r_row = $r->demux_column_names($row, true);
                // Fixup pkey from fkey
                $peer_row[$peer_pk] = $r_row[$peer_fkey];
                // Instantiate the peer
                $peer = new $peerclass($peer_row);
                // Compute the peer's key in the caller's collection
                $peer_key =
                    isset($params[self::PARAM_KEY_FN]) && is_callable($params[self::PARAM_KEY_FN])
                        ? $params[self::PARAM_KEY_FN]($peer)
                        : $peer->$peer_pk;

                // Store the peer in the caller's collection
                $this->values[$peer_member_name][$peer_key] = $peer;
                // FIXME: this result is not reflecting the entire relationship. The peer may have more edges to
                // the caller's class besides the caller itself. However, for convenience and performance, we only
                // set the caller in the peer's collection.
                $peer->values[pluralize(camel_case_to_joined_lower(get_class($this)))] = [$this->$pkey => $this];

                if ($has_join) {
                    $joined_obj = new $joined_classname($joined_obj->demux_column_names($row));
                    $peer->values[camel_case_to_joined_lower($joined_classname)] = $joined_obj;
                    $joined_obj->values[camel_case_to_joined_lower($peerclass)] = $peer;
                }

                // This is the new way to access relationship attributes
                $dict[$r_row[$peer_fkey]] = $r_row;

                // Remove known id columns to prevent clobbering relationship attributes
                unset($r_row[self::DEFAULT_PK_COLUMN_NAME]);
                unset($r_row[$fkey]);
                unset($r_row[$peer_fkey]);

                // Deprecated: store relationship attributes in the peer
                foreach ($r_row as $key => $value) {
                    $peer->values[$key] = $value;
                }
            }

            $ret = $r->among([$this], array_values($this->values[$peer_member_name]), [$this->values[$pkey] => $dict]);
        } else {
            // Unset previously set value
            unset($this->values[$peer_member_name]);
        }
        $conn->free_result();

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *  @fn has_one($class_or_table_name)
     *  @short Loads the child of the receiver in a one-to-one relationship.
     *  @param class_or_table_name The name of the child class or table.
     *  @param params An array of conditions. For the semantics, see find_all
     *  @return ret Handle to the relationship between the receiver class and the child class
     *  @see find_all
     */
    public function has_one($class_or_table_name, $params = [])
    {
        try {
            // Assume class name and obtain table name
            $childclass = $class_or_table_name;
            $child = new $childclass();
            $table_name = $child->get_table_name();
        } catch (Throwable $t) {
            // Assume table name and infer class name
            $table_name = $class_or_table_name;
            $childclass = table_name_to_class_name($table_name);
            $child = new $childclass();
            trigger_error(
                sprintf(
                    '%s::%s was invoked with a table name instead of a class name. This behavior is deprecated and will be removed in a future milestone. Please refactor your code to use class names.',
                    get_class($this),
                    __FUNCTION__
                ),
                E_USER_DEPRECATED
            );
        }

        $fkey = $this->get_foreign_key_name();
        $children = $child->find_all([
            self::PARAM_WHERE_CLAUSE => "`{$fkey}` = '{$this->values[$this->primary_key]}'",
            // Do not limit results in strict mode, so we can raise if we get more than one
            'limit' => isset($params[self::PARAM_STRICT]) ? null : 1
        ]);
        if (is_array($children) && count($children) > 0) {
            if (isset($params[self::PARAM_STRICT]) && count($children) > 1) {
                throw new Exception(sprintf('Only one child expected, but found %d', count($children)));
            }
            $child = first($children);
            $child->values[camel_case_to_joined_lower(get_class($this))] = $this;
            $child_member_name = isset($params[self::PARAM_AS])
                ? $params[self::PARAM_AS]
                : camel_case_to_joined_lower($childclass);
            $this->values[$child_member_name] = $child;

            return Relationship::one_to_one(get_called_class(), $childclass)->between($this, $child);
        } else {
            // Unset previously set value
            unset($this->values[camel_case_to_joined_lower($childclass)]);
        }
        return false;
    }

    /**
     *  Finder methods
     */

    /**
     *  @fn find_by_query($query)
     *  @short Returns an array of model objects by executing a custom SELECT query.
     *  @details This is a powerful instance method to retrieve objects from the database with a custom query.
     *  You can, among other things, do LEFT JOIN queries here.
     *  @param query The SELECT query to fetch objects.
     */
    public function find_by_query($query)
    {
        $conn = Db::get_connection();

        $ret = null;

        $conn->prepare($query);
        $conn->exec();
        if ($conn->num_rows() > 0) {
            $classname = get_class($this);
            $results = [];
            while ($row = $conn->fetch_assoc()) {
                $obj = new $classname();
                $obj->find_by_id($row[$this->primary_key]);
                $results[] = $obj;
            }
            $ret = $results;
        }
        $conn->free_result();

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *  @fn find_all($params)
     *  @short Returns an array of model objects that satisfy the requirements expressed in the <tt>params</tt> argument.
     *  @details This method lets you find all objects of this class that satisfy a custom set of requirements, which you
     *  can express by setting the following keys of the <tt>params</tt> argument:
     *  @li <tt>where_clause</tt> You can express a custom SQL WHERE expression here (e.g. `date` < '2008-05-01')
     *  @li <tt>order_by</tt> You can express a custom SQL ORDER BY expression here (e.g. `date` DESC)
     *  @li <tt>limit</tt> You can express a custom limit for the returned results.
     *  @li <tt>start</tt> You can express a custom start for the returned results.
     *  Additionally, the caller can pass a class name as value of the <tt>join</tt> key, in order to perform a JOIN query
     *  to return an associated model object. Currently, only 1:1 relationships are supported.
     *  @param params An array of parameters for the underlying SQL query.
     */
    function find_all($params = [])
    {
        $conn = Db::get_connection();

        if (empty($params[self::PARAM_WHERE_CLAUSE])) {
            $params[self::PARAM_WHERE_CLAUSE] = '1';
        }
        if (empty($params[self::PARAM_ORDER_BY])) {
            $params[self::PARAM_ORDER_BY] = "`{$this->get_table_name()}`.`{$this->get_primary_key()}` ASC";
        }
        if (empty($params[self::PARAM_LIMIT])) {
            $params[self::PARAM_LIMIT] = 999;
        }
        if (empty($params[self::PARAM_START])) {
            $params[self::PARAM_START] = 0;
        }

        $ret = null;

        if (!empty($params[self::PARAM_JOIN])) {
            $has_join = true;
            $joined_classname = $params[self::PARAM_JOIN];
            $joined_obj = new $joined_classname();
            if (
                $joined_obj->has_column($this->get_foreign_key_name()) &&
                // Edge case: this model's foreign key name is the joined object's primary key
                $this->get_foreign_key_name() != $joined_obj->get_primary_key()
            ) {
                $query = 'SELECT {7} FROM `{1}` JOIN `{4}` ON `{1}`.`{2}` = `{4}`.`{3}`';
            } elseif ($this->has_column($joined_obj->get_foreign_key_name())) {
                $query = 'SELECT {7} FROM `{1}` JOIN `{4}` ON `{1}`.`{6}` = `{4}`.`{5}`';
            } else {
                trigger_error(
                    sprintf(
                        '[%s::%s] Failed to find a foreign key column `%s` in table `%s` or `%s` in table `%s`.',
                        get_class($this),
                        __FUNCTION__,
                        $this->get_foreign_key_name(),
                        $joined_obj->get_table_name(),
                        $joined_obj->get_foreign_key_name(),
                        $this->get_table_name()
                    ),
                    E_USER_ERROR
                );
            }
            $query .= " WHERE (1 AND ({$params[self::PARAM_WHERE_CLAUSE]})) ORDER BY {$params[self::PARAM_ORDER_BY]} LIMIT {$params[self::PARAM_START]}, {$params[self::PARAM_LIMIT]}";
            $conn->prepare(
                $query,
                $this->get_table_name(), // 1
                $this->get_primary_key(), // 2
                $this->get_foreign_key_name(), // 3
                $joined_obj->get_table_name(), // 4
                $joined_obj->get_primary_key(), // 5
                $joined_obj->get_foreign_key_name(), // 6,
                implode(
                    ',',
                    array_merge($this->get_column_names_for_query(true), $joined_obj->get_column_names_for_query(true))
                ) // 7
            );
        } else {
            $has_join = false;
            $conn->prepare(
                "SELECT * FROM `{1}` WHERE (1 AND ({$params[self::PARAM_WHERE_CLAUSE]})) ORDER BY {$params[self::PARAM_ORDER_BY]} LIMIT {$params[self::PARAM_START]}, {$params[self::PARAM_LIMIT]}",
                $this->get_table_name()
            );
        }
        $conn->exec();
        if ($conn->num_rows() > 0) {
            $classname = get_class($this);
            $results = [];
            while ($row = $conn->fetch_assoc()) {
                $obj = new $classname($has_join ? $this->demux_column_names($row) : $row);
                $results[] = $obj;

                if ($has_join) {
                    $joined_obj = new $joined_classname($joined_obj->demux_column_names($row));
                    $obj->values[camel_case_to_joined_lower($joined_classname)] = $joined_obj;
                    $joined_obj->values[camel_case_to_joined_lower($classname)] = $obj;
                }
            }
            $ret = $results;
        }
        $conn->free_result();

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *  @fn find_one($params)
     *  @short Returns a single model object that satisfies the requirements expressed in the <tt>params</tt> argument.
     *  @details This method lets you find a single object that satisfies a custom set of requirements, which you
     *  can express by setting the following keys of the <tt>params</tt> argument:
     *  @li <tt>where_clause</tt> You can express a custom SQL WHERE expression here (e.g. `date` < '2008-05-01')
     *  @li <tt>order_by</tt> You can express a custom SQL ORDER BY expression here (e.g. `date` DESC)
     *  @li <tt>start</tt> You can express a custom start for the returned results.
     *  This method behaves like <tt>find_all()</tt>, except the return value is a single object instead of an array,
     *  and the <tt>limit</tt> key of the <tt>params</tt> argument is implicitly set to 1.
     *  If no model object satisfies the requirements, this method returns null.
     *  @param params An array of parameters for the underlying SQL query.
     *  @return ret Model object satisfying the requirements, or null
     *  @see find_all
     */
    function find_one($params = [])
    {
        if ($ret = $this->find_all(array_merge($params, ['limit' => 1]))) {
            return first($ret);
        }
        return null;
    }

    /**
     *  @fn find($id, $classname)
     *  @short Returns an object whose primary key value is <tt>id</tt>.
     *  @details This method historically accepts a second argument to explicitly reference the name of the ActiveRecord subclass in order to
     *  create the right object with older PHP versions. This is now deprecated as no longer necessary.
     *  @param id The value of the primary key.
     *  @param classname The name of the subclass to apply this static method to.
     *  @return ret Model object with the requested primary key, or null
     */
    static function find($id, $classname = 'ActiveRecord')
    {
        if ($classname != 'ActiveRecord') {
            trigger_error(
                sprintf(
                    "%s::%s was invoked with a second argument '%s'. This is deprecated and will be removed in a future milestone. Please refactor your code to remove the second argument.",
                    get_called_class(),
                    __FUNCTION__,
                    $classname
                ),
                E_USER_DEPRECATED
            );
        } else {
            $classname = get_called_class();
        }
        if (Config::get('OBJECT_POOL_ENABLED')) {
            $obj = self::_get_from_pool($classname, $id);
            if ($obj) {
                return $obj;
            }
        }
        $obj = new $classname();
        if ($obj->find_by_id($id)) {
            return $obj;
        }
        return null;
    }

    /**
     *  @fn find_by_id($id)
     *  @short Populates an object with the values of the DB row whose primary key value is <tt>id</tt>.
     *  @details This instance method populates the receiver object with the contents of the DB row whose
     *  primary key is <tt>id</tt>.
     *  @param id The primary key of the desired DB row.
     *  @return This method returns TRUE if such row exists, FALSE otherwise.
     */
    public function find_by_id($id)
    {
        $conn = Db::get_connection();

        $ret = false;

        $conn->prepare(
            "SELECT * FROM `{1}` WHERE `{$this->primary_key}` = '{2}' LIMIT 1",
            $this->get_table_name(),
            $id
        );
        $conn->exec();
        if ($conn->num_rows() > 0) {
            $classname = get_class($this);
            $columns = self::_get_columns($classname);
            $values = $conn->fetch_assoc();
            foreach ($columns as $column) {
                $this->values[$column] = $this->validate_field($column, $values[$column]);
            }
            self::_add_to_pool($classname, $id, $this);

            $ret = true;
        }

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *  @fn count_all($params)
     *  @short Returns the count of model objects that satisfy the requirements expressed in the <tt>params</tt> argument.
     *  @details This method lets you count all objects of this class that satisfy a custom set of requirements, which you
     *  can express by setting the following keys of the <tt>params</tt> argument:
     *  @li <tt>where_clause</tt> You can express a custom SQL WHERE expression here (e.g. `date` < '2008-05-01')
     *  @param params An array of parameters for the underlying SQL query.
     *  @return ret Count of model objects satisfying the requirements
     */
    public function count_all($params = [])
    {
        $conn = Db::get_connection();

        $ret = 0;

        if (empty($params[self::PARAM_WHERE_CLAUSE])) {
            $params[self::PARAM_WHERE_CLAUSE] = '1';
        }
        if (!empty($params[self::PARAM_JOIN])) {
            $joined_classname = $params[self::PARAM_JOIN];
            $joined_obj = new $joined_classname();
            if ($joined_obj->has_column($this->get_foreign_key_name())) {
                $query = 'SELECT COUNT(*) FROM `{1}` JOIN `{4}` ON `{1}`.`{2}` = `{4}`.`{3}`';
            } elseif ($this->has_column($joined_obj->get_foreign_key_name())) {
                $query = 'SELECT COUNT(*) FROM `{1}` JOIN `{4}` ON `{1}`.`{6}` = `{4}`.`{5}`';
            } else {
                trigger_error(
                    sprintf(
                        '[%s::%s] Failed to find a foreign key column `%s` in table `%s` or `%s` in table `%s`.',
                        get_class($this),
                        __FUNCTION__,
                        $this->get_foreign_key_name(),
                        $joined_obj->get_table_name(),
                        $joined_obj->get_foreign_key_name(),
                        $this->get_table_name()
                    ),
                    E_USER_ERROR
                );
            }
            $query .= " WHERE (1 AND ({$params[self::PARAM_WHERE_CLAUSE]}))";
            $conn->prepare(
                $query,
                $this->get_table_name(), // 1
                $this->get_primary_key(), // 2
                $this->get_foreign_key_name(), // 3
                $joined_obj->get_table_name(), // 4
                $joined_obj->get_primary_key(), // 5
                $joined_obj->get_foreign_key_name(), // 6,
                implode(
                    ',',
                    array_merge($this->get_column_names_for_query(true), $joined_obj->get_column_names_for_query(true))
                ) // 7
            );
        } else {
            $conn->prepare(
                "SELECT COUNT(*) FROM `{1}` WHERE (1 AND ({$params[self::PARAM_WHERE_CLAUSE]}))",
                $this->get_table_name()
            );
        }
        $result = $conn->exec();

        $ret = (int) first($conn->fetch_array());

        $conn->free_result();

        Db::close_connection($conn);

        return $ret;
    }

    protected function wrap_value_for_query($key, $value, $conn)
    {
        if (is_null($value)) {
            return 'NULL';
        }
        $classname = get_class($this);
        $column_info = self::_get_column_info($classname);
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

    /**
     * @fn ignore
     * @short Convenience method to set the <code>_ignore</code> flag in a streaming style
     */
    public function ignore(): self
    {
        $this->_ignore = true;
        return $this;
    }

    /**
     *  @fn save
     *  @short Requests the receiver to save its data in the bound table.
     *  @details This method has two distinct effects. If called on an object fetched
     *  from the table, it performs an <tt>UPDATE</tt> SQL statement to update the
     *  table data to the new values. If called on an object created programmatically, it
     *  performs an <tt>INSERT</tt> SQL statement, and sets the object's primary key
     *  value to the value resulting by the insert.
     *  @return This method returns TRUE if the object has been saved successfully.
     */
    public function save()
    {
        if ($this->volatile) {
            trigger_error(
                sprintf(
                    '[%s::%s] This model object is volatile and cannot be saved.',
                    get_called_class(),
                    __FUNCTION__
                ),
                E_USER_NOTICE
            );
            return;
        }

        $conn = Db::get_connection();

        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        $ret = false;
        $nonempty = [];

        $this->validate(true);

        for ($i = 0; $i < count($columns); $i++) {
            if (
                // Do not set the primary key unless we're creating a new row
                ($columns[$i] != $this->get_primary_key() || $this->_force_create) &&
                // Exclude read-only columns
                !in_array($columns[$i], self::READONLY_COLUMNS) &&
                // Exclude empty columns
                $this->values &&
                array_key_exists($columns[$i], $this->values) &&
                (isset($this->values[$columns[$i]]) || is_null($this->values[$columns[$i]]))
            ) {
                $nonempty[] = $columns[$i];
            }
        }

        if (!empty($this->values[$this->get_primary_key()]) && !isset($this->_force_create)) {
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
            $query .= " WHERE `{$this->get_primary_key()}` = '{2}' LIMIT 1";
            $conn->prepare($query, $this->get_table_name(), $this->values[$this->get_primary_key()]);
            $conn->exec();
            $ret = true;
        } else {
            $query = (isset($this->_ignore) ? 'INSERT IGNORE' : 'INSERT') . ' INTO `{1}` (';
            for ($i = 0; $i < count($nonempty); $i++) {
                $query .= "`{$nonempty[$i]}`";
                if ($i < count($nonempty) - 1) {
                    $query .= ', ';
                }
            }
            $query .= ') VALUES (';
            for ($i = 0; $i < count($nonempty); $i++) {
                $query .= $this->wrap_value_for_query($nonempty[$i], $this->values[$nonempty[$i]], $conn);
                if ($i < count($nonempty) - 1) {
                    $query .= ', ';
                }
            }
            $query .= ')';
            $conn->prepare($query, $this->get_table_name());
            $conn->exec();
            $insert_id = $conn->insert_id();
            if ($insert_id !== 0) {
                $this->values[self::$actual_primary_key_names[get_called_class()]] = $insert_id;
            }
            if ($conn->affected_rows() > 0) {
                $ret = true;
            }
        }

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *  @fn delete($optimize)
     *  @short Deletes an object's database counterpart.
     *  @details This method performs a <tt>DELETE</tt> SQL statement on the
     *  table bound to the receiver's class, requesting the deletion of the object whose
     *  primary key is equal to the receiver's primary key value. If the object has been
     *  created programmatically and lacks a primary key value, this method has no effect.
     *  @param bool optimize Set to <tt>true</tt> if you want the table to be optimized after deletion.
     */
    public function delete($optimize = false)
    {
        $ret = false;

        if ($this->volatile) {
            trigger_error(
                sprintf(
                    '[%s::%s] This model object is volatile and cannot be deleted.',
                    get_called_class(),
                    __FUNCTION__
                ),
                E_USER_NOTICE
            );
            return $ret;
        }

        if (!empty($this->values[$this->primary_key])) {
            $conn = Db::get_connection();

            $conn->prepare(
                "DELETE FROM `{1}` WHERE `{$this->primary_key}` = '{2}' LIMIT 1",
                $this->get_table_name(),
                $this->values[$this->primary_key]
            );
            $conn->exec();
            if ($conn->affected_rows() > 0) {
                $ret = true;
            }

            self::_delete_from_pool(get_called_class(), $this->values[$this->primary_key]);

            // Clean up
            if ($optimize) {
                $conn->prepare('OPTIMIZE TABLE `{1}`', $this->get_table_name());
                $conn->exec();
            }

            Db::close_connection($conn);
        }

        return $ret;
    }

    protected function validate($raise = false)
    {
        if (!$this->values) {
            return;
        }

        $classname = get_class($this);
        $columns = self::_get_columns($classname);

        foreach ($columns as $column) {
            if (
                // Do not set the primary key unless we're creating a new row
                ($column != $this->get_primary_key() || $this->_force_create) &&
                // Exclude read-only columns
                !in_array($column, self::READONLY_COLUMNS) &&
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
        $classname = get_class($this);
        $column_info = self::_get_column_info($classname);
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
                    throw new Exception(
                        sprintf("%s: Attempt to null the field '%s' but it is not nullable", get_called_class(), $key)
                    );
                }
                $ret = null;
            } else {
                preg_match('/([a-z]+)(\((.+)\))?/', $info['Type'], $matches);
                [, $type] = $matches;

                switch ($type) {
                    case 'enum':
                        $possible_values = array_map(function ($value) {
                            return trim($value, '\'');
                        }, explode(',', $matches[3]));
                        if (!(is_null($value) || in_array($value, $possible_values))) {
                            if ($raise) {
                                throw new Exception(
                                    sprintf(
                                        "%s: Attempt to set the field '%s' to a value with incorrect type. Expected '%s(%s)' but found: '%s'",
                                        get_called_class(),
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
                                    "%s: Attempt to set the field '%s' to a value with incorrect type. Expected '%s(%d)' but found: '%s'",
                                    get_called_class(),
                                    $key,
                                    $type,
                                    $max_length,
                                    gettype($value)
                                )
                            );
                        }
                        $ret = is_null($value) ? null : (int) $value;
                        break;
                    case 'decimal':
                    case 'float':
                        if ($raise && !is_null($value) && !is_float($value)) {
                            throw new Exception(
                                sprintf(
                                    "%s: Attempt to set the field '%s' to a value with incorrect type. Expected 'float' but found: '%s'",
                                    get_called_class(),
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
     *  @fn relative_url
     *  @short Provides a relative URL that will be used by the <tt>permalink</tt> public method.
     *  @details Subclassers that wish to provide custom permalinks for objects should override this method.
     *  You should return the URL portion after the <tt>APPLICATION_ROOT</tt> part only.
     */
    protected function relative_url()
    {
        return '';
    }

    /**
     *  @fn permalink($relative)
     *  @short Provides a unique permalink URL for the receiver object.
     *  @details Subclassers that wish to provide custom permalinks for objects should not override this method.
     *  Override the <tt>relative_url</tt> method instead.
     *  @param relative <tt>TRUE</tt> if the permalink should not contain the protocol and domain part of the URL, <tt>FALSE</tt> if you
     *  want them.
     */
    public function permalink($relative = true)
    {
        $relative_url = $this->relative_url();
        return $relative
            ? sprintf('%s%s', APPLICATION_ROOT, $relative_url)
            : sprintf(
                '%s://%s%s%s',
                isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http',
                $_SERVER['HTTP_HOST'],
                APPLICATION_ROOT,
                $relative_url
            );
    }

    /**
     * @fn as($superclass)
     * @short Poses as one of the superclasses
     * @details Call this method to pose an instance of this class as a superclass.
     * The method throws if <tt>$superclass</tt> is not a valid superclass.
     * @param $superclass The superclass
     */
    public function as($superclass)
    {
        if (is_subclass_of(get_class($this), $superclass)) {
            return new $superclass($this->values);
        }
        trigger_error(
            sprintf(
                '[%s::%s] Attempted to cast an instance of %s to %s but it is not a valid superclass.',
                get_class($this),
                __FUNCTION__,
                get_class($this),
                $superclass
            ),
            E_USER_ERROR
        );
    }

    /*
        function __call($method, $args)
        {
            echo "Unknown call of $method with arguments " . var_export($args, true);
        }
        */

    /**
     *  @fn __set($key, $value)
     *  @short Magic method to set the value of a property.
     *  @param key The key of the property.
     *  @param value The value of the property.
     */
    public function __set($key, $value)
    {
        if ($this->has_column($key)) {
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
        if (in_array($key, self::WRITEONLY_COLUMNS)) {
            return '***';
        }
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
     *  @fn __unset($key)
     *  @short Magic method to unset a property.
     *  @param key The key to unset.
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

    /**
     *  @fn __debugInfo()
     *  @short Magic method to print debug information about an instance.
     *  @return an array of key/value pairs representing the instance's properties.
     */
    public function __debugInfo()
    {
        $debug_info = [];
        foreach ($this->get_column_names() as $column) {
            if (in_array($column, self::WRITEONLY_COLUMNS)) {
                $debug_info[$column] = '***';
            } elseif (isset($this->values[$column])) {
                $debug_info[$column] = is_null($this->values[$column]) ? 'NULL' : $this->values[$column];
            }
        }
        return $debug_info;
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
     *  @param cols The columns of the model object.
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
     *  @fn _add_to_pool($classname, $id, $obj)
     *  @short Adds an object to the object pool.
     *  @param classname Name of the class for the desired object.
     *  @param id Primary key value for the desired object.
     *  @param obj The object to add to the pool.
     */
    private static function _add_to_pool($classname, $id, $obj)
    {
        if (!Config::get('OBJECT_POOL_ENABLED')) {
            return;
        }
        if (!isset(self::$object_pool[$classname])) {
            self::$object_pool[$classname] = [];
        }
        self::$object_pool[$classname][$id] = $obj;
    }

    /**
     *  @fn _get_from_pool($classname, $id)
     *  @short Retrieves an object from the object pool.
     *  @param classname Name of the class for the desired object.
     *  @param id Primary key value for the desired object.
     */
    private static function _get_from_pool($classname, $id)
    {
        if (!Config::get('OBJECT_POOL_ENABLED')) {
            return;
        }
        if (!isset(self::$object_pool[$classname]) || !isset(self::$object_pool[$classname][$id])) {
            return null;
        }
        return self::$object_pool[$classname][$id];
    }

    /**
     *  @fn _delete_from_pool($classname, $id)
     *  @short Deletes an object from the object pool.
     *  @param classname Name of the class for the desired object.
     *  @param id Primary key value for the desired object.
     */
    private static function _delete_from_pool($classname, $id)
    {
        unset(self::$object_pool[$classname][$id]);
    }

    /**
     *  @fn _purge_pool($classname)
     *  @short Deletes the object pool for a classname.
     *  @param classname Name of the class to purge.
     */
    public static function _purge_pool($classname)
    {
        unset(self::$object_pool[$classname]);
    }

    public static function get_pool_stats($classname)
    {
        $count = isset(self::$object_pool[$classname]) ? count(self::$object_pool[$classname]) : 0;
        return ['count' => $count];
    }
}
