<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	function get_first_unanswered_ord ($arr_qoc, $ord)
	{
		$n = count ($arr_qoc);

		for ($i = $ord; $i < $n; ++$i)
			if (!$arr_qoc[$i][2])
				return $i;

		for ($i = 0; $i < $ord; ++$i)
			if (!$arr_qoc[$i][2])
				return $i;

		return $arr_qoc[$ord];
	}

	function next_ord ($arr_qoc, $ord)
	{
		if (++$ord < count ($arr_qoc))
			return $ord;
		else
			return 0;
	}

	function get_qoc ($arr_qoc, $ord)
	{
		return $arr_qoc[$ord];
	}

	function get_arr_qoc ($db, $test)
	{
		$result = $db->query ("select Question, Ord from Choice join (select Choice, Ord from Test_Choice where Test = $test) as A on ID = Choice group by Question order by Ord");
		$arr_qoc = fetch_columns ($result);
		mysql_free_result ($result);

		$result = $db->query ("select distinct Question from Choice where ID in (select Answer from Test_Answer where Test = $test)");
		$answered = fetch_column ($result);
		mysql_free_result ($result);

		if ($answered != null)
		{
			foreach ($arr_qoc as $i => $q)
				$arr_qoc[$i][2] = in_array ($q[0], $answered);
		}
		else
		{
			foreach ($arr_qoc as $i => $q)
				$arr_qoc[$i][2] = false;
		}

		unset ($answered);

		return $arr_qoc;
	}

	function get_choices ($db, $test, $ord)
	{
		$result = $db->query ("select ID, Text from Choice where ID in (select Choice from Test_Choice where Test=$test and Ord = $ord) order by Text");
		$choices = fetch_columns ($result);
		mysql_free_result ($result);

		return $choices;
	}

	function init_ord()
	{
		if (isset ($_GET['ord']))
			return $_GET['ord'];
		else if (isset ($_SESSION['ord']))
			return $_SESSION['ord'];
		else
			return 0;
	}

	function jump_ord ($arr_qoc, $ord)
	{
		if (isset ($_GET['next']))
			return next_ord ($arr_qoc, $ord);
		else if (isset ($_GET['skip']))
			return get_first_unanswered_ord ($arr_qoc, next_ord ($arr_qoc, $ord));
		else
			return $ord;
	}

	function time_to_minutes ($time)
	{
		return $time->format('H') * 60 + $time->format('i');
	}
?>
<?php
	session_start();

	if (! (isset ($_SESSION['student'])
			&& isset ($_SESSION['test'])
			&& isset ($_GET['id'])))
	{
		echo "Invalid session";
		return;
	}

	$student = $_SESSION['student'];
	$test = $_SESSION['test'];
	$id = $_GET['id'];

	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);

	switch ($id)
	{
		case 'list':
		{
			$ord = init_ord();
			$arr_qoc = get_arr_qoc ($db, $test);
			$ord = jump_ord ($arr_qoc, $ord);
			$qoc = get_qoc ($arr_qoc, $ord);

			foreach ($arr_qoc as $qoc)
			{
				$question = $qoc[0];
				$o = $qoc[1];

				echo '<a class=\'question';
				if ($o == $ord)	echo ' current';
				if ($qoc[2])	echo ' answered';
				echo '\'';

				echo " href='javascript:onSelect($o)'>";
				printf ("Câu %02d", $o + 1);
				echo "</a><br>";
			}

			$_SESSION['ord'] = $ord;
			break;
		}

		case 'main':
		{
			$ord = init_ord();

			if (isset ($_GET['ans']) && !isset ($_SESSION['TIME_OUT']))
			{
				$db->insertTestAnswer ($test, $_GET['ans']);
			}

			$arr_qoc = get_arr_qoc ($db, $test);
			$ord = jump_ord ($arr_qoc, $ord);

			$question = $arr_qoc[$ord][0];

			printf ('<h3 align=center>Câu %02d</h3>', $ord + 1);

			$qtext = $db->getValue ("select Text from Question where ID = $question");
			echo "<div id=question>$qtext</div>";

			$answer = $db->getValue ("select ID from (select Answer from Test_Answer where Test = $test) as A join (select ID from Choice where Question = $question) as B on Answer = ID;");

			$arr_it = get_choices ($db, $test, $ord);

			echo '<div id=choices align=center>';
			echo '<table width=100% cellspacing=0 cellpadding=0>';

			foreach ($arr_it as $i => $it)
			{
				$b = $i % 2;
				$id = $it['ID'];

				echo "<tr class='choice choice$b";

				if ($id == $answer)
					echo ' chose';

				echo "' onclick='onChoose ($ord, $id)'>";

				echo '<td align=center><span class=choiceli>(';
				echo chr (ord ('A') + $i);
				echo ')</span><td>';
				echo $it['Text'];
			}

			echo '</table>';
			echo '</div>';
			break;
		}

		case 'clock':
		{
			$ts = $db->getValue ("select Time_Spent from Test where ID = $test");

			if ($ts == null) $ts = '00:00:00';
			$ts = new DateTime ($ts);
			$its = time_to_minutes ($ts);

			if (isset ($_SESSION['duration']))
				$duration = $_SESSION['duration'];
			else
			{
				$duration = $db->getValue ("select Duration from Exam where ID = (select Exam from Test where ID = $test)");
				$duration = new DateTime ($duration);
				$_SESSION['duration'] = $duration;
			}

			$iduration = time_to_minutes ($duration);

			if ($ts < $duration)
			{
				echo "<div class=title>Thời gian</div>";

				$ts->modify ("+1 minute");
				$db->query ("update Test set Time_Spent = '" . $ts->format ('H:i:s')
						. "' where ID = $test");
			}
			else
			{
				echo '<div class="script">onTimeOut();</div>';
				echo "<div class=timeout>Hết giờ</div>";
				$_SESSION['TIME_OUT'] = true;
			}

			printf ("<div class=duration>%02d/%02d</div>", $its, $iduration);
			printf ("<div class=percent>%02d%%</div>", intval (100.0*$its/$iduration));

			break;
		}
	}

?>