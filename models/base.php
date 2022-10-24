<?php
/**
 *	Project EmeRails - Codename Ocarina
 *
 *	Copyright (c) 2008, 2017 Claudio Procida
 *	http://www.emeraldion.it
 *
 * @format
 */

require_once dirname(__FILE__) . '/../include/common.inc.php';
require_once dirname(__FILE__) . '/../include/db.inc.php';

/**
 *	@class ActiveRecord
 *	@short The abstract base class for DB-backed model objects.
 *	@details Every object of a subclass of ActiveRecord are mapped 1:1 to records of a table in
 *	a relational Database. Naming conventions assume that if the ActiveRecord subclass is called
 *	<tt>MyProduct</tt>, database records are stored in a table called <tt>my_products</tt>.
 *	Conversely, if an object of another class is in a relation with your object, it is assumed that a
 *	foreign key called <tt>my_product_id</tt> exists in the other table. Of course this is overridable
 *	by setting explicitly <tt>$table_name</tt> and <tt>$foreign_key</tt> to a value of your choice.
 */
abstract class ActiveRecord
{
    /**
     *	@attr columns
     *	@short Array of columns for the model object.
     */
    static $columns = array();

    /**
     *	@attr class_initialized
     *	@short Array containing initialization information for subclasses.
     */
    static $class_initialized = array();

    /**
     *	@attr belongs_to_classes
     *	@short Array containing information on parent tables for subclasses.
     */
    static $belongs_to_classes = array();

    /**
     *	@attr has_many_classes
     *	@short Array containing information on child tables for subclasses.
     */
    static $has_many_classes = array();

    /**
     *	@attr object_pool
     *	@short Pool of objects already fetched from database.
     */
    static $object_pool = array();

    /**
     *	@attr table_name
     *	@short Name of the table bound to this model class.
     */
    protected $table_name;

    /**
     *	@attr primary_key
     *	@short Name of the primary key column for the bound table.
     *	@details Set this attribute only when the primary key of the bound table is not the canonical <tt>id</tt>.
     */
    protected $primary_key = 'id';

    /**
     *	@attr foreign_key_name
     *	@short Used to create the name of foreign key column in tables that are in a relationship with the bound table.
     *	@details Set this attribute only when the foreign key that references objects of this class
     *	is not the canonical name (e.g. 'product' for class Product).
     */
    protected $foreign_key_name;

    /**
     *	@attr values
     *	@short Array of values for the columns of model object.
     */
    private $values;

