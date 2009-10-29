<?php
include_once "../config.php";
include_once "../lib/DBConnection.php";

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

class Database extends DBConnection
{
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

	function getClassList()
	{
		return $this->getColumns ('Name, ID', 'Class');
	}

	function getSubjectList()
	{
		return $this->getColumns ('Name, ID', 'Subject');
	}

	function getTeacherList()
	{
		return $this->getColumns ('FirstName, LastName, ID', 'Teacher');
	}

	function insertClass ($class)
	{
		$this->query ("insert into Class values (null, '$class', $class[4]);");
		return $this->getValue ('ID', 'Class', "Name='$class'");
	}

	function insertSubject ($subject)
	{
		$this->query ("insert into Subject values (null, '$subject');");
		return $this->getValue ('ID', 'Subject', "Name='$subject'");
	}

	function insertTeacher ($teacher)
	{
		$lastname = substr (strrchr($teacher, 32), 1);
		$firstname = substr ($teacher, 0, strlen($teacher) - strlen($lastname));
		$this->query ("insert into Teacher values (null, '$firstname', '$lastname');");
		return $this->getValue ('ID', 'Teacher', "FirstName='$firstname' AND LastName='$lastname'");
	}

	function insertExam ($name, $class, $subject, $time, $teacher, $duration, $sched_time)
	{
		if ($name == null)
			$name = 'null';
		else
			$name = "'$name'";

		$this->query ("insert into Exam values (null, $name, $class, $subject, $time, '$teacher', '$duration', '$sched_time', null);");
	}
}

?>