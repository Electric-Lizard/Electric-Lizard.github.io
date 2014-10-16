<?php
$row = "Have a [url=http:://buuee.com]sad[/url] cum bb";
$delimiters = '/(\[|\]|=[\'"]?[^\'"\]\[]*[\'"]?|\\s)/s';
$tokens = preg_split($delimiters, $row, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
//checkout
echo "<pre>";
echo var_dump($tokens);
echo "</pre>";

$parsed = [];
function getTokenType($value) {
	$types = array(
		"/^(\[)$/" => "OPEN_BRACKET",
		"/^(\])$/" => "CLOSE_BRACKET",
		"/^(=.*)$/" => "ATTR_VALUE",
		"/^(\/\\w+)$/" => "CLOSE_TAG",
		"/^(\\s+)$/s" => "WHITESPACE"
	);
	foreach ($types as $pattern => $type) {
		if (preg_match($pattern, $value)) {
			return $type;
		}
	}
	return $type = "STRING";
}

//checkout
foreach ($tokens as $key => $token) {
	$named[] = array (getTokenType($token) => $token);
}
echo "<pre>";
foreach ($named as $k => $v) {
	echo end($v) . " => " . key($v) . "\n";
}
echo "</pre>";

function consumePlainText($token) {
	global $parsed;
	if (end($parsed)["type"] == "PLAIN_TEXT") {
		end($parsed);
		$parsed[key($parsed)]["data"] = end($parsed)["data"] . $token;
	} else {
		$parsed[] = array("type" => "PLAIN_TEXT", "data" => $token);
	}
}
//main function
function searchOpenBracket($position = 0) {
	global $tokens;
	foreach ($tokens as $key => $token) {
		if ($key < $position) {
			continue;
		}
		if (getTokenType($token) == "OPEN_BRACKET") {
			searchTagName($key+1, $token);
		} else {
			consumePlainText($token);
		}
	}
}

function searchTagName($position, $previousTokens) {
	global $tokens;
	if (getTokenType($tokens[$position]) == "STRING") {
		searchAttrValue($position+1, $previousTokens . $tokens[$position], $tokens[$position]);
	} else {
		consumePlainText($previousTokens . $tokens[$position]);
		searchOpenBracket($position+1);
	}
}
function searchAttrValue($position, $previousTokens, $tagName) {
	global $tokens;
	if (getTokenType($tokens[$position]) == "ATTR_VALUE") {
		searchCloseBracket($position+1, $previousTokens . $tokens[$position], $tagName, $tokens[$position]);
	} elseif (getTokenType($tokens[$position]) == "CLOSE_BRACKET") {
		searchCloseTag($position+1, $previousTokens . $tokens[$position], $tagName);
	} else {
		consumePlainText($previousTokens . $tokens[$position]);
		searchOpenBracket($position+1);
	}
}
function searchCloseBracket($position, $previousTokens, $tagName, $attrValue) {
	global $tokens;
	if (getTokenType($tokens[$position]) == "CLOSE_BRACKET") {
		searchCloseTag($position+1, $previousTokens . $tokens[$position], $tagName, $attrValue);
	} else {
		consumePlainText($previousTokens . $tokens[$position]);
		searchOpenBracket($position+1);
	}
}
function searchCloseTag($position, $previousTokens, $tagName, $attrValue = "") {
	global $tokens;
	foreach ($tokens as $key => $token) {
		if ($key < $position) {
			continue;
		}
		if (getTokenType($token) == "OPEN_BRACKET" &&
		getTokenType($tokens[$key+1]) == "CLOSE_TAG" &&
		getTokenType($tokens[$key+2]) == "CLOSE_BRACKET") {
			$tagContent = [];
			for ($i = $position; $i < $key; $i++) {
				$tagContent[] = $tokens[$i];
			}
			$tagContent = implode("", $tagContent);
			consumeTag($tagName, $tagContent, $attrValue);
			searchOpenBracket($key+3);
			listParsed();
			die();
		}
	}
	consumePlainText($previousTokens);
	searchOpenBracket($position);
}
function consumeTag($tagName, $tagContent, $attrValue = "") {
	global $parsed;
	if (!empty($attrValue)) {
		$parsed[] = array("type" => "BB_TAG", "name" => $tagName, "attrValue" => substr($attrValue, 1), "data" => $tagContent);
	} else {
		$parsed[] = array("type" => "BB_TAG", "name" => $tagName, "data" => $tagContent);
	}
}

searchOpenBracket(); //search tags essensially
function listParsed() {
	global $parsed;
	echo "<pre>";
	var_dump($parsed);
	echo "</pre>";
}
listParsed();