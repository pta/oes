<?php
include_once "../config.php";
?>
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
	<link href="../ptajax/module.css" rel="stylesheet" type="text/css">
	<link href="exam.css" rel="stylesheet" type="text/css">

	<script>
		var statInterval;
		var exam;

		function onAutoStat()
		{
			loadModule ('stat', 'exam_modules.php?id=stat&exam=' + exam);
		}

		function setAutoStat (ex)
		{
			exam = ex;
			clearAutoStat();
			statInterval = setInterval ('onAutoStat()',
					<?php echo STAT_RELAD_INTERVAL?>);
			onAutoStat();
		}

		function clearAutoStat()
		{
			if (statInterval)
				clearInterval (statInterval);
		}
	</script>
</head>
<body>
	<script>insertModule ('list', 'exam_modules.php?id=list');</script>
	<script>insertModule ('detail', 'about:blank');</script>
	<script>insertModule ('stat', 'about:blank');</script>
</body>