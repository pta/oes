<?php
include_once "../config.php";
include_once "../lib/DBConnection.php";

class Database extends DBConnection
{
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
		$k = num_value ($class[4]);
		$class = str_value ($class);

		$this->query ("insert into Class values (null, $class, $k);");
		return $this->getValue ('ID', 'Class', "Name=$class");
	}

	function insertSubject ($subject)
	{
		$subject = str_value ($subject);
		$this->query ("insert into Subject values (null, $subject);");
		return $this->getValue ('ID', 'Subject', "Name=$subject");
	}

	function insertTeacher ($teacher)
	{
		$lastname = substr (strrchr($teacher, 32), 1);
		$firstname = substr ($teacher, 0, strlen($teacher) - strlen($lastname));

		$lastname = str_value ($lastname);
		$firstname = str_value ($firstname);

		$this->query ("insert into Teacher values (null, $firstname, $lastname);");

		return $this->getValue ('ID', 'Teacher', "FirstName=$firstname AND LastName=$lastname");
	}

	function insertExam ($name, $class, $subject, $time, $teacher, $duration, $sched_time)
	{
		$name = str_value ($name);
		$class = num_value ($class);
		$subject = num_value ($subject);
		$teacher = num_value ($teacher);

		$this->query ("insert into Exam values (null, $name, $class, $subject, $time, $teacher, '$duration', '$sched_time', null);");
	}
}

?>