<?php

class DBConnection
{
	private $connection;

	function __construct ($server, $username, $password)
	{
		$this->connection = mysql_pconnect ($server, $username, $password);

		if (!$this->connection)
			throw new Exception ('Could not connect: ' . mysql_error());
	}

	/**
	 * compatibility
	 */
	function DBConnection ($server, $username, $password)
	{
		if (version_compare (PHP_VERSION,"5.0.0","<"))
		{
			$this->__construct ($server, $username, $password);
			register_shutdown_function (array ($this, "__destruct"));
		}
	}

	function __destruct()
	{
		mysql_close ($this->connection);
	}

	function selectDatabase ($database)
	{
		$result = mysql_select_db ($database, $this->connection);

		if (!$result)
			throw new Exception ("Could not use $database: " . mysql_error());
	}

	function query ($query)
	{
		$result = mysql_query ($query);

		if (!$result)
		{
			$message  = 'Invalid query: ' . mysql_error() . "\n";
			$message .= 'Whole query: ' . $query;
			throw new Exception ($message);
		}

		return $result;
	}

	function begin()
	{$this->query ('begin;');}

	function rollback()
	{$this->query ('rollback;');}

	function commit()
	{$this->query ('commit;');}
}
?>