    /**
     *	@fn __construct($_values)
     *	@short Constructs and initializes an ActiveRecord object.
     *	@details Due to the lack of a static class initialization method,
     *	the default constructor is in charge of gathering information about
     *	the bound table columns the first time an object is created. Subsequent
     *	creations will use the values stored in static class variables.
     *	Subclassers don't need to override the constructor. They can in turn
     *	override the <tt>init</tt> method in order to perform custom initialization.
     *	@param values Column values to initialize the object.
     */
    function __construct($_values = null)
    {
        $conn = Db::get_connection();

        $classname = get_class($this);
        $initialized = self::_is_initialized($classname);
        if (!$initialized) {
            $conn->prepare('DESCRIBE `{1}`', $this->get_table_name());
            $conn->exec();
            $columns = array();
            while ($row = $conn->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            self::_set_columns($classname, $columns);
            self::_set_initialized($classname, true);
        }
        $columns = self::_get_columns($classname);
        if (!empty($_values)) {
            $this->values = array();
            foreach ($_values as $key => $val) {
                $keyexists = in_array($key, $columns);
                if ($keyexists) {
                    $this->values[$key] = get_magic_quotes_gpc()
                        ? stripslashes($val)
                        : $val;
                }
            }
        }
        $this->init($_values);

        Db::close_connection($conn);
    }

    /**
     *	@fn init($values)
     *	@short Performs specialized initialization tasks.
     *	@details Subclassers will use this method to perform custom initialization.
     *	@note The default implementation simply does nothing.
     *	@param values An array of column-value pairs to initialize the receiver.
     */
    protected function init($values)
    {
    }

    /**
     *	@fn get_table_name
     *	@short Returns the name of the table bound to this class.
     *	@details This method returns the name of the table which contains
     *	data for objects of this class. If the ActiveRecord subclass is called <tt>MyRecord</tt>,
     *	the table name will be <tt>my_records</tt>. Of course you can override this behavior by
     *	setting explicitly the value of <tt>$table_name</tt> in the declaration of your class.
     */
    private function get_table_name()
    {
        if (!$this->table_name) {
            $classname = get_class($this);
            $this->table_name = class_name_to_table_name($classname);
        }
        return $this->table_name;
    }

    /**
     *	@fn get_foreign_key_name
     *	@short Returns the name of the foreign key for this class.
     *	@details This method returns the name of the column to lookup when considering relations
     *	with objects of this class. If the ActiveRecord subclass is called <tt>MyRecord</tt>,
     *	the foreign key name will be <tt>my_record_id</tt>. Of course you can override this behavior by
     *	setting explicitly the value of <tt>$foreign_key_name</tt> in the declaration of your class.
     */
    private function get_foreign_key_name()
    {
        if (empty($this->foreign_key_name)) {
            $classname = get_class($this);
            $this->foreign_key_name = class_name_to_foreign_key($classname);
        }
        return $this->foreign_key_name;
    }

    /**
     *	@fn get_primary_key
     *	@short Returns the name of the primary key for this class.
     *	@details This method returns the name of the primary key in the table bound to this class.
     *	By default, ActiveRecord considers as primary key a column named <tt>id</tt>. Of course you can override
     *	this behavior by setting explicitly the value of <tt>$primary_key</tt> in the declaration of your class.
     */
    private function get_primary_key()
    {
        if (!$this->primary_key) {
            $this->primary_key = 'id';
        }
        return $this->primary_key;
    }

    /**
     *	@fn has_column($key)
     *	@short Verifies the existence of a column named <tt>key</tt> in the bound table.
     *	@param key The name of the column to check.
     */
    private function has_column($key)
    {
        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        return in_array($key, $columns);
    }

    /**
     *	@fn belongs_to($table_name)
     *	@short Loads the parent of the receiver in a one-to-many relationship.
     *	@param table_name The name of the parent table.
     */
    public function belongs_to($table_name)
    {
        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        $ownerclass = table_name_to_class_name($table_name);
        $owner = new $ownerclass();
        if (in_array(table_name_to_foreign_key($table_name), $columns)) {
            $owner->find_by_id(
                $this->values[table_name_to_foreign_key($table_name)]
            );
        } elseif (in_array($owner->foreign_key_name, $columns)) {
            $owner->find_by_id($this->values[$owner->foreign_key_name]);
        }
        $this->values[singularize($table_name)] = $owner;
        $owner->values[singularize($this->table_name)] = $this;
    }

    /**
     *	@fn has_many($table_name, $params)
     *	@short Loads the children of the receiver in a one-to-many relationship.
     *	@param table_name The name of the child table.
     *	@param params An array of conditions. For the semantics, see find_all
     *	@return true if the relationship is fulfilled, false otherwise
     *	@see find_all
     */
    public function has_many($table_name, $params = array())
    {
        $childclass = table_name_to_class_name($table_name);
        $obj = new $childclass();
        $fkey = $this->get_foreign_key_name();
        if (isset($params['where_clause'])) {
            $params[
                'where_clause'
            ] = "({$params['where_clause']}) AND `{$fkey}` = '{$this->values[$this->primary_key]}' ";
        } else {
            $params[
                'where_clause'
            ] = "`{$fkey}` = '{$this->values[$this->primary_key]}' ";
        }
        $children = $obj->find_all($params);
        if (is_array($children) && count($children) > 0) {
            foreach ($children as $child) {
                $child->values[singularize($this->table_name)] = $this;
            }
            $this->values[$table_name] = $children;

            return true;
        }
        return false;
    }

    /**
     *	@fn has_and_belongs_to_many($table_name, $params)
     *	@short Loads the object network the receiver belongs to in a many-to-many relationship.
     *	@param table_name The name of the peer table.
     *	@param params An array of conditions. For the semantics, see find_all
     *	@see find_all
     */
    public function has_and_belongs_to_many($table_name, $params = array())
    {
        $conn = Db::get_connection();

        $peerclass = table_name_to_class_name($table_name);
        $peer = new $peerclass();
        $fkey = $this->get_foreign_key_name();
        $peer_fkey = $peer->get_foreign_key_name();

        // By convention, relation table name is the union of
        // the two member tables' names joined by an underscore
        // in alphabetical order
        $table_names = array($table_name, $this->table_name);
        sort($table_names);
        $relation_table = implode('_', $table_names);

        $conn->prepare(
            "SELECT * FROM `{1}` WHERE `{2}` = '{3}'",
            $relation_table,
            $fkey,
            $this->values[$this->primary_key]
        );
        $conn->exec();
        //print_r($conn->query);
        if ($conn->num_rows() > 0) {
            $this->values[$table_name] = array();
            while ($row = $conn->fetch_assoc()) {
                $peer = new $peerclass();
                $peer->find_by_id($row[$peer_fkey]);
                $this->values[$table_name][] = $peer;
                $peer->values[$this->table_name] = array($this);
                // print_r($peer);

                /* hack: store relationship data in the peer */
                unset($row['id']);
                unset($row[$fkey]);
                unset($row[$peer_fkey]);
                foreach ($row as $key => $value) {
                    $peer->values[$key] = $value;
                }
                /* /hack */
            }
        }
        $conn->free_result();

        Db::close_connection($conn);
    }

    /**
     *	@fn has_one($table_name)
     *	@short Loads the child the receiver in a one-to-one relationship.
     *	@param table_name The name of the child table.
     *	@param params An array of conditions. For the semantics, see find_all
     *	@return true if the relationship is fulfilled, false otherwise
     *	@see find_all
     */
    public function has_one($table_name)
    {
        $childclass = table_name_to_class_name($table_name);
        $obj = new $childclass();
        $fkey = $this->get_foreign_key_name();
        $children = $obj->find_all(array(
            'where_clause' => "`{$fkey}` = '{$this->values[$this->primary_key]}'",
            'limit' => 1
        ));
        if (is_array($children) && count($children) > 0) {
            $child = $children[0];
            $child->values[singularize($this->table_name)] = $this;
            $this->values[singularize($table_name)] = $child;

            return true;
        }
        return false;
    }

    /**
     *	Finder methods
     */

    /**
     *	@fn find_by_query($query)
     *	@short Returns an array of model objects by executing a custom SELECT query.
     *	@details This is a powerful instance method to retrieve objects from the database with a custom query.
     *	You can, among other things, do LEFT JOIN queries here.
     *	@param query The SELECT query to fetch objects.
     */
    public function find_by_query($query)
    {
        $conn = Db::get_connection();

        $ret = null;

        $conn->prepare($query);
        $conn->exec();
        if ($conn->num_rows() > 0) {
            $classname = get_class($this);
            $results = array();
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
     *	@fn find_all($params)
     *	@short Returns an array of model objects that satisfy the requirements expressed in the <tt>params</tt> argument.
     *	@details This method lets you find all objects of this class that satisfy a custom set of requirements, which you
     *	can express by setting the following keys of the <tt>params</tt> argument:
     *	@li <tt>where_clause</tt> You can express a custom SQL WHERE expression here (e.g. `date` < '2008-05-01')
     *	@li <tt>order_by</tt> You can express a custom SQL ORDER BY expression here (e.g. `date` DESC)
     *	@li <tt>limit</tt> You can express a custom limit for the returned results.
     *	@li <tt>start</tt> You can express a custom start for the returned results.
     *	@param params An array of parameters for the underlying SQL query.
     */
    function find_all($params = array())
    {
        $conn = Db::get_connection();

        if (empty($params['where_clause'])) {
            $params['where_clause'] = '1';
        }
        if (empty($params['order_by'])) {
            $params['order_by'] = "`{$this->primary_key}` ASC";
        }
        if (empty($params['limit'])) {
            $params['limit'] = 999;
        }
        if (empty($params['start'])) {
            $params['start'] = 0;
        }

        $ret = null;

        $conn->prepare(
            "SELECT * FROM `{1}` WHERE (1 AND ({$params['where_clause']})) ORDER BY {$params['order_by']} LIMIT {$params['start']}, {$params['limit']}",
            $this->get_table_name()
        );
        $conn->exec();
        if ($conn->num_rows() > 0) {
            $classname = get_class($this);
            $results = array();
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
     *	@fn find($id, $classname)
     *	@short Returns an object whose primary key value is <tt>id</tt>.
     *	@details Due to limitations of PHP, a static method always apply to the
     *	superclass. We have to explicitly reference the name of the subclass in order to
     *	create the right object.
     *	@param id The value of the primary key.
     *	@param classname The name of the subclass to apply this static method to.
     */
    static function find($id, $classname = 'ActiveRecord')
    {
        $obj = new $classname();
        if ($obj->find_by_id($id)) {
            return $obj;
        }
        return null;
    }

    /**
     *	@fn find_by_id($id)
     *	@short Populates an object with the values of the DB row whose primary key value is <tt>id</tt>.
     *	@details This instance method populates the receiver object with the contents of the DB row whose
     *	primary key is <tt>id</tt>.
     *	@param id The primary key of the desired DB row.
     *	@return This method returns TRUE if such row exists, FALSE otherwise.
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
                $this->values[$column] = $values[$column];
            }
            self::_add_to_pool($classname, $id, $this);

            $ret = true;
        }

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *	@fn count_all($params)
     *	@short Returns the count of model objects that satisfy the requirements expressed in the <tt>params</tt> argument.
     *	@details This method lets you count all objects of this class that satisfy a custom set of requirements, which you
     *	can express by setting the following keys of the <tt>params</tt> argument:
     *	@li <tt>where_clause</tt> You can express a custom SQL WHERE expression here (e.g. `date` < '2008-05-01')
     *	@param params An array of parameters for the underlying SQL query.
     */
    public function count_all($params = array())
    {
        $conn = Db::get_connection();

        $ret = 0;

        if (empty($params['where_clause'])) {
            $params['where_clause'] = '1';
        }
        $conn->prepare(
            "SELECT COUNT(*) FROM `{1}` WHERE (1 AND ({$params['where_clause']}))",
            $this->get_table_name()
        );
        $result = $conn->exec();

        $ret = $conn->fetch_array()[0];

        $conn->free_result();

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *	@fn save
     *	@short Requests the receiver to save its data in the bound table.
     *	@details This method has two distinct effects. If called on an object fetched
     *	from the table, it performs an <tt>UPDATE</tt> SQL statement to update the
     *	table data to the new values. If called on an object created programmatically, it
     *	performs an <tt>INSERT</tt> SQL statement, and sets the object's primary key
     *	value to the value resulting by the insert.
     *	@return This method returns TRUE if the object has been saved successfully.
     */
    public function save()
    {
        $conn = Db::get_connection();

        $classname = get_class($this);
        $columns = self::_get_columns($classname);
        $ret = false;

        $nonempty = array();
        for ($i = 0; $i < count($columns); $i++) {
            if (isset($this->values[$columns[$i]])) {
                $nonempty[] = $columns[$i];
            }
        }

        if (
            !empty($this->values[$this->primary_key]) &&
            !isset($this->_force_create)
        ) {
            $query = 'UPDATE `{1}` SET ';
            for ($i = 0; $i < count($nonempty); $i++) {
                $query .= "`{$nonempty[$i]}` = '{$conn->escape(
                    $this->values[$nonempty[$i]]
                )}'";
                if ($i < count($nonempty) - 1) {
                    $query .= ', ';
                }
            }
            $query .= " WHERE `{$this->primary_key}` = '{2}' LIMIT 1";
            $conn->prepare(
                $query,
                $this->get_table_name(),
                $this->values[$this->primary_key]
            );
            $conn->exec();
            $ret = true;
        } else {
            $query =
                (isset($this->_ignore) ? 'INSERT IGNORE' : 'INSERT') .
                ' INTO `{1}` (';
            for ($i = 0; $i < count($nonempty); $i++) {
                $query .= "`{$nonempty[$i]}`";
                if ($i < count($nonempty) - 1) {
                    $query .= ', ';
                }
            }
            $query .= ') VALUES (';
            for ($i = 0; $i < count($nonempty); $i++) {
                $query .= "'{$conn->escape($this->values[$nonempty[$i]])}'";
                if ($i < count($nonempty) - 1) {
                    $query .= ', ';
                }
            }
            $query .= ')';
            $conn->prepare($query, $this->get_table_name());
            $conn->exec();
            $insert_id = $conn->insert_id();
            if ($insert_id !== 0) {
                $this->values[$this->primary_key] = $insert_id;
            }
            if ($conn->affected_rows() > 0) {
                $ret = true;
            }
        }

        Db::close_connection($conn);

        return $ret;
    }

    /**
     *	@fn delete($optimize)
     *	@short Deletes an object's database counterpart.
     *	@details This method performs a <tt>DELETE</tt> SQL statement on the
     *	table bound to the receiver's class, requesting the deletion of the object whose
     *	primary key is equal to the receiver's primary key value. If the object has been
     *	created programmatically and lacks a primary key value, this method has no effect.
     *	@param bool cleanup Set to <tt>FALSE</tt> if you do not want the table to be optimized after deletion.
     */
    public function delete($optimize = true)
    {
        $conn = Db::get_connection();

        if (!empty($this->values[$this->primary_key])) {
            $conn->prepare(
                "DELETE FROM `{1}` WHERE `{$this->primary_key}` = '{2}' LIMIT 1",
                $this->get_table_name(),
                $this->values[$this->primary_key]
            );
            $conn->exec();

            // Clean up
            if ($optimize) {
                $conn->prepare('OPTIMIZE TABLE `{1}`', $this->get_table_name());
                $conn->exec();
            }
        }

        Db::close_connection($conn);
    }

    /**
     *	@fn relative_url
     *	@short Provides a relative URL that will be used by the <tt>permalink</tt> public method.
     *	@details Subclassers that wish to provide custom permalinks for objects should override this method.
     *	You should return the URL portion after the <tt>APPLICATION_ROOT</tt> part only.
     */
    protected function relative_url()
    {
        return '';
    }

    /**
     *	@fn permalink($relative)
     *	@short Provides a unique permalink URL for the receiver object.
     *	@details Subclassers that wish to provide custom permalinks for objects should not override this method.
     *	Override the <tt>relative_url</tt> method instead.
     *	@param relative <tt>TRUE</tt> if the permalink should not contain the protocol and domain part of the URL, <tt>FALSE</tt> if you
     *	want them.
     */
    public function permalink($relative = true)
    {
        $relative_url = $this->relative_url();
        return $relative
            ? sprintf('%s%s', APPLICATION_ROOT, $relative_url)
            : sprintf(
                'http://%s%s%s',
                $_SERVER['HTTP_HOST'],
                APPLICATION_ROOT,
                $relative_url
            );
    }

    /*
		function __call($method, $args)
		{
			echo "Unknown call of $method with arguments " . var_export($args, true);
		}
		*/

    /**
     *	@fn __set($key, $value)
     *	@short Magic method to set the value of a property.
     *	@param key The key of the property.
     *	@param value The value of the property.
     */
    public function __set($key, $value)
    {
        if ($this->has_column($key)) {
            $this->values[$key] = $value;
        } else {
            $this->$key = $value;
        }
    }

    /**
     *	@fn __get($key)
     *	@short Magic method to get the value of a property.
     *	@param key The key of the desired property.
     */
    public function __get($key)
    {
        if ($this->values !== null && array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }
        if (isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }

    /**
     *	@fn __isset($key)
     *	@short Magic method to determine if a property exists.
     *	@param key The key to test.
     */
    public function __isset($key)
    {
        if (!(isset($this->values) && !empty($this->values))) {
            return false;
        }
        if (array_key_exists($key, $this->values)) {
            return true;
        } elseif (isset($this->$key)) {
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
        } elseif (isset($this->$key)) {
            unset($this->key);
        }
    }

    /**
     *	@fn _set_initialized($classname, $initialized)
     *	@short Marks the class <tt>classname</tt> as initialized.
     *	@details This method allows ActiveRecord to keep track of what subclasses have already been
     *	initialized by inspectioning the bound database table schema, whithout the need for a per-class
     *	initialization method.
     *	@param classname The name of the class that should be marked as initialized
     *	@param initialized <tt>TRUE</tt> if the class should be considered initialized, <tt>FALSE</tt> otherwise.
     */
    private static function _set_initialized($classname, $initialized)
    {
        self::$class_initialized[$classname] = $initialized;
    }

    /**
     *	@fn _is_initialized($classname)
     *	@short Tells whether the class <tt>classname</tt> has already been initialized.
     *	@param classname The name of the class that you want to inspect.
     *	@return <tt>TRUE</tt> if the class has been initialized, <tt>FALSE</tt> otherwise.
     */
    private static function _is_initialized($classname)
    {
        if (!isset(self::$class_initialized[$classname])) {
            return false;
        }
        return self::$class_initialized[$classname];
    }

    /**
     *	@fn _set_columns($classname, $cols)
     *	@short Stores the columns for the desired class.
     *	@param classname Name of the class for the desired object.
     *	@param cols The columns of the model object.
     */
    private static function _set_columns($classname, $cols)
    {
        self::$columns[$classname] = $cols;
    }

    /**
     *	@fn _get_columns($classname)
     *	@short Returns the columns for the desired class.
     *	@param classname Name of the class for the desired object.
     */
    private static function _get_columns($classname)
    {
        if (!isset(self::$class_initialized[$classname])) {
            return null;
        }
        return self::$columns[$classname];
    }

    /**
     *	@fn _add_to_pool($classname, $id, $obj)
     *	@short Adds an object to the object pool.
     *	@param classname Name of the class for the desired object.
     *	@param id Primary key value for the desired object.
     *	@param obj The object to add to the pool.
     */
    private static function _add_to_pool($classname, $id, $obj)
    {
        if (!isset(self::$object_pool[$classname])) {
            self::$object_pool[$classname] = array();
        }
        self::$object_pool[$classname][$id] = $obj;
    }

    /**
     *	@fn _get_from_pool($classname, $id)
     *	@short Retrieves an object from the object pool.
     *	@param classname Name of the class for the desired object.
     *	@param id Primary key value for the desired object.
     */
    private static function _get_from_pool($classname, $id)
    {
        if (
            !isset(self::$object_pool[$classname]) ||
            !isset(self::$object_pool[$classname][$id])
        ) {
            return null;
        }
        return self::$object_pool[$classname][$id];
    }
}

if (version_compare(PHP_VERSION, '5') < 0) {
    if (function_exists('overload')) {
        overload('ActiveRecord');
    }
}
?>
