<?php
/**
 * @format
 */

require_once __DIR__ . '/db_adapter.php';

use Emeraldion\EmeRails\Config;
use Emeraldion\EmeRails\Db;

define('DB_HOST', Config::get('DB_HOST'));
define('DB_USER', Config::get('DB_USER'));
define('DB_PASS', Config::get('DB_PASS'));
define('DB_NAME', Config::get('DB_NAME'));
define('DB_CHARSET', Config::get('DB_CHARSET'));
define('DB_DEBUG', Config::get('DB_DEBUG'));

/**
 * @format
 *	@class MysqliAdapter
 *	@short MySQLi Database adapter.
 */
class MysqliAdapter implements DbAdapter
{
    /**
     *	@attr NAME
     *	@short Name of this adapter
     */
    const NAME = 'mysqli';

    /**
     *	@attr queries_count
     *	@short Counter for queries executed.
     */
    static $queries_count = 0;

    /**
     *	@attr link
     *	@short Connection link to the database.
     */
    public $link;

    /**
     *	@attr query
     *	@short Query for the database.
     */
    public $query;

    /**
     *	@attr result
     *	@short The result of the last query.
     */
    public $result;

    /**
     *	@fn connect
     *	@short Connects to the database.
     */
    public function connect()
    {
        if (!is_object($this->link)) {
            $this->link = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($this->link->connect_errno) {
                die('Cannot connect: ' . $this->link->connect_error);
            }
            $this->link->set_charset(DB_CHARSET);
        }
    }

    /**
     *	@fn select_db($database_name)
     *	@short Selects the desired database.
     *	@param database_name The name of the database.
     */
    public function select_db($database_name)
    {
        $this->connect();
        return $this->link->select_db($database_name);
    }

    /**
     *	@fn close
     *	@short Closes the connection to the database.
     */
    public function close()
    {
        $this->link->close();
    }

    /**
     *	@fn prepare($query)
     *	@short Prepares a query for execution
     *	@param query The query to execute.
     */
    public function prepare($query)
    {
        $this->connect();

        $args = func_get_args();
        $args_len = func_num_args();
        if ($args_len > 1) {
            for ($i = 1; $i < $args_len; $i++) {
                $query = str_replace('{' . $i . '}', $this->link->real_escape_string($args[$i]), $query);
            }
        }
        $this->query = $query;

        if (DB_DEBUG) {
            $this->print_query();
        }
    }

    /**
     *	@fn exec
     *	@short Executes a query.
     */
    public function exec()
    {
        $this->connect();
        ($this->result = $this->link->query($this->query)) or
            die(DB_DEBUG ? "Error ({$this->query}): {$this->link->error}" : 'DB unavailable');
        $this->insert_id = $this->link->insert_id;

        self::$queries_count++;

        return $this->result;
    }

    /**
     *	@fn insert_id
     *	@short Returns the id generated by the last INSERT query.
     */
    public function insert_id()
    {
        return $this->insert_id;
    }

    /**
     *	@fn escape($value)
     *	@short Escapes the given value to avoid SQL injections.
     *	@param value The value to escape.
     */
    public function escape($value)
    {
        $this->connect();
        return $this->link->real_escape_string($value);
    }

    /**
     *	@fn result($pos, $colname)
     *	@short Returns a single result of the last SELECT query.
     *	@param row The row of the resultset.
     *	@param colname The name (or the alias, if applicable) of the desired row.
     */
    public function result($pos = 0, $colname = null)
    {
        // Adapted from http://stackoverflow.com/questions/2089590/mysqli-equivalent-of-mysql-result
        $numrows = $this->result->num_rows;
        if ($numrows && $pos <= $numrows - 1 && $pos >= 0) {
            if (!$this->result->data_seek($pos)) {
                return false;
            }
            $resrow =
                is_numeric($colname) || is_null($colname) ? $this->result->fetch_row() : $this->result->fetch_assoc();
            if (!is_null($colname) && isset($resrow[$colname])) {
                return $resrow[$colname];
            } else {
                return $resrow[0];
            }
        }
        return false;
    }

    /**
     *	@fn num_rows
     *	@short Returns the number of rows returned by a previous SELECT query.
     */
    public function num_rows()
    {
        return $this->result->num_rows;
    }

    /**
     *	@fn affected_rows
     *	@short Returns the number of rows affected by a previous INSERT, UPDATE or DELETE query.
     */
    public function affected_rows()
    {
        return $this->link->affected_rows;
    }

    /**
     *	@fn fetch_assoc
     *	@short Returns the current row of the resultset as an associative array.
     */
    public function fetch_assoc()
    {
        return $this->result->fetch_assoc();
    }

    /**
     *	@fn fetch_array
     *	@short Returns the current row of the resultset as an array.
     */
    public function fetch_array()
    {
        return $this->result->fetch_array();
    }

    /**
     *	@fn free_result
     *	@short Releases the result of the last query.
     */
    public function free_result()
    {
        $this->result->free();
    }

    /**
     *	@fn print_query
     *	@short Prints the last query for debug.
     */
    public function print_query()
    {
        echo <<<EOT
			<pre class="db-debug">{$this->query}</pre>
EOT;
    }
}

Db::register_adapter(new MysqliAdapter(), MysqliAdapter::NAME);
?>
