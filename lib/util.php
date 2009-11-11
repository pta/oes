<?php
function mb_ucfirst ($string, $encode = 'utf-8')
{
	return mb_convert_case (mb_substr ($string, 0, 1, $encode), MB_CASE_TITLE, $encode)
		. mb_substr ($string, 1, mb_strlen ($string), $encode);
}

define ("SECOND", 1);
define ("MINUTE", 60 * SECOND);
define ("HOUR", 60 * MINUTE);
define ("DAY", 24 * HOUR);
define ("MONTH", 30 * DAY);

function relative_time ($time)
{
	if (!$time)
		return "chưa bao giờ";

	$delta = time() - $time;

	if ($delta < 0)
	{
		$delta = -$delta;
		$relative = 'nữa';
	}
	else if ($delta > 0)
		$relative = 'trước';
	else
		return 'bây giờ';

	if ($delta < 1 * MINUTE)
	{
		return ($delta == 1 ? "một" : $delta) . " giây " . $relative;
	}
	if ($delta < 2 * MINUTE)
	{
		return "một phút " . $relative;
	}
	if ($delta < 45 * MINUTE)
	{
		return floor ($delta/MINUTE) . " phút " . $relative;
	}
	if ($delta < 90 * MINUTE)
	{
		return "một giờ " . $relative;
	}
	if ($delta < 24 * HOUR)
	{
		return floor ($delta/HOUR) . " giờ " . $relative;
	}
	if ($delta < 48 * HOUR)
	{
		return ($relative == 'trước') ? "hôm qua" : "ngày mai";
	}
	if ($delta < 30 * DAY)
	{
		return floor ($delta/DAY) . " ngày " . $relative;
	}
	if ($delta < 12 * MONTH)
	{
		$months = floor ($delta/DAY/30);
		return ($months <= 1 ? "một" : $months) . " tháng " . $relative;
	}
	else
	{
		$years = floor($delta / DAY / 365);
		return ($years <= 1 ? "một" : $years) . " năm " . $relative;
	}
}
?>