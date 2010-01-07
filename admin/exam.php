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
	<script type="text/javascript" src="../js/ptajax.js"></script>
	<link href="exam.css" rel="stylesheet" type="text/css">

	<script>
		var loader = new Loader();

		var statInterval;
		var exam;

		function updateStat()
		{
			loader.load ('exam_modules.php?action=stat&exam=' + exam);
		}

		function setStatInterval (ex)
		{
			exam = ex;
			clearStatInterval();
			statInterval = setInterval ('updateStat()', <?php echo STAT_RELAD_INTERVAL?>);
		}

		function clearStatInterval()
		{
			if (statInterval)
				clearInterval (statInterval);
		}
	</script>
</head>
<body>
	<script>
		loader.insert ('list');
		loader.insert ('detail');
		loader.insert ('stat');
		loader.load ('exam_modules.php?action=init');
	</script>
</body>