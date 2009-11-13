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

	function insertExam ($name, $class, $subject, $time, $teacher, $duration, $sched_time, $noq, $max_noc, $mul_choice)
	{
		$name = str_value ($name);
		$class = num_value ($class);
		$subject = num_value ($subject);
		$teacher = num_value ($teacher);
		$duration = num_value ($duration);
		$noq = num_value ($noq);
		$max_noc = num_value ($max_noc);
		$mul_choice = $mul_choice?'true':'false';

		$this->query ("insert into Exam values (null, $name, $class, $subject, $time, $teacher, $duration, '$sched_time', null, null, $noq, $max_noc, $mul_choice)");
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

	function openTest ($student, $exam)
	{
		return $this->getValue ("select ID from Test"
				. " where Student = $student and Exam = $exam");
	}

	function createTest ($student, $exam, $subject, $noq, $max_noc)
	{
		$subject = num_value ($subject);

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
				$this->insertTestChoice ($ord, $test, $choice);
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
		$this->query ("delete from Test_Answer where Test = $test and Answer in (select ID from Choice where Question = (select Question from Choice where ID = $choice))");
		$this->query ("insert into Test_Answer values ($test, $choice)");
	}

	function insertTest ($student, $exam)
	{
		$this->query ("insert into Test values (null, $student, $exam, null)");
		return $this->getLastInsertID();
	}

	function insertRandomExam()
	{
		$noq = 13;

		for ($i = 0; $i < $noq; ++$i)
		{
			$this->query ('insert ignore into Exam values (
					null,
					left(md5(rand()),6),
					0.5 + rand() * (select count(*) from Class),
					0.5 + rand() * (select count(*) from Subject),
					0.5 + rand() * 4,
					0.5 + rand() * (select count(*) from Teacher),
					' . (mt_rand (5, 9) * 5) . ',
					NOW() + INTERVAL (RAND() * 91 - 45) DAY + INTERVAL (RAND() * 172801 - 86400) SECOND,
					if (rand()<0.6, null, NOW() - INTERVAL (RAND() * 45) DAY + INTERVAL (RAND() * 172801 - 86400) SECOND),
					if (rand()<0.8, null, NOW() - INTERVAL (RAND() * 45) DAY + INTERVAL (RAND() * 172801 - 86400) SECOND),
					' . (mt_rand (4, 9) * 5) . ',
					' . mt_rand (3, 5) . ',
					rand())');
		}

		$this->query ('update Exam set Start_Time = Sched_Time where End_Time is not null and Start_Time is null');
	}
}

?>