<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	session_start();
	unset ($_SESSION['user']);

	if (isset ($_POST['submit']))
	{
		$id = str_value ($_POST['id']);
		$pass = str_value ($_POST['pass']);

		$db = new Database (DB_HOST, DB_USER, DB_PASS);
		$db->selectDatabase (DB_NAME);

		$user = $db->getValue ("select ID from User where ID = $id and Pass = sha1($pass)");

		if ($user != null)
		{
			$_SESSION['user'] = $user;

			if (isset ($_SESSION['page']))
				$page = $_SESSION['page'];
			else
				$page = 'exam_new.php';

			header ("Location: $page");
		}
		else
		{
			header ('Content-Type: text/html; charset=UTF-8');
			?>
				<center>Đăng nhập thất bại!</center>
				<center>Xin hãy kiểm tra thông tin đã nhập.</center>
				<center><button onClick='history.back()'>Trở lại</button></center>
			<?php
		}

		return;
	}
?>

<HTML>
<HEAD>
	<title>OES - Student Login</title>

	<!-- link calendar files  -->
	<script language="JavaScript" src="../js/tigra_calendar/calendar_db.js"></script>
	<link rel="stylesheet" href="../js/tigra_calendar/calendar.css">
</HEAD>
<BODY>
<div align=center>
	<h1>Đăng nhập quản trị</h1>

	<form action=login.php method=POST>
		<table>
			<tr><td><label for=id>ID</label>
				<td><input id=id name=id>

			<tr><td><label for=pass>Mật khẩu</label>
				<td><input id=pass name=pass type=password>

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Đăng nhập'>
		</table>
	</form>

</div>
</BODY>
</HTML>