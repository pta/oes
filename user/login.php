<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	if (isset ($_POST['submit']))
	{
		$student_id = $_POST['student_id'];
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$dob = $_POST['dob'];
		$class = $_POST['class'];

		try
		{
			$_SESSION['student'] = $db->ensureStudent ($student_id,
					$firstname, $lastname, $dob, $class);

			header ("Location: test.php");
		}
		catch (Exception $e)
		{
			?>
				<HEAD>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
					<title>OES Admin - Student Login</title>
				</HEAD>

				<center>Đăng nhập thất bại!</center>
				<center>Xin hãy kiểm tra thông tin đã nhập.</center>
				<center><button onClick='history.back()'>Trở lại</button></center>
			<?php

			echo $e->getMessage();
			return -1;
		}
	}
?>

<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>OES Admin - Student Login</title>

	<!-- link calendar files  -->
	<script language="JavaScript" src="../js/tigra_calendar/calendar_db.js"></script>
	<link rel="stylesheet" href="../js/tigra_calendar/calendar.css">
</HEAD>
<BODY>
<div align=center>
	<h1>Thông tin thí sinh</h1>

	<form action=login.php method=POST>
		<table>
			<tr><td><label for=student_id>Mã SV</label>
				<td><input id=student_id name=student_id>

			<tr><td><label for=lastname>Họ đệm</label>
				<td><input id=lastname name=lastname>

			<tr><td><label for=firstname>Tên</label>
				<td><input id=firstname name=firstname>

			<tr><td><label for=dob>Ngày sinh</label>
				<td><input id=dob name=dob>
					<script language="JavaScript">
						var d = new Date();
						d.setFullYear (d.getFullYear() - 22);
						var sample_dob = f_tcalGenerDate (d);
						new tcal ({'controlname':'dob', 'selected':sample_dob});
					</script>

			<tr><td><label for=class>Lớp</label>
				<td><select id=class name=class>
						<option value=0>------</option>
						<?php
							$arr = $db->getClassList();
							foreach ($arr as $class)
								echo "<option value=$class[1]>$class[0]</option>";
						?>
					</select>

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Hoàn thành'>
		</table>
	</form>

</div>
</BODY>
</HTML>