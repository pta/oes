<?php

function fetch_column ($result)
{
	$arr = array();
	while ($row = mysql_fetch_array ($result))
		$arr[] = $row[0];
	return $arr;
}

function fetch_columns ($result)
{
	$arr = array();
	while ($row = mysql_fetch_array ($result))
		$arr[] = $row;
	return $arr;
}

function fetch_assoc ($result)
{
	$arr = array();
	while ($row = mysql_fetch_assoc ($result))
		$arr[] = $row;
	return $arr;
}

/**
 * wrap string value
 */
function str_value ($val)
{
	if ($val == null)
		return 'null';
	else
		return "'$val'";
}

/**
 * wrap number value
 */
function num_value ($val)
{
	if (is_numeric ($val))
		return $val;
	else
		return 'null';
}

class DBConnection
{
	private $connection;
	private $server, $username, $password;
	private $database;

	function __construct ($server, $username, $password)
	{
		$this->connection = mysql_pconnect ($server, $username, $password);

		if (!$this->connection)
			throw new Exception ('Could not connect: ' . mysql_error());

		$this->server = $server;
		$this->username = $username;
		$this->password = $password;
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

		$this->database = $database;
	}

	function import ($source)
	{
		$result = system ("mysql --show-warnings\
						--host='$this->server'\
						--user='$this->username'\
						--password='$this->password'\
						--database='$this->database'\
						--execute='source $source'",
				$return_code);

		if ($return_code != 0)
			throw new Exception ("Could not import $source: " . $result);

		return $result;
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
	{$this->query ('begin');}

	function rollback()
	{$this->query ('rollback');}

	function commit()
	{$this->query ('commit');}

	function getColumn ($column, $table)
	{
		$result = $this->query ("select `$column` from `$table` order by `$column`");
		$ret = fetch_column ($result);
		mysql_free_result ($result);
		return $ret;
	}

	function getColumns ($columns, $table)
	{
		$result = $this->query ("select $columns from `$table` order by $columns");
		$ret = fetch_columns ($result);
		mysql_free_result ($result);
		return $ret;
	}

	function getValue ($query)
	{
		$result = $this->query ($query);
		$row = mysql_fetch_array ($result);
		mysql_free_result ($result);
		return $row[0];
	}

	function getLastInsertID()
	{
		return $this->getValue ('select last_insert_id()');
	}
}
?>