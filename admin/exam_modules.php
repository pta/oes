<?php
include_once "../config.php";
include_once "../lib/Database.php";
include_once "../lib/util.php";
?>
<?php
	function getCorrectCount ($db, $test)
	{
		$correct = 0;
		$result = $db->query ("select ID, Question from oes_TQ where Test = $test");

		while ($rowTQ = mysql_fetch_array ($result))
		{
			$tq = $rowTQ['ID'];
			$wrong = $db->getValue ("select count(Choice) from oes_Answer where TQ = $tq and
			(select Correct from oes_Choice where ID = Choice) = 0");

			if (!$wrong)
			{
				$question = $rowTQ['Question'];

				$noa = $db->getValue ("select count(Choice) from oes_Answer where TQ = $tq");
				$noc = $db->getValue ("select count(ID) from oes_Choice where Question = $question and Correct = 1");

				if ($noa === $noc)
					++$correct;
			}
		}

		mysql_free_result ($result);
		return $correct;
	}
?>
<?php
	if (!isset ($_GET['action']))
		return;

	$action = $_GET['action'];

	if (isset ($_GET['exam']))
		$exam = $_GET['exam'];
	else
		$exam = null;

	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	switch ($action)
	{
		case 'init':
			$update['list'] = true;
			break;

		case 'detail':
			$running = $db->getValue (
					"select (EndTime is null and StartTime is not null)
					from oes_Exam where ID = $exam");

			if ($running)
				echo "<script>parent.setStatInterval($exam);</script>";
			else
				echo "<script>parent.clearStatInterval();</script>";

			$update['detail'] = $update['list'] = $update['stat'] = true;
			break;

		case 'start':
			$db->query ("update oes_Exam set StartTime = now() where ID = $exam");
			echo "<script>parent.setStatInterval($exam);</script>";

			$update['detail'] = $update['list'] = true;
			break;

		case 'stop':
			$db->query ("update oes_Exam set EndTime = now() where ID = $exam");
			echo "<script>parent.clearStatInterval();</script>";

			$update['detail'] = $update['list'] = $update['stat'] = true;
			break;

		case 'stat':
			$update['stat'] = true;
			break;
	}

	if (isset ($update['list']))
	{
		echo '<div id=list>';

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

			echo "<tr class='$style' onClick='loader.load (\"exam_modules.php?action=detail&exam=$ex\")'>";

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
		echo '</div>';

		mysql_free_result ($result);
	}

	if (isset ($update['detail']))
	{
		echo '<div id=detail>';

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

		echo '<tr' . ($c++ & 1 ? ' class=odd' : '');
		if (!$row['StartTime'])
			echo "><td><a href=# onClick='loader.load (\"exam_modules.php?action=start&exam=$exam\")'>Bắt đầu</a><td>";
		else
		{
			echo '><td>Bắt đầu<td>' . $row['StartTime'];

			echo '<tr' . ($c++ & 1 ? ' class=odd' : '');
			if ($row['EndTime'])
				echo '><td>Kết thúc<td>' . $row['EndTime'];
			else
				echo "><td><a href=# onClick='loader.load (\"exam_modules.php?action=stop&exam=$exam\")'>Kết thúc</a><td>";
		}

		echo '</table>';
		echo '</div>';
	}

	if (isset ($update['stat']))
	{
		echo '<div id=stat>';

		$noq = $db->getValue ("select NoQ from oes_Exam where ID = $exam");

		$result = $db->query ("select
				T.ID as Test,
				IDCode,
				LastName,
				FirstName,
				DoB,
				T.TimeSpent,
				(select count(distinct ID) from oes_Answer join oes_TQ on ID = TQ
						where Test = T.ID)
						as Done
			from oes_Student join
				(select * from oes_Test where Exam = $exam) as T
				on T.Student = oes_Student.ID");

		$nor = mysql_num_rows ($result);
		if ($nor == 0)
			echo "<div id=sum>Chưa có bài dự thi nào</div>";
		else
		{
			echo "<div id=sum>Tổng số $nor bài dự thi</div>";
			echo '<table class=examtable cellspacing="0"><tr>';
			echo '<th>MSV<th>Họ tên<th>NS<th>Hết<th>Làm<th>Đúng<th>XS<th>Điểm';

			$c = 0;

			while ($row = mysql_fetch_array ($result))
			{
				echo (($c++)&1)?"<tr class=alt>":"<tr>";

				$correct = getCorrectCount ($db, $row['Test']);

				echo '<td>' . $row['IDCode'];
				echo '<td>' . $row['LastName'] . ' ' . $row['FirstName'];
				echo '<td>' . $row['DoB'];
				echo '<td align=right>' . $row['TimeSpent'] . ' phút';
				echo '<td align=right>' . $row['Done'] . ' câu';
				echo '<td align=right>' . $correct . ' câu';
				echo '<td align=right>' . ($row['Done'] ? round (100 * $correct / $row['Done']) . '%' : '---');
				echo '<td align=right>' . round (round (40 * $correct / $noq) / 4, 2);
			}

			echo '</table>';
		}

		echo '</div>';

		mysql_free_result ($result);
	}
?>