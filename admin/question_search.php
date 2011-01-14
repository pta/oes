<?php
	session_start();
	$_SESSION['page'] = 'question_search.php';

	if (!isset ($_SESSION['user']))
	{
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
	<title>OES Admin - Search Question</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link href="exam.css" rel="stylesheet" type="text/css">
</HEAD>

<?php
	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);
	$res = null;

	if (isset ($_POST['submit']))
	{
		$search = $_POST['search'];
		$query = "SELECT oes_Question.ID, Text, Name FROM oes_Question join oes_Subject on oes_Subject.ID = oes_Question.Subject where Text like '%".$search."%'";

		if (($subject = $_POST['subject']) > 0)
		{
			$query .= " AND Subject = " . $subject;
		}

		$query .= ' limit 10';

		try
		{
			$result = $db->query ($query);
			$res = fetch_columns($result);
			mysql_free_result ($result);
		}
		catch (Exception $e)
		{
			?>
				<center>Xảy ra lỗi trong khi tìm kiếm.</center>
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
	<h1>Tìm câu hỏi</h1>

	<form action=# method=POST>
		<table>
			<tr><td align=center><label for=subject>Môn</label>
					<select id=subject name=subject>
						<option value=0>Tất cả</option>
						<?php
							$arr = $db->getSubjectList();
							foreach ($arr as $subject)
								echo "<option value=$subject[1]>$subject[0]</option>";
						?>
					</select>

			<tr><td height=26>

			<tr><td>Tìm câu hỏi có chứa:
			<tr align=center><td><input name=search size=39>
					<input type=submit name=submit value='Tìm kiếm'>
		</table>
	</form>

	<?php
		if (isset ($res))
		{
			echo '<table class=examtable><tr><td>ID<td>Nội dung<td>Môn';
			foreach ($res as $question)
			{
				echo '<tr align=center><td>'.$question[0];
				echo '<td><a href=question_edit.php?question='.$question[0].'>'.$question[1].'</a><td align=center>'.$question[2];
			}
			echo '</table>';
		}
	?>

</div>
</BODY>
</HTML>