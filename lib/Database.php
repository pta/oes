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

	function ensureStudent ($student_id, $firstname, $lastname, $dob, $class)
	{
		if ($student_id != null)
			$id = $this->getValue ("select ID from Student where Student_ID = '$student_id'");

		if ($id == null)
		{
			$id = $this->insertStudent ($student_id, $firstname, $lastname, $dob, $class);
		}
		else if ($firstname || $lastname || $dob || $class)
		{
			$id = $this->getStudentID ($student_id, $firstname, $lastname, $dob, $class);

			if ($id == null)
				throw new Exception ("Incorrect Student data: ($student_id, $firstname, $lastname, $dob, $class)");
		}

		return $id;
	}

	function insertStudent ($student_id, $firstname, $lastname, $dob, $class)
	{
		$student_id = str_value ($student_id);
		$firstname = str_value ($firstname);
		$lastname = str_value ($lastname);
		$dob = str_value ($dob);
		$class = num_value ($class);

		$this->query ("insert into Student values (null, $student_id, $firstname, $lastname, $dob, $class)");
		return $this->getLastInsertID();
	}

	function getStudentID ($student_id, $firstname, $lastname, $dob, $class)
	{
		$student_id = str_value ($student_id);
		$firstname = str_value ($firstname);
		$lastname = str_value ($lastname);
		$dob = str_value ($dob);
		$class = num_value ($class);

		return $this->getValue ("select ID from Student where (Student_ID = $student_id) and (FirstName = $firstname) and LastName = $lastname and DoB = $dob and Class = $class");
	}

	function insertClass ($class)
	{
		$k = num_value ($class[4]);
		$class = str_value ($class);

		$this->query ("insert into Class values (null, $class, $k)");
		return $this->getLastInsertID();
	}

	function insertSubject ($subject)
	{
		$subject = str_value ($subject);
		$this->query ("insert into Subject values (null, $subject)");
		return $this->getLastInsertID();
	}

	function insertTeacher ($teacher)
	{
		$lastname = substr (strrchr($teacher, 32), 1);
		$firstname = substr ($teacher, 0, strlen($teacher) - strlen($lastname));

		$lastname = str_value ($lastname);
		$firstname = str_value ($firstname);

		$this->query ("insert into Teacher values (null, $firstname, $lastname)");
		return $this->getLastInsertID();
	}

	function insertExam ($name, $class, $subject, $time, $teacher, $duration, $sched_time, $noq, $max_noc)
	{
		$name = str_value ($name);
		$class = num_value ($class);
		$subject = num_value ($subject);
		$teacher = num_value ($teacher);
		$noq = num_value ($noq);
		$max_noc = num_value ($max_noc);

		//$this->query ("insert into Exam values (null, $name, $class, $subject, $time, $teacher, '$duration', '$sched_time', null, $noq, $max_noc)");
		$this->query ("insert into Exam values (null, $name, $class, $subject, $time, $teacher, '$duration', '$sched_time', '$sched_time', $noq, $max_noc)");
		return $this->getLastInsertID();
	}

	function insertQuestion ($text, $subject)
	{
		$text = str_value ($text);
		$subject = num_value ($subject);

		$this->query ("insert into Question values (null, $text, $subject)");
		return $this->getLastInsertID();
	}

	function insertChoice ($question, $text, $correct)
	{
		$text = str_value ($text);

		$this->query ("insert into Choice values (null, $question, $text, $correct)");
		return $this->getLastInsertID();
	}

	function getRunningExams ($student)
	{
		$class = $this->getValue ("select Class from Student where ID=$student");
		$class = num_value ($class);

		$result = $this->query ("select * from Exam where Class=$class and Start_Time is not NULL");
		$exams = fetch_assoc ($result);
		mysql_free_result ($result);

		return $exams;
	}

	function openTest ($student, $exam)
	{
		return $this->getValue ("select ID from Test"
				. " where Student = $student and Exam = $exam");
	}

	function createTest ($student, $exam, $subject, $noq, $max_noc)
	{
		$subject = str_value ($subject);

		$test = $this->insertTest ($student, $exam);

		$result = $this->query ("select ID from Question"
				. " where Subject = $subject order by rand() limit $noq");
		$questions = fetch_column ($result);
		mysql_free_result ($result);

		foreach ($questions as $ord => $question)
		{
			$n = $max_noc - 1;

			$result = $this->query (
					"(select ID from Choice"
							. " where Question = $question and Correct = 1"
							. " order by rand() limit 1)"
					. "union"
					. "(select ID from Choice"
							. " where Question = $question and Correct = 0"
							. " order by rand() limit $n)"
					. "order by rand()");
			$choices = fetch_column ($result);
			mysql_free_result ($result);

			foreach ($choices as $choice)
			{
				$this->insertTestChoice ($ord + 1, $test, $choice);
			}
		}

		return $test;
	}

	function insertTestChoice ($ord, $test, $choice)
	{
		$this->query ("insert into Test_Choice values ($ord, $test, $choice)");
	}

	function insertTestAnswer ($test, $choice)
	{
		$this->query ("insert into Test_Answer values ($test, $choice)");
	}

	function insertTest ($student, $exam)
	{
		$this->query ("insert into Test values (null, $student, $exam, null)");
		return $this->getLastInsertID();
	}
}

?>