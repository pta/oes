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

	header ('Content-Type: text/html; charset=UTF-8');
?>

<html>
<head>
	<title>OES - Question</title>
	<link href="abutton.css" rel="stylesheet" type="text/css">
	<link href="question.css" rel="stylesheet" type="text/css">
	<link href="../ptajax/module.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../ptajax/module.js"></script>

	<script type="text/javascript">
		var TIME_OUT = false;
		var clockInterval;

		function onChoose (ord, answer)
		{
			if (!TIME_OUT)
			{
				loadModule ('main', 'question_modules.php?id=main&ord=' + ord + '&ans=' + answer);
				loadModule ('list', 'question_modules.php?id=list&ord=' + ord);
				loadModule ('proc', 'question_modules.php?id=proc');
			}
		}

		function onSelect (ord)
		{
			loadModule ('main', 'question_modules.php?id=main&ord=' + ord);
			loadModule ('list', 'question_modules.php?id=list&ord=' + ord);
		}

		function onSkip()
		{
			loadModule ('main', 'question_modules.php?id=main&skip');
			loadModule ('list', 'question_modules.php?id=list&skip');
		}

		function onNext()
		{
			loadModule ('main', 'question_modules.php?id=main&next');
			loadModule ('list', 'question_modules.php?id=list&next');
		}

		function onTimeOut()
		{
			clearInterval (clockInterval);
			TIME_OUT = true;
		}
	</script>
</head>

<body>
<div id=all>
	<script>insertModule ('main', 'question_modules.php?id=main')</script>

	<div id=control>
		<a class=button href='login.php'>Thoát</a>

		<script>
			insertModule ('clock', 'question_modules.php?id=clock')
			clockInterval = setInterval (
					"loadModule ('clock', 'question_modules.php?id=clock')",
					<?php echo $miliseconds_per_minute;?>);
		</script>

		<script>insertModule ('proc', 'question_modules.php?id=proc')</script>

		<a class=button href='javascript:onNext()'>Tiếp theo</a>
		<a class=button href='javascript:onSkip()'>Câu hỏi chưa chọn</a>

		<script>insertModule ('list', 'question_modules.php?id=list')</script>
	</div>
</div>
</body>
</html>