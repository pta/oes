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
	<link href="../ptajax/module.css" rel="stylesheet" type="text/css">
	<link href="question.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../ptajax/module.js"></script>
</head>

<body style="margin:0; padding:0">
<table align=center width=780 height=560 bgcolor=cyan>
	<td width=100>
		<script>insertModule ('left', 'question_modules.php?id=left')</script>
	<td valign=top>
		<script>insertModule ('main', 'question_modules.php?id=main')</script>
	<td width=100 bgcolor=#4DAADC>
		<script>insertModule ('right', 'question_modules.php?id=right')</script>
</table>
</body>
</html>