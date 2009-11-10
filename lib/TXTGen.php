<?php

function mb_ucfirst ($string, $encode = 'utf-8')
{
	return mb_convert_case (mb_substr ($string, 0, 1, $encode), MB_CASE_TITLE, $encode)
		. mb_substr ($string, 1, mb_strlen ($string), $encode);
}

class TXTGen
{
	static $MARKS = array ('.', '?', '!', '...');

	private $words;
	private $n = 0;

	function __construct ($inputFilePath)
	{
		$this->load ($inputFilePath);
	}

	function __destruct()
	{
		unset ($this->words);
		$n = 0;
	}

	function load ($inputFilePath)
	{
		$file = fopen ($inputFilePath, 'rb');

		try
		{
			$this->words = array();

			while (!feof ($file))
				$this->words[$this->n++] = trim (fgets ($file));

			fclose ($file);
		}
		catch (Exception $e)
		{
			fclose ($file);
			throw $e;
		}
	}

	function randWord()
	{
		return $this->words [mt_rand (0, $this->n - 1)];
	}

	function randPhrase()
	{
		$count = mt_rand (1, 6);

		$phrase = $this->randWord();

		for ($i = 1; $i < $count; ++$i)
			$phrase .= ' ' . $this->randWord();

		return $phrase;
	}

	function randMark()
	{
		return self::$MARKS [mt_rand (0, count (self::$MARKS)-1)];
	}

	function randSentence ($mark = null)
	{
		$count = mt_rand (1, 3);

		$sentence = mb_ucfirst ($this->randPhrase());

		for ($i = 1; $i < $count; ++$i)
			$sentence .= ', ' . $this->randPhrase();

		$sentence .= $mark?$mark:$this->randMark();

		return $sentence;
	}
}

/*
$txtGen = new TXTGen ("../lib/words.txt");
echo $txtGen->randSentence ();
*/

?>