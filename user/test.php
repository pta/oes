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
	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	$exams = $db->getRunningExams ($student);

	if (count ($exams) == 0)
	{
		header ('Content-Type: text/html; charset=UTF-8');
		echo '<center>Không có buổi thi nào cho bạn.</center>';
		return;
	}

	$exam = $exams[0];

	$test = $db->openTest ($student, $exam['ID']);

	if ($test == null)
	{
		$db->begin();

		try
		{
			$test = $db->createTest ($student, $exam['ID'], $exam['Subject'],
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
?>

<HTML>
<HEAD>
	<title>OES Admin - Test</title>
</HEAD>

<BODY>
<div align=center>
	<h1>Tạo đợt thi mới</h1>

	<form action=exam_list.php method=POST>
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
				<td><input id=max_noc name=max_noc value=4>

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Hoàn thành'>
		</table>
	</form>

</div>
</BODY>
</HTML>