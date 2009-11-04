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
<?php
	header ('Content-Type: text/html; charset=UTF-8');

	$db = new Database ($db_server, $db_username, $db_password);
	$db->selectDatabase ($db_database);
?>