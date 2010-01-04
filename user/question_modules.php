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

		return $ord;
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
		$result = $db->query ("select Question, ID from oes_Choice join (select Choice, Ord from oes_Test_Choice where Test = $test) as A on ID = Choice group by Question order by Ord");
		$arr_qoc = fetch_columns ($result);
		mysql_free_result ($result);

		$result = $db->query ("select distinct Question from oes_Choice where ID in (select Answer from oes_Test_Answer where Test = $test)");
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
		$result = $db->query ("select ID, Text from oes_Choice where ID in (select Choice from oes_Test_Choice where Test=$test and Ord = $ord)");
		$choices = fetch_columns ($result);
		mysql_free_result ($result);

		return $choices;
	}

	function init_ord()
	{
		if (isset ($_GET['ord']))
			return $_GET['ord'];
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

	function get_arr_rowTQ ($db, $test)
	{
		static $arr_rowTQ = null;

		if ($arr_rowTQ == null)
		{
			$result = $db->query ("select * from oes_TQ where Test = $test");
			$arr_rowTQ = fetch_columns ($result);
			mysql_free_result ($result);
		}

		return $arr_rowTQ;
	}
?>
<?php
	session_start();

	if (! (isset ($_SESSION['student'])
			&& isset ($_SESSION['test'])
			&& isset ($_GET['action'])))
	{
		echo "Invalid session";
		return;
	}

	$student = $_SESSION['student'];
	$test = $_SESSION['test'];
	$action = $_GET['action'];

	$db = new Database (DB_HOST, DB_USER, DB_PASS);
	$db->selectDatabase (DB_NAME);

	$give['list'] = false;
	$give['main'] = false;
	$give['proc'] = false;

	$tq = null;

	if (isset ($_GET['tq']))
		$tq = $_GET['tq'];
	else
	{
		$arr_rowTQ = get_arr_rowTQ ($db, $test);
		$tq = $arr_rowTQ[0]['ID'];
	}

	switch ($action)
	{
		case 'answer':
			if (!isset ($_SESSION['TIME_OUT']))
			{
				$ended = $db->getValue ("select (EndTime is not null) from oes_Exam where ID = (select Exam from oes_Test where ID = $test)");

				if (!$ended)
				{
					$db->insertAnswer ($tq, $_GET['choice']);
					$give['list'] = $give['main'] = $give['proc'] = true;
				}
				else
				{
					echo '<script>parent.onTimeOut()</script>';
					$_SESSION['TIME_OUT'] = true;
				}
			}
			break;

		case 'init':
			$give['list'] = $give['main'] = $give['proc'] = true;
			break;

		case 'select':
			$give['list'] = $give['main'] = true;
			break;

		case 'next':
			$give['list'] = $give['main'] = true;
			break;

		case 'skip':
			$give['list'] = $give['main'] = true;
			break;
	}

	if ($give['list'])
	{
		$arr_rowTQ = get_arr_rowTQ ($db, $test);

		$arr_Answer = array();
		foreach ($arr_rowTQ as $rowTQ)
		{
			$idTQ = $rowTQ['ID'];

			$result = $db->query ("select Choice from oes_Answer where TQ = $idTQ");

			if (mysql_num_rows ($result) > 0)
				$arr_Answer[$idTQ] = fetch_column ($result);

			mysql_free_result ($result);
		}

		echo '<div id=list>';

		foreach ($arr_rowTQ as $o => $rowTQ)
		{
			$idTQ = $rowTQ['ID'];

			echo '<a class=\'question';
			if ($tq == $idTQ)				echo ' current';
			if (isset ($arr_Answer[$idTQ]))	echo ' answered';
			echo '\'';

			echo " href='javascript:actionSelect($idTQ)'>";
			printf ("Câu %02d", $o + 1);
			echo '</a>';
		}

		echo '</div>';
	}

	switch ($action)
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
				echo '</a>';
			}

			echo "<script>parent.ord = $ord;</script>";
			break;
		}

		case 'main':
		{
			$ord = init_ord();

			if (isset ($_GET['ans']) && !isset ($_SESSION['TIME_OUT']))
			{
				$ended = $db->getValue ("select (End_Time is not null) from oes_Exam
						where ID = (select Exam from oes_Test where ID = $test)");

				if (!$ended)
					$db->insertTestAnswer ($test, $_GET['ans']);
				else
					echo '<script>parent.onTimeOut()</script>';
			}

			$arr_qoc = get_arr_qoc ($db, $test);
			$ord = jump_ord ($arr_qoc, $ord);

			$question = $arr_qoc[$ord][0];

			printf ('<h3 align=center>Câu %02d</h3>', $ord + 1);

			$qtext = $db->getValue ("select Text from oes_Question where ID = $question");
			echo "<div id=question>$qtext</div>";

			$answer = $db->getValue ("select ID from (select Answer from oes_Test_Answer where Test = $test) as A join (select ID from oes_Choice where Question = $question) as B on Answer = ID;");

			$arr_it = get_choices ($db, $test, $ord);

			echo '<div id=choices>';

			foreach ($arr_it as $i => $it)
			{
				$action = $it['ID'];

				$eo = ($i&1)?'odd':'even';
				$val = ($action==$answer)?'check':'box';
				$cls = ($action==$answer)?'chose':'';

				$td_img = "<td><img src='../images/$val.png' height=30"
						. " onclick='onChoose ($ord, $action)'>";

				echo "<div class='choice $eo $cls'><table>";
				echo $td_img . '<td width=100%>' . $it['Text'] . $td_img;
				echo '</table></div>';
			}

			echo '</div>';
			break;
		}

		case 'proc':
		{
			$done = $db->getValue ("select count(TQ) from oes_Answer where TQ in (select ID from oes_TQ where $test)");

			if ($done == null) $done = 0;

			if (isset ($_SESSION['NoQ']))
				$noq = $_SESSION['NoQ'];
			else
			{
				$noq = $db->getValue ("select count(ID) from oes_TQ where Test = $test");
				$_SESSION['NoQ'] = $noq;
			}

			if ($done < $noq)
				echo "<div class=title>Thực hiện</div>";
			else
				echo "<div class=done>Hoàn thành</div>";

			printf ("<div class=factor>%02d/%02d</div>", $done, $noq);
			printf ("<div class=percent>%02d%%</div>", intval (100.0*$done/$noq));

			break;
		}
	}

?>