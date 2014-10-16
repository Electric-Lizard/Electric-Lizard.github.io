<?php
require_once "tokenizer.php";
class Parser {
	const NODE_PLAIN_TEXT = 100;
	const NODE_SINGLE_TAG = 101;
	const NODE_PAIR_TAG = 102;
	public function __construct() {
		$this->tokenizer = new Tokenizer;
	}
	public function parse($rawText, $isTokenized = false) {
		$isTokenized? $tokens = $rawText: $tokens = $this->tokenizer->tokenize($rawText);
		$parsedText = [];
		foreach ($tokens as $key => $token) {
			if (isset($position) && $key < $position) continue;
			//switch is used for better flexibility with several tag types in future
			switch ($this->tokenizer->getTokenType($token)) {
				case Tokenizer::TOKEN_WHITESPACE:
				case Tokenizer::TOKEN_STRING:
				case Tokenizer::TOKEN_CLOSE_TAG:
				case Tokenizer::TOKEN_CLOSE_BRACKET:
				$parsedText = $this->consumePlainText($parsedText, $token);
				break;
				case Tokenizer::TOKEN_OPEN_PAIR_TAG:
				if ($this->tokenizer->getTokenType($tokens[$key+1]) == Tokenizer::TOKEN_CLOSE_BRACKET) {
					$tagName = substr($token, 1);
					$results = $this->searchCloseTag($tokens, $tagName, $key+2);
					if ($results !== false) {
						$position = $results[0];
						$tagContent = $results[1];
						$parsedText = $this->consumePairTag($parsedText, $tagName, $tagContent);
					} else $parsedText = $this->consumePlainText($parsedText, $token);
				} else $parsedText = $this->consumePlainText($parsedText, $token);
				break;
			}
		}
		return $parsedText;
	}
	public function searchOpenTag($tokens, $startPos = 0, $endPos = null) {
		foreach ($tokens as $key => $token) {
			if ($key < $startPos || (isset($endPos) && $key > $endPos)) continue;
			if ($this->tokenizer->getTokenType($token) == Tokenizer::TOKEN_OPEN_PAIR_TAG && $this->tokenizer->getTokenType($tokens[$key+1]) == TOKEN_CLOSE_BRACKET) {
				return array($key + 1, substr($token, 1));
			}
		}
		return false;
	}
	public function searchCloseTag($tokens, $tagName, $position) {
		foreach ($tokens as $key => $token) {
			if ($this->tokenizer->getTokenType($token) == Tokenizer::TOKEN_CLOSE_TAG &&
				strtolower(substr($token, 2)) == strtolower($tagName) &&
				$this->tokenizer->getTokenType($tokens[$key+1]) == Tokenizer::TOKEN_CLOSE_BRACKET) return array($key + 2, array_slice($tokens, $position, $key-$position));
		}
	return false;
	}
	public function consumePlainText($parsedText, $text) {
		if (end($parsedText)["type"] == self::NODE_PLAIN_TEXT) {
			$parsedText[key($parsedText)]["data"] = end($parsedText)["data"] . $text;
		} else {
			$parsedText[] = array("type" => self::NODE_PLAIN_TEXT, "data" => $text);
		}
		return $parsedText;
	}
	public function consumePairTag($parsedText, $tagName, $tagContent) {
		$parsedText[] = array("type" => self::NODE_PAIR_TAG, "tagName" => $tagName, "data" => $this->parse($tagContent, true));
		return $parsedText;
	}
}