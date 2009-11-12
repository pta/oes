<?php
include_once "../config.php";
include_once "../lib/Database.php";
include_once "../lib/util.php";
?>
<?php
	if (!isset ($_GET['id']))
		return;

	$id = $_GET['id'];

	if (isset ($_GET['exam']))
		$exam = $_GET['exam'];
	else
		$exam = null;

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
					Sched_Time, Start_Time, End_Time
				from (select * from Exam
						where End_Time is null or End_Time > CURRENT_DATE - INTERVAL 1 MONTH
					) as E
					join Class on E.Class = Class.ID
					join Subject on E.Subject = Subject.ID
				order by Sched_Time desc");

		echo '<table id=examtable cellspacing="0"><tr>';

		//$nof = mysql_num_fields ($result);
		$nof = 4;

		for ($f = 0; $f < $nof; ++$f)
			echo '<th>' . mysql_field_name ($result, $f);
		echo '<th>Lịch thi';

		$i = 0;

		while ($row = mysql_fetch_array ($result))
		{
			$ex = $row['ID'];

			$style = '';

			if ($row['End_Time'])
				$style .= ' finished';
			else if ($row['Start_Time'])
				$style .= ' running';

			if ($ex == $exam) $style .= ' current';

			if (($i++) & 1) $style .= ' alt';

			echo "<tr class='$style' onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$ex\")'>";

			for ($f = 0; $f < $nof; ++$f)
				echo '<td>' . $row[$f];

			echo '<td>';

			if ($row['End_Time'])
				echo '<span class=finished>Đã kết thúc</span>';
			else if ($row['Start_Time'])
				echo '<span class=running>Đang thi</span>';
			else
				echo mb_ucfirst (relative_time (strtotime ($row['Sched_Time'])));
		}

		echo '</table>';

		mysql_free_result ($result);
	}
	else if ($id == 'detail' && $exam)
	{
		if (isset ($_GET['action']))
		{
			$action = $_GET['action'];

			if ($action == "Bắt đầu")
				$db->query ("update Exam set Start_Time = now() where ID = $exam");
			else if ($action == "Kết thúc")
				$db->query ("update Exam set End_Time = now() where ID = $exam");
			else
				throw new Exception ("UnknowActionException");
		}

		echo "<script>parent.loadModule ('list', 'exam_modules.php?id=list&exam=$exam');</script>";

		$result = $db->query ("select
					concat (Teacher.LastName, ' ', Teacher.FirstName) as 'Giáo viên',
					Sched_Time as 'Lịch thi',
					Start_Time as 'Bắt đầu',
					End_Time as 'Kết thúc',
					concat (Duration, ' phút') as 'Thời gian',
					concat (NoQ, ' câu hỏi') as 'Số lượng',
					concat (Max_NoC, ' lựa chọn') as 'Tối đa',
					if (Mul_Choice, 'nhiều đáp án', 'một đáp án') as 'Lựa chọn'
				from (select * from Exam where ID = $exam) as E
					join Teacher on E.Teacher = Teacher.ID");

		$nof = mysql_num_fields ($result);
		$row = mysql_fetch_array ($result);

		if (!$row[0])
			throw new Exception("WTH?");

		$hasAction = false;

		echo '<table class=web20>';
		for ($i = $c = 0; $i < $nof; ++$i)
		{
			$field = mysql_field_name ($result, $i);

			if ($row[$i])
			{
				echo "<tr" . ($c++ & 1 ? ' class=odd' : '')
						. "><td>$field<td>" . $row[$i];
			}
			else if (!$hasAction)
			{
				$hasAction = true;

				echo "<tr" . ($c++ & 1 ? ' class=odd' : '')
						. "><td>"
						. "<a href=# onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$exam&action=$field\")'>"
						. "$field</a><td>";
			}
		}
		mysql_free_result ($result);

		echo '</table>';
	}
?>