<?php
	session_start();
	$_SESSION['page'] = 'sample_db.php';

	if (!isset ($_SESSION['user']))
	{
		header ("Location: login.php");
		return;
	}
?>
<?php
include_once "../config.php";
include_once "../lib/Database.php";
include_once "../lib/TXTGen.php";
?>

<HTML>
<HEAD>
	<title>OES Admin - Sample Database</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</HEAD>

<?php
	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	if (isset ($_POST['submit']))
	{
		$db->begin();
		try
		{
			/* Exam */
			$db->insertRandomExam ();

			/* Question and Choice */
			$txtGen = new TXTGen();

			$subjects = $db->getColumn ('ID', 'oes_Subject');

			foreach ($subjects as $subject)
			{
				for ($i = 0; $i < 100; ++$i)
				{
					$question = $txtGen->randParagraph (mt_rand (1, 6));

					$type = rand (0, 2);

					switch ($type)
					{
						case 0:	// single choice
							$questionID = $db->insertQuestion ($question, $subject, 'true');

							$n = mt_rand (3, 5);
							$c = mt_rand (0, $n - 1);

							for ($j = 0; $j < $n; ++$j)
							{
								$choice = $txtGen->randSentence();
								$db->insertChoice ($questionID, $choice,
										($j == $c) ? 'true' : 'false', 'true');
							}
							break;

						case 1:	// multiple choice with an exclusive "no right answer" option
							$questionID = $db->insertQuestion ($question, $subject, 'false');

							$n = mt_rand (3, 4);
							$nc = 'true';

							for ($j = 0; $j < $n; ++$j)
							{
								$choice = $txtGen->randSentence();

								if (mt_rand (0, 9) < 4)
								{
									$db->insertChoice ($questionID, $choice, 'true', 'false');
									$nc = 'false';
								}
								else
									$db->insertChoice ($questionID, $choice, 'false', 'false');
							}

							$choice = "Không có lựa chọn nào ở trên đúng.";
							$db->insertChoice ($questionID, $choice, $nc, 'true');
							break;

						case 2:	// mixed
							$questionID = $db->insertQuestion ($question, $subject, 'false');

							$n = mt_rand (2, 3);
							$nc = 'true';

							for ($j = 0; $j < $n; ++$j)
							{
								$choice = $txtGen->randSentence();

								if (mt_rand (0, 9) < 4)
								{
									$db->insertChoice ($questionID, $choice, 'true', 'false');
									$nc = 'false';
								}
								else
									$db->insertChoice ($questionID, $choice, 'false', 'false');
							}

							$n = mt_rand (1, 5 - $n);

							if ($nc == 'true')
								$c = mt_rand (0, $n - 1);
							else
								$c = -1;

							for ($j = 0; $j < $n; ++$j)
							{
								$choice = $txtGen->randSentence();
								$db->insertChoice ($questionID, $choice,
										($j == $c) ? 'true' : 'false', 'true');
							}
							break;
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

<BODY>
<div align=center>
	<h1>Sinh dữ liệu mẫu?</h1>

	<form action=sample_db.php method=POST>
		<input type=submit name=submit value='Thực hiện'>
	</form>

</div>
</BODY>
</HTML>