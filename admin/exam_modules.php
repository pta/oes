<?php
include_once "../config.php";
include_once "../lib/Database.php";
include_once "../lib/util.php";
?>
<?php
	if (!isset ($_GET['id']))
		return;

	$id = $_GET['id'];

	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	if ($id == 'list')
	{
		$result = $db->query ("select
					E.Name as Tên,
					Class.Name as Lớp,
					Subject.Name as Môn,
					Time as Lần,
					E.ID as ID,
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

		echo '<table id=examtable cellspacing="0"><tr>';

		//$nof = mysql_num_fields ($result);
		$nof = 4;

		for ($f = 0; $f < $nof; ++$f)
			echo '<th>' . mysql_field_name ($result, $f);
		echo '<th>Lịch thi';

		$i = 0;

		while ($row = mysql_fetch_array ($result))
		{
			$exam = $row['ID'];

			echo (($i++) & 1)?'<tr class=alt':'<tr';
			echo " onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$exam\")'>";

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

		echo '</table>';

		mysql_free_result ($result);
	}
	else if ($id == 'detail' && isset ($_GET['exam']))
	{
		$exam = $_GET['exam'];

		$result = $db->query ("select
					concat (Teacher.LastName, ' ', Teacher.FirstName) as 'Giáo viên',
					Sched_Time as 'Lịch thi',
					Start_Time as 'Bắt đầu',
					End_Time as 'Kết thúc',
					concat (Duration, ' phút') as 'Thời gian',
					concat (NoQ, ' câu hỏi') as 'Số lượng',
					concat (Max_NoC, ' lựa chọn') as 'Tối đa',
					if (Mul_Choice, 'nhiều phương án', 'một phương án') as 'Lựa chọn'
				from (select * from Exam where ID = $exam) as E
					join Teacher on E.Teacher = Teacher.ID");

		if ($result)
		{
			$nof = mysql_num_fields ($result);
			$row = mysql_fetch_array ($result);

			echo '<table>';
			for ($i = 0; $i < $nof; ++$i)
			{
				$field = mysql_field_name ($result, $i);

				if ($row[$i])
					echo "<tr><td>$field<td>" . $row[$i];
			}
			echo '</table>';
		}

		mysql_free_result ($result);
	}
?>