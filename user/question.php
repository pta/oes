<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	session_start();

	if (!isset ($_SESSION['student']))
	{
		header ("Location: login.php");
	}

	if (!isset ($_SESSION['test']))
	{
		header ("Location: test.php");
	}

	$student = $_SESSION['student'];
	$test = $_SESSION['test'];
?>

<html>
<head>
	<title>OES - Question</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<link href="abutton.css" rel="stylesheet" type="text/css">
	<link href="question.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../js/ptajax.js"></script>

	<script type="text/javascript">

		var main = new Loader();
		var clock = new Loader();

		var TIME_OUT = false;
		var clockInterval;
		var ord;

		function actionAnswer (ord, choice)
		{
			if (!TIME_OUT)
			{
				main.load ('question_modules.php?action=answer&ord=' + ord + '&choice=' + choice);
			}
		}

		function actionSelect (ord)
		{
			this.ord = ord;
			main.load ('question_modules.php?action=select&ord=' + ord);
		}

		function actionSkip()
		{
			main.load ('question_modules.php?action=skip&ord=' + ord);
		}

		function actionNext()
		{
			main.load ('question_modules.php?action=next&ord=' + ord);
		}

		function onTimeOut()
		{
			clearInterval (clockInterval);
			TIME_OUT = true;
		}
	</script>
</head>

<body onload="main.load('question_modules.php?action=init')">
<div id=all>
	<script>main.insert('main')</script>

	<div id=control>
		<a class=button href='login.php'>Thoát</a>

		<script>
			clock.insert ('clock', '*');
			clock.load('question_clock.php');
			clockInterval = setInterval ("clock.load()", <?php echo A_MINUTE?>);
		</script>

		<script>main.insert('proc')</script>

		<a class=button href='javascript:actionNext()'>Tiếp theo</a>
		<a class=button href='javascript:actionSkip()'>Câu hỏi chưa chọn</a>

		<script>main.insert('list')</script>
	</div>
</div>
</body>
</html>