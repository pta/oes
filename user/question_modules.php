<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	function first_unaswered ($arr_qoc, $start = 0)
	{
		$n = count ($arr_qoc);

		for ($i = $start; $i < $n; ++$i)
			if (!$arr_qoc[$i][2])
				return $arr_qoc[$i];

		for ($i = 0; $i < $start; ++$i)
			if (!$arr_qoc[$i][2])
				return $arr_qoc[$i];

		return $arr_qoc[$start];
	}

	function next_ord ($arr_qoc, $ord)
	{
		$n = count ($arr_qoc);
		++$ord;

		if ($ord < $n)
			return $ord;
		else
			return $arr_qoc[0][1];
	}

	function get_qoc ($db, $test)
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

	$arr_qoc = get_qoc ($db, $test);

	switch ($id)
	{
		case 'list':
		{
			if (isset ($_GET['ord']))
			{
				$ord = $_GET['ord'];

				if (isset ($_GET['next']))
					$ord = next_ord ($arr_qoc, $ord);
			}
			else
			{
				$qoc = first_unaswered ($arr_qoc, 0);
				$ord = $qoc[1];
			}

			foreach ($arr_qoc as $qoc)
			{
				$question = $qoc[0];
				$o = $qoc[1];

				if ($o == $ord)
					echo '<a class=question_current';
				else if ($qoc[2])
					echo '<a class=question_answered';
				else
					echo '<a';

				echo " href='javascript:onSelect($o)'>";
				printf ("Câu %02d", $o);
				echo "</a><br>";
			}

			break;
		}

		case 'main':
		{

			if (!isset ($_GET['ord']))
			{
				$qoc = first_unaswered ($arr_qoc, 0);
				$question = $qoc[0];
				$ord = $qoc[1];
			}
			else
			{
				$ord = $_GET['ord'];

				if (isset ($_GET['ans']))
				{
					$ans = $_GET['ans'];

					$db->insertTestAnswer ($test, $ans);

					if (isset ($_GET['next']))
						$ord = next_ord ($arr_qoc, $ord);
				}

				$question = $db->getValue ("select Question from Choice where ID = (select Choice from Test_Choice where Test = $test and Ord = $ord limit 1)");
			}

			printf ('<h3 align=center>Câu %02d</h3>', $ord);

			$qtext = $db->getValue ("select Text from Question where ID = $question");
			echo "<div id=question>$qtext</div>";

			$answer = $db->getValue ("select ID from (select Answer from Test_Answer where Test = $test) as A join (select ID from Choice where Question = $question) as B on Answer = ID;");

			$arr_it = get_choices ($db, $test, $ord);

			echo '<div id=choices align=center>';
			echo '<table width=100% cellspacing=0>';

			foreach ($arr_it as $i => $it)
			{
				$b = $i % 2;
				$id = $it['ID'];

				if ($id == $answer)
					echo "<tr class='choice choice$b chose'";
				else
					echo "<tr class='choice choice$b'";

				echo " onclick='onChoose ($ord, $id)'>";

				echo '<td align=center><span class=choiceli>';
				echo chr (ord ('A') + $i);
				echo '</span><td>';
				echo $it['Text'];
			}

			echo '</table>';
			echo '<input type=button value="Bỏ qua">';
			echo '</div>';

			break;
		}

		case 'right':
			echo $id;
			break;
	}

?>