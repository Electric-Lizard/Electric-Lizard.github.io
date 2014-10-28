<?php
class Tokenizer {
	const TOKEN_WHITESPACE = 1;
	const TOKEN_OPEN_TAG = 2;
	const TOKEN_CLOSE_TAG = 3;
	const TOKEN_CLOSE_BRACKET = 4;
	const TOKEN_ATTR_VALUE = 5;
	const TOKEN_SMILE = 6;
	const TOKEN_URL = 7;
	const TOKEN_STRING = 99;
	public function __construct() {
		$this->brokenSmiles = ':\\)|;\\)|:D|;D|>:\\(|:p|:\\(|:o|8\\)';
		$this->delimiters = '/(\[\\w+|\[\/\\w+|\]|=[^\]]+|(?<!\\w):\\w+:|'.$this->brokenSmiles.'(?!\\w)|(?<!\\w)https?:\/\/[^\\s\\[\\]\\(\\)\\<\\>]+|\\s)/su';
					//		  [tag | [/tag | ] |                     :smile:                     | url | whitespases
		$this->position = 0;
		$this->isFirstTime = true;
	}
	public function tokenize ($rawText) {
		$this->tokens = preg_split($this->delimiters, $rawText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}
	public function getTokenType($token) {
		$types = array(
			self::TOKEN_WHITESPACE => '/^\\s+$/s',
			self::TOKEN_OPEN_TAG => '/^\[(\\w+)$/',
			self::TOKEN_CLOSE_TAG => '/^\[\/(\\w+)$/',
			self::TOKEN_CLOSE_BRACKET => '/^\]$/',
			self::TOKEN_ATTR_VALUE => '/^=[^\]]+$/u',
			self::TOKEN_SMILE => '/^:\\w*:|'.$this->brokenSmiles.'$/',
			self::TOKEN_URL => '/^(?<!\\w)https?:\/\/[^\\s\\[\\]\\(\\)\\<\\>]+$/'
		);
		foreach ($types as $type => $pattern) {
			if (preg_match($pattern, $token, $matches)) {
				//$type = $this->validateType($type, $matches);
				return $type;
			}
		}
		return $type = self::TOKEN_STRING;
	}
	public function getNextToken() {
		$this->isFirstTime? $this->isFirstTime = false: $this->position++;
		if ($this->position >= count($this->tokens)) {
			return false;
		} else {
			return $this->tokens[$this->position];
		}
	}
	public function getCurrentToken() {
		return $this->tokens[$this->position];
	}
	public function getCurrentTokenType() {
		return $this->getTokenType($this->tokens[$this->position]);
	}
	public function getNextTokenType() {
		return $this->getTokenType($this->tokens[$this->position+1]);
	}
}
