<?php
	session_start();
	$_SESSION['page'] = 'question_edit.php';

	if (!isset ($_SESSION['user']))
	{
		header ("Location: login.php");
		return;
	}

	if (!isset ($_GET['q']))
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
	$q = $_GET['q'];

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
					echo "<center>Không đặt tên <b>Môn</b> mới là '$newsubject'.</center>";
					throw $e;
				}
			}

			$questionID = $db->updateQuestion ($q, $question, $subject,
					isset ($_POST['shuffleable']) ? 'true' : 'false', 0.5);

			foreach ($choice as $c)
				$db->insertChoice ($questionID, $c[0], $c[1], $c[2]);

			$db->commit();
			//echo "<center>Chỉnh sửa câu hỏi thành công!</center>";
			header ("Location: question_edit.php?q=$questionID");
			return;
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
	else
	{
		$result = $db->query ("select * from oes_Question where ID = $q");
		$question_assoc = fetch_assoc ($result);
		mysql_free_result ($result);

		$question = $question_assoc[0]['Text'];
		$subject = $question_assoc[0]['Subject'];
		$shuffleable = $question_assoc[0]['Shuffleable'];

		$result = $db->query ("select * from oes_Choice where Question = $q");
		$choice = fetch_assoc ($result);
		mysql_free_result ($result);
	}
?>

<BODY>
<div align=center>
	<h1>Sửa câu hỏi</h1>

	<form action=# method=POST>
		<table>
			<tr><td align=center><label for=subject>Môn</label>
					<?php
						echo $db->getValue("select Name from oes_Subject where ID = $subject");
						echo "<input type=hidden name=subject value=$subject>";
					?>

			<tr><td>
				<table>
					<tr><td>Câu hỏi
					<tr><td><textarea cols=60 rows=6 id=question name=question><?php
								echo $question;
							?></textarea>
					<tr><td>Lựa chọn <label><input type=checkbox name=shuffleable <?php
								if ($shuffleable) echo 'checked';
							?>>Cho phép đảo chỗ</label>
					<tr><td>
						<table>
							<?php
								for ($i = 0; $i < 6; ++$i)
								{
									echo "<tr><td><input size=34 name=choice$i value='";
									if (isset($choice[$i])) echo $choice[$i]['Text'];
									echo"'>";
									echo "<label><input type=checkbox name=correct$i ";
									if (isset($choice[$i]) && $choice[$i]['Correct']) echo 'checked';
									echo ">Đúng</label>";
									echo "<label><input type=checkbox name=exclusive$i ";
									if (isset($choice[$i]) && $choice[$i]['Exclusive']) echo 'checked';
									echo ">Duy nhất</label>";
								}
							?>
						</table>
				</table>

			<tr align=center>
				<td colspan=2>
					<input type=reset value=Huỷ>
					<input type=submit name=submit value='Lưu'>
		</table>
	</form>

</div>
</BODY>
</HTML>