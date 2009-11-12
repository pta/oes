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

		echo '<table class=examtable cellspacing="0"><tr>';

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
		echo "<script>parent.setAutoStat ('$exam');</script>";

		$result = $db->query ("select
					concat (Teacher.LastName, ' ', Teacher.FirstName) as 'Giáo viên',
					concat (Duration, ' phút') as 'Thời gian',
					concat (NoQ, ' câu hỏi') as 'Số lượng',
					concat (Max_NoC, ' lựa chọn') as 'Tối đa',
					if (Mul_Choice, 'nhiều đáp án', 'một đáp án') as 'Lựa chọn',
					concat ((select count(ID) from Test where Exam = $exam), ' bài dự thi') as 'Tổng số',
					Sched_Time as 'Lịch thi',
					Start_Time as 'Bắt đầu',
					End_Time as 'Kết thúc'
				from (select * from Exam where ID = $exam) as E
					join Teacher on E.Teacher = Teacher.ID");

		$nof = mysql_num_fields ($result);
		$row = mysql_fetch_array ($result);

		echo '<table class=web20>';

		for ($i = $c = 0; $i < $nof; ++$i)
		{
			$field = mysql_field_name ($result, $i);

			if ($row[$i])
			{
				echo "<tr" . ($c++ & 1 ? ' class=odd' : '')
						. "><td>$field<td>" . $row[$i];
			}
			else
			{
				echo "<tr" . ($c++ & 1 ? ' class=odd' : '')
						. "><td>"
						. "<a href=# onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$exam&action=$field\")'>"
						. "$field</a><td>";

				break;
			}
		}
		mysql_free_result ($result);

		echo '</table>';
	}
	else if ($id == 'stat' && $exam)
	{
			/* if no row available => stop autoload interval */
		$running = $db->getValue (
				"select (End_Time is null and Start_Time is not null)
				from Exam where ID = $exam");

		if (!$running)
			echo "<script>parent.clearAutoStat();</script>";

		$noq = $db->getValue ("select NoQ from Exam where ID = $exam");

		$result = $db->query ("select
				Student_ID as 'MSV',
				concat (LastName, ' ', FirstName) as 'Họ Tên',
				concat (T.Time_Spent, ' phút') as 'Hết',
				concat ((select count(Answer) from Test_Answer where Test = T.ID), ' câu') as Làm,
				concat ((select count(Answer) from Test_Answer where Test = T.ID
							and (select Correct from Choice where Answer = Choice.ID)), ' câu') as Đúng,
				concat (ifnull((select round(100 * Đúng / Làm)), ''), '%') as XS,
				round (round (40 * (select Đúng) / $noq) / 4, 2) as Điểm
			from Student join
				(select * from Test where Exam = $exam) as T
				on T.Student = Student.ID");

		if (mysql_num_rows ($result) != 0)
		{
			echo '<table class=examtable cellspacing="0"><tr>';

			$nof = mysql_num_fields ($result);

			for ($f = 0; $f < $nof; ++$f)
				echo '<th>' . mysql_field_name ($result, $f);

			$i = 0;

			while ($row = mysql_fetch_array ($result))
			{
				if (($i++) & 1)
					echo "<tr class=alt>";
				else
					echo "<tr>";

				for ($f = 0; $f < $nof; ++$f)
				{
					echo '<td>' . $row[$f];
				}
			}

			echo '</table>';
		}

		mysql_free_result ($result);
	}
?>