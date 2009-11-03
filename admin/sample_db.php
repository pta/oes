<?php
include_once "../config.php";
include_once "../lib/Database.php";
include_once "../lib/util.php";
?>

<?php
	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	if (isset ($_POST['submit']))
	{
		$db->begin();
		try
		{
			$subjects = $db->getColumn ('ID', 'Subject');

			foreach ($subjects as $subject)
			{
				for ($i = 0; $i < 100; ++$i)
				{
					$question = str_replace ("\n", '<br>', rand_str (rand (100, 200)));

					$questionID = $db->insertQuestion ($question, $subject);

					$n = rand (2, 10);
					for ($j = 0; $j < $n; ++$j)
					{
						$choice = str_replace ("\n", '<br>', rand_str (rand (10, 50)));
						$correct = (mt_rand (0, 3) == 0)?'true':'false';
						$db->insertChoice ($questionID, $choice, $correct);
					}
				}
			}

			$db->commit();
			echo "<center>Sinh ngẫu nhiên dữ liệu trắc nghiệm thành công!</center>";
		}
		catch (Exception $e)
		{
			$db->rollback();

			echo "<center>Thất bại!</center>";
			echo "<center><button onClick='history.back()'>Trở lại</button></center>";
			echo $e->getMessage();

			return -1;
		}
	}
?>

<HTML>
<HEAD>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>OES Admin - New Question</title>
</HEAD>
<BODY>
<div align=center>
	<h1>Sinh ngẫu nhiên dữ liệu trắc nghiệm?</h1>

	<form action=question_random.php method=POST>
		<input type=reset value=Huỷ>
		<input type=submit name=submit value='Thực hiện'>
	</form>

</div>
</BODY>
</HTML>