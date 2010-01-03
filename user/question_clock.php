<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	session_start();

	if (! (isset ($_SESSION['student'])
			&& isset ($_SESSION['test'])))
	{
		echo "Invalid session";
		return;
	}

	$student = $_SESSION['student'];
	$test = $_SESSION['test'];

	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	$ts = $db->getValue ("select TimeSpent from oes_Test where ID = $test");

	if ($ts == null) $ts = 0;

	if (isset ($_SESSION['duration']))
		$duration = $_SESSION['duration'];
	else
	{
		$duration = $db->getValue ("select Duration from oes_Exam where ID = (select Exam from oes_Test where ID = $test)");
		$_SESSION['duration'] = $duration;
	}

	if ($ts < $duration)
	{
		echo "<div class=title>Thời gian</div>";
		$db->query ("update oes_Test set TimeSpent = " . ($ts + 1)
				. " where ID = $test");
	}
	else
	{
		echo '<script>parent.onTimeOut()</script>';
		echo "<div class=timeout>Hết giờ</div>";
		$_SESSION['TIME_OUT'] = true;
	}

	printf ("<div class=factor>%02d/%02d</div>", $ts, $duration);
	printf ("<div class=percent>%02d%%</div>", 100*$ts/$duration);
?>