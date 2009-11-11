<?php
function mb_ucfirst ($string, $encode = 'utf-8')
{
	return mb_convert_case (mb_substr ($string, 0, 1, $encode), MB_CASE_TITLE, $encode)
		. mb_substr ($string, 1, mb_strlen ($string), $encode);
}
?>