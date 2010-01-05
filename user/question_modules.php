<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	function get_noq ($db, $test)
	{
		if (isset ($_SESSION['NoQ']))
			return $_SESSION['NoQ'];
		else
			return $_SESSION['NoQ'] = count (get_arr_rowTQ ($db, $test));
	}

	function get_arr_rowTQ ($db, $test)
	{
		if (isset ($_SESSION['arr_rowTQ']))
			return $_SESSION['arr_rowTQ'];
		else
		{
			$result = $db->query ("select * from oes_TQ where Test = $test");
			$arr_rowTQ = fetch_columns ($result);
			mysql_free_result ($result);

			return $_SESSION['arr_rowTQ'] = $arr_rowTQ;
		}
	}

	function update_map_TQ_Answer ($db, $tq)
	{
		$value = $db->getValue ("select TQ from oes_Answer where TQ = $tq limit 1");

		if ($value)
			$_SESSION['map_TQ_Answer'][$tq] = true;
		else
			unset ($_SESSION['map_TQ_Answer'][$tq]);
	}

	function get_map_TQ_Answer ($db, $test)
	{
		if (isset ($_SESSION['map_TQ_Answer']))
			return $_SESSION['map_TQ_Answer'];
		else
		{
			$map_TQ_Answer = array();

			$result = $db->query ("select ID from (select * from oes_TQ where Test = $test) as sTQ join oes_Answer on ID = TQ");

			while ($row = mysql_fetch_array ($result))
				$map_TQ_Answer[$row[0]] = true;

			mysql_free_result ($result);

			return $_SESSION['map_TQ_Answer'] = $map_TQ_Answer;
		}
	}

	function get_tq_from_ord ($db, $test, $ord)
	{
		$arr_rowTQ = get_arr_rowTQ ($db, $test);
		return $arr_rowTQ[$ord]['ID'];
	}

	function get_qText ($db, $idQ)
	{
		if (!isset ($_SESSION['arr_qText']))
			$_SESSION['arr_qText'] = array();

		if (isset ($_SESSION['arr_qText'][$idQ]))
			return $_SESSION['arr_qText'][$idQ];
		else
			return $_SESSION['arr_qText'][$idQ] =
					$db->getValue ("select Text from oes_Question where ID = $idQ");
	}

	function get_arr_rowChoice ($db, $idQ)
	{
		if (!isset ($_SESSION['map_QC']))
			$_SESSION['map_QC'] = array();

		if (isset ($_SESSION['map_QC'][$idQ]))
			return $_SESSION['map_QC'][$idQ];
		else
		{
			$result = $db->query ("select * from oes_Choice where Question = $idQ");
			$map_QC = fetch_columns ($result);
			mysql_free_result ($result);

			return $_SESSION['map_QC'][$idQ] = $map_QC;
		}
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

	if (isset ($_GET['ord']))
		$ord = $_GET['ord'];
	else
		$ord = 0;

	$tq = get_tq_from_ord ($db, $test, $ord);

	switch ($action)
	{
		case 'answer':
			if (!isset ($_SESSION['TIME_OUT']))
			{
				$ended = $db->getValue ("select (EndTime is not null) from oes_Exam where ID = (select Exam from oes_Test where ID = $test)");

				if (!$ended)
				{
					if ($db->insertAnswer ($tq, $_GET['choice']))
					{
						update_map_TQ_Answer ($db, $tq);
						$update['list'] = $update['proc'] = true;
					}

					$update['main'] = true;
				}
				else
				{
					echo '<script>parent.onTimeOut()</script>';
					$_SESSION['TIME_OUT'] = true;
				}
			}
			break;

		case 'init':
			$update['list'] = $update['main'] = $update['proc'] = true;
			break;

		case 'select':
			$update['list'] = $update['main'] = true;
			break;

		case 'next':
			{
				$noq = get_noq ($db, $test);

				if (++$ord >= $noq)
					$ord = 0;

				$tq = get_tq_from_ord ($db, $test, $ord);

				$update['list'] = $update['main'] = true;
			}
			break;

		case 'skip':
			{
				$arr_rowTQ = get_arr_rowTQ ($db, $test);
				$map_TQ_Answer = get_map_TQ_Answer ($db, $test);
				$noq = get_noq ($db, $test);

				for ($i = $ord + 1; $i < $noq; ++$i)
				{
					if (!isset ($map_TQ_Answer[$arr_rowTQ[$i]['ID']]))
						break;
				}

				if ($i >= $noq)
				{
					for ($i = 0; $i <= $ord; ++$i)
					{
						if (!isset ($map_TQ_Answer[$arr_rowTQ[$i]['ID']]))
							break;
					}
				}

				$ord = $i;
				$tq = get_tq_from_ord ($db, $test, $ord);

				$update['list'] = $update['main'] = true;
			}
			break;
	}

	if (isset ($update['main']))
	{
		echo '<div id=main>';

		$arr_rowTQ = get_arr_rowTQ ($db, $test);
		$idQ = $arr_rowTQ[$ord]['Question'];

		printf ('<h3 align=center>Câu %02d</h3>', $ord + 1);

		$qtext = get_qText ($db, $idQ);
		echo "<div id=question>$qtext</div>";

		$arr_rowChoice = get_arr_rowChoice ($db, $idQ);

		$result = $db->query ("select Choice from oes_Answer where TQ = $tq");
		$arr_Answer = fetch_column ($result);
		mysql_free_result ($result);

		echo '<div id=choices>';

		foreach ($arr_rowChoice as $i => $rowChoice)
		{
			$idChoice = $rowChoice['ID'];

			$eo = ($i&1) ? 'odd' : 'even';

			if (in_array ($idChoice, $arr_Answer))
			{
				$img = $rowChoice['Exclusive'] ? 'radio' : 'check';
				$cls = 'chose';
			}
			else
			{
				$img = $rowChoice['Exclusive'] ? 'circle' : 'box';
				$cls = '';
			}

			$td_img = "<td><img src='../images/$img.png' height=30";

			if ($img != 'radio')
				$td_img	.= " onclick='actionAnswer ($ord, $idChoice)'";

			$td_img .= ">";

			echo "<div class='choice $eo $cls'><table>";
			echo $td_img . '<td width=100%>' . $rowChoice['Text'] . $td_img;
			echo '</table></div>';
		}

		echo '</div>';
		echo '</div>';
	}

	if (isset ($update['list']))
	{
		echo '<div id=list>';

		$arr_rowTQ = get_arr_rowTQ ($db, $test);
		$map_TQ_Answer = get_map_TQ_Answer ($db, $test);

		foreach ($arr_rowTQ as $o => $rowTQ)
		{
			$idTQ = $rowTQ['ID'];

			echo '<a class=\'question';
			if ($tq == $idTQ)					echo ' current';
			if (isset ($map_TQ_Answer[$idTQ]))	echo ' answered';
			echo '\'';

			echo " href='javascript:actionSelect($o)'>";
			printf ("Câu %02d", $o + 1);
			echo '</a>';
		}

		echo '</div>';
	}

	if (isset ($update['proc']))
	{
		echo "<div id=proc>";

		$done = count (get_map_TQ_Answer ($db, $test));
		$noq = get_noq ($db, $test);

		if ($done < $noq)
			echo "<div class=title>Thực hiện</div>";
		else
			echo "<div class=done>Hoàn thành</div>";

		printf ("<div class=factor>%02d/%02d</div>", $done, $noq);
		printf ("<div class=percent>%02d%%</div>", intval (100.0*$done/$noq));

		echo "</div>";
	}

	echo "<script>parent.ord = $ord</script>";
?>