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
	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	if (isset ($_GET['eid']))
	{
		$eid = $_GET['eid'];

		$result = $db->query ("select * from Exam where ID = $eid");
		$exam = mysql_fetch_array ($result);
		mysql_free_result ($result);

		$test = $db->openTest ($student, $eid);

		if ($test == null)
		{
			$db->begin();

			try
			{
				$test = $db->createTest ($student, $eid, $exam['Subject'],
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
		return;
	}
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="../ptajax/module.js"></script>
	<link href="test.css" rel="stylesheet" type="text/css">
</head>
<?php
	$class = $db->getValue ("select Class from Student where ID=$student");
	$class = num_value ($class);

	$result = $db->query ("select
				E.ID as ID,
				E.Name as Tên,
				Subject.Name as Môn,
				Time as Lần,
				concat (ifnull((select Time_Spent from Test where Exam = E.ID and Student = $student),0),
						'/', Duration, ' phút') as 'Đã dùng',
				concat ((select count(Answer) from Test_Answer
						where Test = (select ID from Test where Exam = E.ID and Student = $student)),
						'/', NoQ, ' câu') as 'Số lượng',
				if (Mul_Choice, 'nhiều đáp án', 'một đáp án') as 'Lựa chọn'
			from (select * from Exam where Class = $class
					and Start_Time is not null and End_Time is null) as E
				join Subject on E.Subject = Subject.ID");

	if (mysql_num_rows ($result) == 0)
	{
		echo '<center>Không có buổi thi nào cho bạn.</center>';
		mysql_free_result ($result);
		return;
	}

	echo '<h2>Chọn môn thi</h2>';
	echo '<table class=examtable cellspacing="0"><tr>';

	$nof = mysql_num_fields ($result);

	for ($f = 1; $f < $nof; ++$f)
		echo '<th>' . mysql_field_name ($result, $f);

	$i = 0;

	while ($row = mysql_fetch_array ($result))
	{
		$ex = $row['ID'];

		$style = (($i++) & 1)?'alt':null;

		echo "<tr class='$style' onClick='window.location=\"test.php?eid=$ex\"'>";

		for ($f = 1; $f < $nof; ++$f)
			echo '<td>' . $row[$f];
	}

	echo '</table>';

	mysql_free_result ($result);
?>