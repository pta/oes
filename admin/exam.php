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
include_once "../lib/util.php";
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="../ptajax/module.js"></script>
</head>
<?php
	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	$result = $db->query ("select
				E.Name as Tên,
				Class.Name as Lớp,
				Subject.Name as Môn,
				Time as Lần,
				concat (Teacher.LastName, ' ', Teacher.FirstName) as Teacher,
				Duration,
				UNIX_TIMESTAMP (Sched_Time) as Sched_Time,
				UNIX_TIMESTAMP (Start_Time) as Start_Time,
				UNIX_TIMESTAMP (End_Time) as End_Time,
				NoQ, Max_NoC, Mul_Choice
			from (select * from Exam
					where End_Time is null or End_Time > CURRENT_DATE - INTERVAL 1 MONTH
				) as E
				join Class on E.Class = Class.ID
				join Subject on E.Subject = Subject.ID
				join Teacher on E.Teacher = Teacher.ID
			order by Sched_Time desc");

	echo '<h3>Quản lý buổi thi</h3>';

	echo '<div align=center><table id=examtable cellspacing="0">';
	echo '<tr>';

	//$nof = mysql_num_fields ($result);
	$nof = 4;

	for ($f = 0; $f < $nof; ++$f)
		echo '<th>' . mysql_field_name ($result, $f);
	echo '<th>Lịch thi';

	$i = 0;

	while ($row = mysql_fetch_array ($result))
	{
		echo (($i++) & 1)?'<tr class=alt>':'<tr>';

		for ($f = 0; $f < $nof; ++$f)
			echo '<td>' . $row[$f];

		echo '<td>';

		if ($row['End_Time'])
			echo 'Đã kết thúc';
		else if ($row['Start_Time'])
			echo 'Đang thi';
		else
			echo mb_ucfirst (relative_time ($row['Sched_Time']));
	}

	echo '</table></div>';

	mysql_free_result ($result);
?>

<script>insertModule ('detail', 'about:blank');</script>
