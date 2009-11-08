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
		function onChoose (ord, answer)
		{
			loadModule ('main', 'question_modules.php?id=main&ord=' + ord + '&ans=' + answer);
			loadModule ('list', 'question_modules.php?id=list&ord=' + ord);
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
	</script>
</head>

<body style="margin:0; padding:0">
<table align=center width=780 cellspacing=0 cellpadding=0>
	<td valign=top>
		<script>insertModule ('main', 'question_modules.php?id=main')</script>
	<td valign=top width=100>
		<table cellspacing=0 cellpadding=0>
			<tr><td><script>insertModule ('list', 'question_modules.php?id=list')</script>
			<tr><td align=center>
				<a class=button href='javascript:parent.history.go(-2)'>Quay lại</a>
				<a class=button href='javascript:onNext()'>Tiếp theo</a>
				<a class=button href='javascript:onSkip()'>Câu hỏi chưa chọn</a>
		</table>
</table>
</body>
</html>