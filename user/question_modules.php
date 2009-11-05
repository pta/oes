<?php
include_once "../config.php";
include_once "../lib/Database.php";
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

	$result = $db->query ("select distinct Question from Choice where ID in (select Answer from Test_Answer where Test = $test)");
	$answered = fetch_column ($result);
	mysql_free_result ($result);

	if ($answered == null)
		$answered = array();

	$result = $db->query ("select Question, Ord from Choice join (select Choice, Ord from Test_Choice where Test = $test) as A on ID = Choice group by Question order by Ord");
	$questions = fetch_columns ($result);
	mysql_free_result ($result);

	switch ($id)
	{
		case 'left':
		{
			foreach ($questions as $q)
			{
				$question = $q[0];
				$ord = $q[1];

				echo "<a href=# onClick='javascript:loadModule (\"main\", \"question_modules.php?id=main&ord=$ord\");return false;'>";
				printf ("#%02d", $ord);
				echo "</a>";

				if (in_array ($question, $answered))
					echo '------';
				echo '<br>';
			}

			break;
		}

		case 'main':
		{
			if (!isset ($_GET['ord']))
			{
				// first unanswered question
				foreach ($questions as $q)
				{
					$question = $q[0];
					$ord = $q[1];

					if (!in_array ($question, $answered))
						break;
				}
			}
			else
			{
				$ord = $_GET['ord'];

				$question = $db->getValue ("select Question from Choice where ID = (select Choice from Test_Choice where Test = $test and Ord = $ord limit 1)");

				if (isset ($_GET['ans']))
				{
					//............................
					++$ord;
					header ("Location: question_modules.php?id=main&ord=$ord");
				}
			}

			printf ('<h3 align=center>Câu #%02d</h3>', $ord);

			$qtext = $db->getValue ("select Text from Question where ID = $question");
			echo "<div id=question>$qtext</div>";

			$result = $db->query ("select ID, Text from Choice where ID in (select Choice from Test_Choice where Test=$test and Ord = $ord) order by Text");
			$choices = fetch_columns ($result);
			mysql_free_result ($result);

			echo '<div id=choices" align=center>';
			echo '<table width=100%>';

			foreach ($choices as $i => $choice)
			{
				$b = $i % 2;
				$answer = $choice['ID'];

				echo "<tr class=choice$b>";
				echo "<td align=center>";
				echo chr (ord ('A') + $i);
				echo '<td>';
				echo $choice['Text'];
				echo '<td align=center>';
				echo "<input type=button value=Chọn onclick='loadModule (\"main\", \"question_modules.php?id=main&ord=$ord&ans=$answer\")'>";
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