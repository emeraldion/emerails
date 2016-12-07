<?php
	/**
	 *	Project EmeRails - Codename Ocarina
	 *
	 *	Copyright (c) 2008, 2015 Claudio Procida
	 *	http://www.emeraldion.it
	 *
	 */

	require_once(dirname(__FILE__) . "/../config/db.conf.php");
	
	/**
	 *	@class Db
	 *	@short Implements an abstraction of a Database connection manager.
	 */
	class Db
	{
		/**
		 *	@short List of registered adapters
		 */
		private static $adapters = array();

		/**
		 *	@short Connections pool
		 */
		private static $pool = array();

		/**
		 *	@fn get_adapter($name = DB_ADAPTER)
		 *	@short Returns a database adapter registered under name
		 *	@param name A name associated to the adapter.
		 */
		public static function get_adapter($name = DB_ADAPTER)
		{
			return isset(self::$adapters[$name]) ? self::$adapters[$name] : NULL;
		}

		/**
		 *	@fn register_adapter($adapter, $name)
		 *	@short Registers a database adapter under name
		 *	@param adapter An instance of a class implementing the DbAdapter interface.
		 *	@param name A name associated to the adapter.
		 */
		public static function register_adapter($adapter, $name)
		{
			self::$adapters[$name] = $adapter;
		}

		/**
		 *	@fn get_default_adapter()
		 *	@short Returns the default database adapter
		 */
		public static function get_default_adapter()
		{
			return self::get_adapter(DB_ADAPTER);
		}

		/**
		 *	@fn get_connection($name = DB_ADAPTER)
		 *	@short Returns a connection for the requested adapter
		 *	@details If a free connection is found in the pool it is returned to the caller,
		 *	otherwise a new connection is created and added to the pool.
		 *	@param name The name of the adapter
		 */
		public static function get_connection($name = DB_ADAPTER)
		{
			if (!isset(self::$pool[$name]))
			{
				self::$pool[$name] = array();
			}
			if (count(self::$pool[$name]) > 0)
			{
				foreach (self::$pool[$name] as &$item)
				{
					if ($item["in_use"] == 0)
					{
						$item["in_use"]++;
						return $item["connection"];
					}
				}
			}
			// Create a new connection
			$adapter_class = get_class(self::get_adapter());
			$conn = eval("return new {$adapter_class}();");
			$item = array("in_use" => 0,
				"connection" => $conn);
			// Add it to the pool
			self::$pool[$name][] = $item;
			// Return it
			return $conn;
		}

		/**
		 *	@fn close_connection($conn, $name = DB_ADAPTER)
		 *	@short Closes a connection for the requested adapter
		 *	@details The connection is actually never closed, it is just marked as free
		 *	and kept in the pool for later reuse.
		 *	@param conn The connection that should be closed
		 *	@param name The name of the adapter
		 */
		public static function close_connection($conn, $name = DB_ADAPTER)
		{
			// self::show_pool();
			if (count(self::$pool[$name]) > 0)
			{
				foreach (self::$pool[$name] as &$item)
				{
					if ($item["connection"] == $conn)
					{
						$item["in_use"]--;
					}
				}
			}
		}

		// TODO: remove
		public static function show_pool()
		{
			print_r(self::$pool);
		}
	}

	// TODO: remove
	if (isset($_REQUEST["show_connections_pool"]))
	{
		Db::show_pool();
	}
?>
