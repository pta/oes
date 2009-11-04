<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	session_start();

	if (!isset ($_SESSION['student']))
	{
		header ("Location: login.php");
	}

	$student = $_SESSION['student'];
?>
<?php
	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	$exams = $db->getRunningExams ($student);

	if (count ($exams) == 0)
	{
		header ('Content-Type: text/html; charset=UTF-8');
		echo '<center>Không có buổi thi nào cho bạn.</center>';
		return;
	}

	$exam = $exams[0];

	$test = $db->openTest ($student, $exam['ID']);

	if ($test == null)
	{
		$db->begin();

		try
		{
			$test = $db->createTest ($student, $exam['ID'], $exam['Subject'],
					$exam['NoQ'], $exam['Max_NoC']);

			$db->commit();
		}
		catch (Exception $e)
		{
			$db->rollback();

			header ('Content-Type: text/html; charset=UTF-8');
			?>
				<center>Không thể tạo <b>Bài thi</b> mới.</center>
				<center><button onClick='history.back()'>Trở lại</button></center>
			<?php

			echo $e->getMessage();
			return -1;
		}
	}

	$_SESSION['test'] = $test;

	header ('Location: question.php');
?>