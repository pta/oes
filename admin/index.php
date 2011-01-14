<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>OES Admin</title>
	<link href="index.css" rel="stylesheet" type="text/css">
</head>

<body>
	<table id=all>
		<td><div id=control>
				<a target=main href=exam.php>Các buổi thi</a>
				<a target=main href=exam_new.php>Tạo buổi thi mới</a>
				<br>
				<a target=main href=question_new.php>Thêm câu hỏi</a>
				<a target=main href=question_search.php>Sửa câu hỏi</a>
				<br>
				<a target=main href=login.php>Thoát</a>
			</div>
		<td width=100%>
			<iframe frameborder=0 id=main name=main src=<?php
				session_start();
				if (isset ($_SESSION['page']))
					echo $_SESSION['page'];
				else
					echo 'exam.php';
			?>></iframe>
	</table>
</body>
</html>
