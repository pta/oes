<?php

function fetch_column ($result)
{
	while ($row = mysql_fetch_array ($result))
		$arr[] = $row[0];
	return $arr;
}

function fetch_columns ($result)
{
	while ($row = mysql_fetch_array ($result))
		$arr[] = $row;
	return $arr;
}

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
			$message  = 'Invalid query: ' . mysql_error() . "\n<br>";
			$message .= "Whole query: <code>$query</code>";
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

	function getColumn ($column, $table)
	{
		$result = $this->query ("select `$column` from `$table` order by `$column`;");
		$ret = fetch_column ($result);
		mysql_free_result ($result);
		return $ret;
	}

	function getColumns ($columns, $table)
	{
		$result = $this->query ("select $columns from `$table` order by $columns;");
		$ret = fetch_columns ($result);
		mysql_free_result ($result);
		return $ret;
	}

	function getValue ($colname, $table, $criteria)
	{
		$query = "select `$colname` from `$table` where $criteria;";
		$result = $this->query ($query);
		$row = mysql_fetch_array ($result);
		return $row[0];
	}
}
?>