<?php
	
	/**
	 *	@interface DbAdapter
	 *	@short Methods that all database connection adapters must implement.
	 */
	interface DbAdapter
	{
		/**
		 *	@fn connect
		 *	@short Connects to the database.
		 */
		public function connect();
		
		/**
		 *	@fn select_db($database_name)
		 *	@short Selects the desired database.
		 *	@param database_name The name of the database.
		 */
		public function select_db($database_name);
		
		/**
		 *	@fn close
		 *	@short Closes the connection to the database.
		 */
		public function close();
		
		/**
		 *	@fn prepare($query)
		 *	@short Prepares a query for execution
		 *	@param query The query to execute.
		 */
		public function prepare($query);
		
		/**
		 *	@fn exec
		 *	@short Executes a query.
		 */
		public function exec();
		
		/**
		 *	@fn insert_id
		 *	@short Returns the id generated by the last INSERT query.
		 */
		public function insert_id();
		
		/**
		 *	@fn escape($value)
		 *	@short Escapes the given value to avoid SQL injections.
		 *	@param value The value to escape.
		 */
		public function escape($value);
		
		/**
		 *	@fn result($pos, $colname)
		 *	@short Returns a single result of the last SELECT query.
		 *	@param row The row of the resultset.
		 *	@param colname The name (or the alias, if applicable) of the desired row.
		 */
		public function result($pos = 0, $colname = NULL);
		
		/**
		 *	@fn num_rows
		 *	@short Returns the number of rows returned by a previous SELECT query.
		 */
		public function num_rows();
		
		/**
		 *	@fn affected_rows
		 *	@short Returns the number of rows affected by a previous INSERT, UPDATE or DELETE query.
		 */
		public function affected_rows();
		
		/**
		 *	@fn fetch_assoc
		 *	@short Returns the current row of the resultset as an associative array.
		 */
		public function fetch_assoc();
		
		/**
		 *	@fn free_result
		 *	@short Releases the result of the last query.
		 */
		public function free_result();
		
		/**
		 *	@fn print_query
		 *	@short Prints the last query for debug.
		 */
		public function print_query();
	}
?>