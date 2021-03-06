<?php
include_once "../config.php";
include_once "../lib/DBConnection.php";

class Database extends DBConnection
{
	function getClassList()
	{
		return $this->getColumns ('Name, ID', 'oes_Class');
	}

	function getSubjectList()
	{
		return $this->getColumns ('Name, ID', 'oes_Subject');
	}

	function getTeacherList()
	{
		return $this->getColumns ('FirstName, LastName, ID', 'oes_Teacher');
	}

	function ensureStudent ($student_id, $firstname, $lastname, $dob, $class)
	{
		if ($student_id != null)
			$id = $this->getValue ("select ID from oes_Student where IDCode = '$student_id'");

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

		$this->query ("insert into oes_Student values (null, $student_id, $firstname, $lastname, $dob, $class)");
		return $this->getLastInsertID();
	}

	function getStudentID ($student_id, $firstname, $lastname, $dob, $class)
	{
		$student_id = str_value ($student_id);
		$firstname = str_value ($firstname);
		$lastname = str_value ($lastname);
		$dob = str_value ($dob);
		$class = num_value ($class);

		return $this->getValue ("select ID from oes_Student where (IDCode = $student_id) and (FirstName = $firstname) and LastName = $lastname and DoB = $dob and Class = $class");
	}

	function insertClass ($class)
	{
		if (strlen ($class) > 4)
			$k = num_value ($class[4]);
		else
			$k = 0;
		$class = str_value ($class);

		$this->query ("insert into oes_Class values (null, $class, $k)");
		return $this->getLastInsertID();
	}

	function insertSubject ($subject)
	{
		$subject = str_value ($subject);
		$this->query ("insert into oes_Subject values (null, $subject)");
		return $this->getLastInsertID();
	}

	function insertTeacher ($teacher)
	{
		$lastname = substr (strrchr($teacher, 32), 1);
		$firstname = substr ($teacher, 0, strlen($teacher) - strlen($lastname));

		$lastname = str_value ($lastname);
		$firstname = str_value ($firstname);

		$this->query ("insert into oes_Teacher values (null, $firstname, $lastname)");
		return $this->getLastInsertID();
	}

	function insertExam ($name, $class, $subject, $time, $teacher, $duration, $noq, $sched_time)
	{
		$name = str_value ($name);
		$class = num_value ($class);
		$subject = num_value ($subject);
		$teacher = num_value ($teacher);
		$duration = num_value ($duration);
		$noq = num_value ($noq);

		$this->query ("insert into oes_Exam values (null, $name, $class, $subject, $time, $teacher, $duration, $noq, '$sched_time', null, null)");
		return $this->getLastInsertID();
	}

	function insertQuestion ($text, $subject, $shuffleable, $rank)
	{
		$text = str_value ($text);
		$subject = num_value ($subject);

		$this->query ("insert into oes_Question values (null, $text, $subject, $shuffleable, $rank, null)");
		return $this->getLastInsertID();
	}

	function updateQuestion ($question, $text, $subject, $shuffleable, $rank)
	{
		$id = $this->insertQuestion ($text, $subject, $shuffleable, $rank);
		$this->query ("update oes_Question set Newer = $id where ID = $question");
		return $id;
	}

	function insertChoice ($question, $text, $correct, $exclusive)
	{
		$text = str_value ($text);

		$this->query ("insert into oes_Choice values (null, $question, $text, $correct, $exclusive)");
		return $this->getLastInsertID();
	}

	function openTest ($student, $exam)
	{
		return $this->getValue ("select ID from oes_Test"
				. " where Student = $student and Exam = $exam");
	}

	function createTest ($student, $exam, $subject, $noq)
	{
		$subject = num_value ($subject);

		$test = $this->insertTest ($student, $exam);

		$result = $this->query ("select ID from oes_Question"
				. " where Subject = $subject order by rand() limit $noq");
		$questions = fetch_column ($result);
		mysql_free_result ($result);

		foreach ($questions as $question)
		{
			$this->insertTQ ($test, $question);
		}

		return $test;
	}

	function insertTQ ($test, $question)
	{
		$this->query ("insert into oes_TQ values (null, $test, $question)");
	}

	function insertAnswer ($tq, $choice)
	{
		$result = $this->query ("select * from oes_Choice where ID = $choice");
		$rowChoice = mysql_fetch_array ($result);
		mysql_free_result ($result);

		$isChose = $this->getValue ("select TQ from oes_Answer where TQ = $tq and Choice = $choice");

		if ($rowChoice['Exclusive'])
		{
			if ($isChose) return false;

			$this->begin();
			$this->query ("delete from oes_Answer where TQ = $tq");
			$this->query ("insert into oes_Answer values ($tq, $choice)");
			$this->commit();
		}
		else
		{
			if ($isChose)
				$this->query ("delete from oes_Answer
						where TQ = $tq and Choice = $choice");
			else
			{
				$this->begin();
				$this->query ("delete from oes_Answer where TQ = $tq and
						(select Exclusive from oes_Choice where ID = Choice) = 1");
				$this->query ("insert into oes_Answer values ($tq, $choice)");
				$this->commit();
			}
		}

		return true;
	}

	function insertTest ($student, $exam)
	{
		$this->query ("insert into oes_Test values (null, $student, $exam, null)");
		return $this->getLastInsertID();
	}

	function insertRandomExam()
	{
		$noq = 13;

		for ($i = 0; $i < $noq; ++$i)
		{
			$this->query ('insert ignore into oes_Exam values (
					null,
					left(md5(rand()),6),
					0.5 + rand() * (select count(*) from oes_Class),
					0.5 + rand() * (select count(*) from oes_Subject),
					0.5 + rand() * 4,
					0.5 + rand() * (select count(*) from oes_Teacher),
					' . (mt_rand (5, 9) * 5) . ',
					' . (mt_rand (4, 9) * 5) . ',
					NOW() + INTERVAL (RAND() * 91 - 45) DAY + INTERVAL (RAND() * 172801 - 86400) SECOND,
					if (rand()<0.6, null, NOW() - INTERVAL (RAND() * 45) DAY + INTERVAL (RAND() * 172801 - 86400) SECOND),
					if (rand()<0.8, null, NOW() - INTERVAL (RAND() * 45) DAY + INTERVAL (RAND() * 172801 - 86400) SECOND))');
		}

		$this->query ('update oes_Exam set StartTime = Schedule where EndTime is not null and StartTime is null');
	}
}

?>