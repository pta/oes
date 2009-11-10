<?php
	session_start();
	if (!isset ($_SESSION['user']))
	{
		$_SESSION['page'] = 'sample_db.php';
		header ("Location: login.php");
		return;
	}
?>
<?php
include_once "../config.php";
include_once "../lib/Database.php";
include_once "../lib/TXTGen.php";
?>
<?php
	header ('Content-Type: text/html; charset=UTF-8');

	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	if (isset ($_POST['submit']))
	{
		$db->begin();
		try
		{
			$txtGen = new TXTGen();

			$subjects = $db->getColumn ('ID', 'Subject');

			foreach ($subjects as $subject)
			{
				for ($i = 0; $i < 100; ++$i)
				{
					$question = $txtGen->randParagraph (mt_rand (1, 6));
					$questionID = $db->insertQuestion ($question, $subject);

					$n = rand (4, 10);
					for ($j = 0; $j < $n; ++$j)
					{
						$choice = $txtGen->randSentence();
						$correct = (mt_rand (0, 3) == 0)?'true':'false';
						$db->insertChoice ($questionID, $choice, $correct);
					}
				}
			}

			$db->commit();
			echo "<center>Sinh dữ liệu mẫu thành công!</center>";
		}
		catch (Exception $e)
		{
			$db->rollback();

			?>
				<center>Thất bại!</center>
				<center><button onClick='history.back()'>Trở lại</button></center>
			<?php

			echo $e->getMessage();
			return -1;
		}
	}
?>

<HTML>
<HEAD>
	<title>OES Admin - Sample Database</title>
</HEAD>

<BODY>
<div align=center>
	<h1>Sinh dữ liệu mẫu?</h1>

	<form action=sample_db.php method=POST>
		<input type=submit name=submit value='Thực hiện'>
	</form>

</div>
</BODY>
</HTML>