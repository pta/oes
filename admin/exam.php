<?php
	session_start();
	if (!isset ($_SESSION['user']))
	{
		$_SESSION['page'] = 'exam.php';
		header ("Location: login.php");
		return;
	}
?>
<?php
include_once "../config.php";
include_once "../lib/Database.php";
?>
<?php
	header ('Content-Type: text/html; charset=UTF-8');

	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);
?>