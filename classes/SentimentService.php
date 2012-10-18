<?php

/**
 * Sentiment service for Thinklog (c) Carlos Torchia (GPL2)
 */

define("SENTIMENT_FILE", DOC_ROOT . "/etc/SentiWordNet_3.0.0_20120510.txt");

class SentimentService
{
	protected $sentiments;

	// Construct this service
	function __construct($service)
	{
	}

	// Read the sentiment values for each word from the file
	function read()
	{
		// First collect the sum of positive and negative sentiments
		// for each word (different instances thereof due to different
		// contexts in which words can arise.
		$file = fopen(SENTIMENT_FILE, "r");
		$wordSums = array();
		$wordN = array();
		while (!feof($file) && ($line = fgets($file))) {
			if (substr($line, 0, 1) == "#") {
				continue;
			}
			$row = explode("\t", $line);

			// Get the words
			$words = preg_split("/[^A-Za-z]+/", $row[4]);

			// Get the sentiment
			$posScore = $row[2];
			$negScore = $row[3];
			$sentiment = $posScore - $negScore;

			// Add these words to our collection of word sums
			foreach ($words as $word) {
				if (isset($wordSums[$word])) {
					$wordSums[$word] += $sentiment;
					$wordN[$word]++;
				} else {
					$wordSums[$word] = $sentiment;
					$wordN[$word] = 1;
				}
			}
		}

		// Now compute the average sentiment for each word
		$this->sentiments = array();
		foreach ($wordSums as $word => $sum) {
			$n = $wordN[$word];
			$this->sentiments[$word] = $sum / $n;
		}
	}

	// Insert extracted sentiments into database
	function insert()
	{
		foreach ($this->sentiments as $word => $value) {
			$query = "INSERT INTO sentiment VALUES ('$word', $value)";
			if (!mysql_query($query)) {
				echo "Could not insert sentiment ('$word', $value): ";
				echo mysql_error() . "\n";
			}
		}
	}

	// Determines the "sentiment" from the given keyword
	function getSentiment($word)
	{
		// See if sentiment is cached in this instance
		if (!isset($this->sentiment)) {
			$this->sentiment = array();
		} else if (isset($this->sentiment[$word])) {
			return $this->sentiment[$word];
		}

		// Look up sentiment in DB
		$query = "SELECT value FROM sentiment WHERE word = '$word'";
		$result = mysql_query($query);
		if (!$result || !($row = mysql_fetch_array($result))) {
			return 0;
		}

		$this->sentiment[$word] = $row["value"];
		return $row["value"];
	}

	// Determines the average sentiment for the given list of keywords
	// @param $keywords List of keyword tuples
	//                  e.g. array(array("keyword" => "broccoli", "cnt" => 5), ...)
	function getAverageSentiment($keywords)
	{
		$sum = 0;
		$n = 0;
		foreach ($keywords as $row) {
			// More frequent terms have more weight
			$sum += $this->getSentiment($row["keyword"]) * $row["cnt"];
			$n += $row["cnt"];
		}

		return $sum / $n;
	}
}
