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

		$result = $db->query ("select * from oes_Exam where ID = $eid");
		$exam = mysql_fetch_array ($result);
		mysql_free_result ($result);

		$test = $db->openTest ($student, $eid);

		if ($test == null)
		{
			$db->begin();

			try
			{
				$test = $db->createTest ($student, $eid, $exam['Subject'], $exam['NoQ']);

				$db->commit();
			}
			catch (Exception $e)
			{
				$db->rollback();

				?>
					<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
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
	$class = $db->getValue ("select Class from oes_Student where ID=$student");
	$class = num_value ($class);

	$result = $db->query ("select
				E.ID as ID,
				E.Name as Name,
				oes_Subject.Name as Subject,
				Time,
				(select TimeSpent from oes_Test where Exam = E.ID and Student = $student) as TimeSpent,
				Duration,
				(select count(ID) from oes_Answer join oes_TQ where Test =
						(select ID from oes_Test where Exam = E.ID and Student = $student)) as Done,
				NoQ
			from (select * from oes_Exam where Class = $class
					and StartTime is not null and EndTime is null) as E
				join oes_Subject on E.Subject = oes_Subject.ID");

	if (mysql_num_rows ($result) == 0)
	{
		echo '<center>Không có buổi thi nào cho bạn.</center>';
		mysql_free_result ($result);
		return;
	}

	echo '<h2>Chọn môn thi</h2>';
	echo '<table class=examtable cellspacing="0"><tr>';
	echo '<th>Tên<th>Môn<th>Lần<th>Đã dùng<th>Đã làm';

	$c = 0;

	while ($row = mysql_fetch_array ($result))
	{
		$ex = $row['ID'];

		$style = (($c++) & 1)?'class=alt':null;

		echo "<tr $style onClick='window.location=\"test.php?eid=$ex\"'>";

		echo '<td>' . $row['Name'];
		echo '<td>' . $row['Subject'];
		echo '<td align=right>' . $row['Time'];
		echo '<td align=right>' . ($row['TimeSpent']?$row['TimeSpent']:0) . '/' . $row['Duration'] . ' phút';
		echo '<td align=right>' . $row['Done'] . '/'. $row['NoQ'] . ' câu';
	}

	echo '</table>';

	mysql_free_result ($result);
?>