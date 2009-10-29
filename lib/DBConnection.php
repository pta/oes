<?php
class DBConnection
{
	private $connection;

	function __construct ($server, $username, $password)
	{
		$this->connection = mysql_pconnect ($server, $username, $password)
				or die ('Could not connect: ' . mysql_error());
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
		mysql_select_db ($database, $this->connection)
				or die ("Could not use $database: " . mysql_error());
	}

	function query ($query)
	{
		$result = mysql_query ($query);

		if (!$result)
		{
			$message  = 'Invalid query: ' . mysql_error() . "\n";
			$message .= 'Whole query: ' . $query;
			die ($message);
		}

		return $result;
	}
}
?>