<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>

<?php
	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	if (isset ($_POST['submit']))
	{
		$name = $_POST['name'];
		$time = $_POST['time'];
		$duration = $_POST['duration'];
		$sched_date = $_POST['sched_date'];
		$sched_hour = $_POST['sched_hour'];
		$sched_time = $sched_date . ' ' . $sched_hour . ':00';

		try
		{
			if (($class = $_POST['class']) == 0)
			{
				$newclass = $_POST['newclass'];
				try
				{
					$class = $db->insertClass ($newclass);
				}
				catch (Exception $e)
				{
					echo "<center>Không thể tạo <b>lớp</b> mới với tên '$newclass'.</center>";
					echo "<center>Xin hãy kiểm tra thông tin đã nhập.</center>";
					echo "<center><button onClick='history.back()'>Trở lại</button></center>";
					echo $e->getMessage();
					return -1;
				}
			}

			if (($subject = $_POST['subject']) == 0)
			{
				$newsubject = $_POST['newsubject'];
				try
				{
					$subject = $db->insertSubject ($newsubject);
				}
				catch (Exception $e)
				{
					echo "<center>Không thể tạo <b>môn</b> mới với tên '$newsubject'.</center>";
					echo "<center>Xin hãy kiểm tra thông tin đã nhập.</center>";
					echo "<center><button onClick='history.back()'>Trở lại</button></center>";
					echo $e->getMessage();
					return -1;
				}
			}

			if (($teacher = $_POST['teacher']) == 0)
			{
				$newteacher = $_POST['newteacher'];
				try
				{
					$teacher = $db->insertTeacher ($newteacher);
				}
				catch (Exception $e)
				{
					echo "<center>Không thể tạo <b>giáo viên</b> mới với tên '$newteacher'.</center>";
					echo "<center>Xin hãy kiểm tra thông tin đã nhập.</center>";
					echo "<center><button onClick='history.back()'>Trở lại</button></center>";
					echo $e->getMessage();
					return -1;
				}
			}

			$db->insertExam ($name, $class, $subject, $time, $teacher, $duration, $sched_time);

			echo "<center>Tạo đợt thi mới thành công!</center>";
		}
		catch (Exception $e)
		{
			echo "<center>Không thể tạo <b>đợt thi</b> mới.</center>";
			echo "<center>Xin hãy kiểm tra thông tin đã nhập.</center>";
			echo "<center><button onClick='history.back()'>Trở lại</button></center>";
			echo $e->getMessage();
			return -1;
		}
	}
?>

<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>OES Admin - Exam</title>
</HEAD>
<BODY>
<div align=center>
	<h1>Tạo đợt thi mới</h1>

	<form action=exam_new.php method=POST>
		<table>
			<tr><td><label for=name>Tên</label>
				<td><input id=name name=name>

			<tr><td><label for=class>Lớp</label>
				<td><select id=class name=class>
						<option value=0>Tạo mới</option>
						<?php
							$arr = $db->getClassList();
							foreach ($arr as $class)
								echo "<option value=$class[1]>$class[0]</option>";
						?>
					</select>
					<input id=newclass name=newclass>

			<tr><td><label for=subject>Môn</label>
				<td><select id=subject name=subject>
						<option value=0>Tạo mới</option>
						<?php
							$arr = $db->getSubjectList();
							foreach ($arr as $subject)
								echo "<option value=$subject[1]>$subject[0]</option>";
						?>
					</select>
					<input id=newsubject name=newsubject>

			<tr><td><label for=time>Lần</label>
				<td><input id=time name=time value=1>

			<tr><td><label for=teacher>Giáo viên</label>
				<td><select id=teacher name=teacher>
						<option value=0>Tạo mới</option>
						<?php
							$arr = $db->getTeacherList();
							foreach ($arr as $teacher)
							{
								$tname = $teacher[0] . ' ' . $teacher[1];
								echo "<option value=$teacher[2]>$tname</option>";
							}
						?>
					</select>
					<input id=newteacher name=newteacher>

			<tr><td><label for=duration>Thời gian</label>
				<td><input id=duration name=duration value=90> phút

			<tr><td><label for=sched_date>Ngày  thi</label>
				<td><input id=sched_date name=sched_date> (dd/mm/yy)

			<tr><td><label for=sched_hour>Giờ thi</label>
				<td><input id=sched_hour name=sched_hour value=15:00> (HH:mm)

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Hoàn thành'>
		</table>
	</form>

</div>
</BODY>
</HTML>