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

	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	if ($id == 'list')
	{
		$result = $db->query ("select
					E.Name as Tên,
					oes_Class.Name as Lớp,
					oes_Subject.Name as Môn,
					Time as Lần,
					E.ID as ID,
					Schedule, StartTime, EndTime
				from (select * from oes_Exam
						where EndTime is null or EndTime > CURRENT_DATE - INTERVAL 1 MONTH
					) as E
					join oes_Class on E.Class = oes_Class.ID
					join oes_Subject on E.Subject = oes_Subject.ID
				order by Schedule desc");

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

			if ($row['EndTime'])
				$style .= ' finished';
			else if ($row['StartTime'])
				$style .= ' running';

			if ($ex == $exam) $style .= ' current';

			if (($i++) & 1) $style .= ' alt';

			echo "<tr class='$style' onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$ex\")'>";

			for ($f = 0; $f < $nof; ++$f)
				echo '<td>' . $row[$f];

			echo '<td>';

			if ($row['EndTime'])
				echo '<span class=finished>Đã kết thúc</span>';
			else if ($row['StartTime'])
				echo '<span class=running>Đang thi</span>';
			else
				echo mb_ucfirst (relative_time (strtotime ($row['Schedule'])));
		}

		echo '</table>';

		mysql_free_result ($result);
	}
	else if ($id == 'detail' && $exam)
	{
		if (isset ($_GET['action']))
		{
			$action = $_GET['action'];

			if ($action == "start")
				$db->query ("update oes_Exam set StartTime = now() where ID = $exam");
			else if ($action == "end")
				$db->query ("update oes_Exam set EndTime = now() where ID = $exam");
			else
				throw new Exception ("UnknowActionException");
		}

		echo "<script>parent.loadModule ('list', 'exam_modules.php?id=list&exam=$exam');</script>";
		echo "<script>parent.setAutoStat ($exam);</script>";

		$result = $db->query ("select
					LastName,
					FirstName,
					Duration,
					Schedule,
					StartTime,
					EndTime,
					NoQ
				from (select * from oes_Exam where ID = $exam) as E
					join oes_Teacher on E.Teacher = oes_Teacher.ID");

		$row = mysql_fetch_array ($result);

		echo '<table class=web20>';

		$c = 0;
		echo '<tr' . ($c++ & 1 ? ' class=odd' : null)
				. '><td>Giáo viên<td>' . $row['LastName'] . ' ' . $row['FirstName'];
		echo '<tr' . ($c++ & 1 ? ' class=odd' : null)
				. '><td>Thời gian<td>' . $row['Duration'] . ' phút';
		echo '<tr' . ($c++ & 1 ? ' class=odd' : null)
				. '><td>Số lượng<td>' . $row['NoQ'] . ' câu hỏi';
		echo '<tr' . ($c++ & 1 ? ' class=odd' : null)
				. '><td>Lịch thi<td>' . $row['Schedule'];
		echo '<tr' . ($c++ & 1 ? ' class=odd' : null)
				. '><td>Tổng số<td>'
				. $db->getValue ("select count(ID) from oes_Test where Exam = $exam")
				. ' bài dự thi';

		echo '<tr' . ($c++ & 1 ? ' class=odd' : '');
		if (!$row['StartTime'])
			echo "><td><a href=# onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$exam&action=start\")'>Bắt đầu</a><td>";
		else
		{
			echo '><td>Bắt đầu<td>' . $row['StartTime'];

			echo '<tr' . ($c++ & 1 ? ' class=odd' : '');
			if ($row['EndTime'])
				echo '><td>Kết thúc<td>' . $row['EndTime'];
			else
				echo "><td><a href=# onClick='loadModule (\"detail\", \"exam_modules.php?id=detail&exam=$exam&action=end\")'>Kết thúc</a><td>";
		}

		echo '</table>';
	}
	else if ($id == 'stat' && $exam)
	{
			/* if no row available => stop autoload interval */
		$running = $db->getValue (
				"select (EndTime is null and StartTime is not null)
				from oes_Exam where ID = $exam");

		if (!$running)
			echo "<script>parent.clearAutoStat();</script>";

		$noq = $db->getValue ("select NoQ from oes_Exam where ID = $exam");

		$result = $db->query ("select
				IDCode,
				LastName,
				FirstName,
				DoB,
				T.TimeSpent,
				(select count(ID) from oes_Answer join oes_TQ where (Test = T.ID)) as Done,
				0 as Correct
			from oes_Student join
				(select * from oes_Test where Exam = $exam) as T
				on T.Student = oes_Student.ID");

		if (mysql_num_rows ($result) != 0)
		{
			echo '<table class=examtable cellspacing="0"><tr>';
			echo '<th>MSV<th>Họ tên<th>NS<th>Hết<th>Làm<th>Đúng<th>XS<th>Điểm';

			$c = 0;

			while ($row = mysql_fetch_array ($result))
			{
				echo (($c++)&1)?"<tr class=alt>":"<tr>";

				echo '<td>' . $row['IDCode'];
				echo '<td>' . $row['LastName'] . ' ' . $row['FirstName'];
				echo '<td>' . $row['DoB'];
				echo '<td align=right>' . $row['TimeSpent'] . ' phút';
				echo '<td align=right>' . $row['Done'] . ' câu';
				echo '<td align=right>' . $row['Correct'] . ' câu';
				echo '<td align=right>' . ($row['Done'] ? round (100 * $row['Correct'] / $row['Done']) . '%' : '---');
				echo '<td align=right>' . round (round (40 * $row['Correct'] / $noq) / 4, 2);
			}

			echo '</table>';
		}

		mysql_free_result ($result);
	}
?>