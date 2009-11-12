<?php
	session_start();
	if (!isset ($_SESSION['user']))
	{
		$_SESSION['page'] = 'exam.php';
		header ("Location: login.php");
		return;
	}
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<script type="text/javascript" src="../ptajax/module.js"></script>
</head>
<body>
	<script>insertModule ('list', 'exam_modules.php?id=list');</script>
	<script>insertModule ('detail', 'about:blank');</script>
	<script>insertModule ('stat', 'about:blank');</script>
</body>