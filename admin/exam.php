<?php
	session_start();
	if (!isset ($_SESSION['user']))
	{
		$_SESSION['page'] = 'exam.php';
		header ("Location: login.php");
		return;
	}
?>
<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	header ('Content-Type: text/html; charset=UTF-8');

	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	$result = $db->query ("select E.Name, Class.Name as Class, Subject.Name as Subject, Time,
				concat (Teacher.LastName, ' ', Teacher.FirstName) as Teacher,
				Duration, Sched_Time, Start_Time, End_Time, NoQ, Max_NoC, Mul_Choice
			from (select * from Exam
					where End_Time is null or End_Time > CURRENT_DATE - INTERVAL 1 MONTH
				) as E
				join Class on E.Class = Class.ID
				join Subject on E.Subject = Subject.ID
				join Teacher on E.Teacher = Teacher.ID
			order by Sched_Time desc");

	$fetch_columns ($result);

	mysql_free_result ($result);
?>