<?php
	session_start();
	$_SESSION['page'] = 'question_edit.php';

	if (!isset ($_SESSION['user']))
	{
		header ("Location: login.php");
		return;
	}

	if (!isset ($_GET['question']))
	{
		header ("Location: question_search.php");
		return;
	}
?>
<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>

<HTML>
<HEAD>
	<title>OES Admin - Edit Question</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</HEAD>

<?php
	$question = $_GET['question'];

	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	if (isset ($_POST['submit']))
	{
		$question = $_POST['question'];

		$choice = array();
		for ($i = 0; strlen ($_POST["choice$i"]) > 0; ++$i)
			$choice[$i] = array ($_POST["choice$i"],
					isset ($_POST["correct$i"]) ? 'true' : 'false',
					isset ($_POST["exclusive$i"]) ? 'true' : 'false');

		$db->begin();

		try
		{
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

			$questionID = $db->insertQuestion ($question, $subject,
					isset ($_POST['shuffleable']) ? 'true' : 'false', 0.5);

			foreach ($choice as $c)
				$db->updateChoice ($questionID, $c[0], $c[1], $c[2]);

			$db->commit();
			echo "<center>Chỉnh sửa câu hỏi thành công!</center>";
		}
		catch (Exception $e)
		{
			$db->rollback();

			?>
				<center>Không thể sửa <b>Câu hỏi</b> mới.</center>
				<center>Xin hãy kiểm tra thông tin đã nhập.</center>
				<center><button onClick='history.back()'>Trở lại</button></center>
			<?php

			echo $e->getMessage();
			return -1;
		}
	}

	$result = $db->query ("select * from oes_Question where ID = $question");
	$question_assoc = fetch_assoc ($result);
	mysql_free_result ($result);

	$text = $question_assoc[0]['Text'];
	$subject = $question_assoc[0]['Subject'];
	$shuffleable = $question_assoc[0]['Shuffleable'];

	$result = $db->query ("select * from oes_Choice where Question = $question");
	$choice = fetch_columns ($result);
	mysql_free_result ($result);
?>

<BODY>
<div align=center>
	<h1>Sửa câu hỏi</h1>

	<form action=question_edit.php method=POST>
		<table>
			<tr><td align=center><label for=subject>Môn</label>
					<select id=subject name=subject>
						<option value=0>Tạo mới</option>
						<?php
							$arr = $db->getSubjectList();
							foreach ($arr as $subject)
								echo "<option value=$subject[1]>$subject[0]</option>";
						?>
					</select>
					<input id=newsubject name=newsubject>

			<tr><td>
				<table>
					<tr><td>Câu hỏi
					<tr><td><textarea cols=60 rows=6 id=question name=question></textarea>
					<tr><td>Lựa chọn <label><input type=checkbox name=shuffleable>Cho phép đảo chỗ</label>
					<tr><td>
						<table>
							<script>
								for (var i = 0; i < 6; ++i)
								{
									document.writeln ("<tr><td><input size=34 name=choice" + i + ">");
									document.writeln ("<label><input type=checkbox name=correct" + i + ">Đúng</label>");
									document.writeln ("<label><input type=checkbox name=exclusive" + i + ">Duy nhất</label>");
								}
							</script>
						</table>
				</table>

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Hoàn thành'>
		</table>
	</form>

</div>
</BODY>
</HTML>