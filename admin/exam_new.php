<?php
	session_start();
	if (!isset ($_SESSION['user']))
	{
		$_SESSION['page'] = 'exam_new.php';
		header ("Location: login.php");
		return;
	}
?>
<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>

<HTML>
<HEAD>
	<title>OES Admin - New Exam</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<!-- link calendar files  -->
	<script language="JavaScript" src="../js/tigra_calendar/calendar_db.js"></script>
	<link rel="stylesheet" href="../js/tigra_calendar/calendar.css">
</HEAD>

<?php
	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	if (isset ($_POST['submit']))
	{
		$name = $_POST['name'];
		$time = $_POST['time'];

		$duration = (int) $_POST['duration'];
		$sched_date = $_POST['sched_date'];
		$sched_hour = $_POST['sched_hour'];
		$sched_time = $sched_date . ' ' . $sched_hour . ':00';
		$noq = $_POST['noq'];
		$max_noc = $_POST['max_noc'];
		$mul_choice = isset ($_POST['mul_choice']);

		$db->begin();

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
					echo "<center>Không thể tạo <b>Lớp</b> mới với tên '$newclass'.</center>";
					throw $e;
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
					echo "<center>Không thể tạo <b>Môn</b> mới với tên '$newsubject'.</center>";
					throw $e;
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
					echo "<center>Không thể tạo <b>Giáo viên</b> mới với tên '$newteacher'.</center>";
					throw $e;
				}
			}

			$db->insertExam ($name, $class, $subject, $time, $teacher, $duration, $sched_time, $noq, $max_noc, $mul_choice);

			$db->commit();
			echo "<center>Tạo đợt thi mới thành công!</center>";
		}
		catch (Exception $e)
		{
			$db->rollback();

			?>
				<center>Không thể tạo <b>Đợt thi</b> mới.</center>
				<center>Xin hãy kiểm tra thông tin đã nhập.</center>
				<center><button onClick='history.back()'>Trở lại</button></center>
			<?php

			echo $e->getMessage();
			return -1;
		}
	}
?>

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
								$tname = $teacher[1] . ' ' . $teacher[0];
								echo "<option value=$teacher[2]>$tname</option>";
							}
						?>
					</select>
					<input id=newteacher name=newteacher>

			<tr><td><label for=duration>Thời gian</label>
				<td><input id=duration name=duration value=60> phút

			<tr><td><label for=sched_date>Ngày thi</label>
				<td><script language="JavaScript">
						var today = f_tcalGenerDate (new Date());
						document.writeln ("<input id=sched_date name=sched_date value=" + today + ">");
						new tcal ({'controlname':'sched_date'});
					</script>

			<tr><td><label for=sched_hour>Giờ thi</label>
				<td><input id=sched_hour name=sched_hour value=15:00> (HH:mm)

			<tr><td><label for=noq>Số câu hỏi</label>
				<td><input id=noq name=noq value=30>

			<tr><td><label for=max_noc>Số lựa chọn tối đa</label>
				<td><input id=max_noc name=max_noc value=5>

			<input type=checkbox id=mul_choice name=mul_choice>
				<label for=mul_choice>Cho phép câu hỏi nhiều lựa chọn</label>

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Hoàn thành'>
		</table>
	</form>

</div>
</BODY>
</HTML>