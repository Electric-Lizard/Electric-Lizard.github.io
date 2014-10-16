<?php
class Tokenizer {
	const TOKEN_WHITESPACE = 7;
	const TOKEN_OPEN_SINGLE_TAG = 1;
	const TOKEN_OPEN_PAIR_TAG = 2;
	const TOKEN_CLOSE_TAG = 3;
	const TOKEN_CLOSE_BRACKET = 4;
	const TOKEN_STRING = 99;
	public function __construct() {
		$this->delimiters = '/(\[\\w+|\[\/\\w+|\]|\\s)/s';
					//		  [tag | [/tag | ] | whitespases
		$this->validTags = array ("url", "b", "i", "u");
	}
	public function tokenize ($rawText) {
		$tokens = preg_split($this->delimiters, $rawText, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
		return $tokens;
	}
	public function getTokenType($token) {
		$types = array(
			self::TOKEN_WHITESPACE => '/^\\s+$/s',
			self::TOKEN_OPEN_PAIR_TAG => '/^\[(\\w+)$/',
			self::TOKEN_CLOSE_TAG => '/^\[\/(\\w+)$/',
			self::TOKEN_CLOSE_BRACKET => '/^\]$/'
		);
		foreach ($types as $type => $pattern) {
			if (preg_match($pattern, $token, $matches)) {
				$type = $this->validateType($type, $matches);
				return $type;
			}
		}
		return $type = self::TOKEN_STRING;
	}
	public function validateType($type, $matches) {

		if ($type == self::TOKEN_OPEN_PAIR_TAG || $type == self::TOKEN_CLOSE_TAG) {
			if (in_array(strtolower($matches[1]), $this->validTags)) {
				return $type;
			} else {
				return $type = self::TOKEN_STRING;
			}
		} else {
			return $type;
		}
	}
}