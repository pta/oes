<?php
include_once "../lib/util.php";

class TXTGen
{
	static $MARKS = array ('.', '?', '!', '...');

	private $words;
	private $n = 0;

	function __construct ($inputFilePath = '../lib/words.txt')
	{
		$this->load ($inputFilePath);
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

	function randParagraph ($nos = 13)
	{
		$paragraph = $this->randSentence();

		for ($i = 0; $i < $nos; ++$i)
			$paragraph .= ' ' . $this->randSentence();

		return $paragraph;
	}
}

/*
$txtGen = new TXTGen();
echo $txtGen->randSentence ();
*/

?>