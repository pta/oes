<?php
include_once "../config.php";
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>OES Admin</title>

	<link href="index.css" rel="stylesheet" type="text/css">
	<link href="../ptajax/module.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../ptajax/module.js"></script>

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
<div id=all>
	<div id=control>
		<a href=# onClick="loadModule('main','exam.php')">Các buổi thi</a>
		<a href=# onClick="loadModule('main','exam_new.php')">Tạo buổi thi mới</a>
		<br>
		<a href=# onClick="loadModule('main','sample_db.php')">Sinh dữ liệu mẫu</a>
		<a href=# onClick="loadModule('main','question_new.php')">Thêm câu hỏi</a>
		<br>
		<a href=# onClick="loadModule('main','login.php')">Thoát</a>
	</div>
	<script>insertModule ('main', 'exam.php')</script>
</div>
</body>
</html>
