<?php
require_once "tokenizer.php";
class Parser {
	public function __construct() {

	}
	public function getParsedThree($rawText) {
		$three = new Root;
		$three->children = $this->parse($rawText);
		return $three;
	}
	protected function parse($rawText, $isTokenized = false) {
		$this->tokenizer = new Tokenizer;
		$parsedNodes = []; // root children
		$isTokenized? $this->tokenizer->tokens = $rawText: $this->tokenizer->tokenize($rawText);
		while ($token = $this->tokenizer->getNextToken()) {
			echo $this->tokenizer->position . ". $token    =>" . $this->tokenizer->getCurrentTokenType() . "\n\n";
			switch ($this->tokenizer->getCurrentTokenType()) {
				case Tokenizer::TOKEN_OPEN_TAG: $tag = $this->parseTag($token);
				$tag? $parsedNodes[] = $tag: $parsedNodes = parsePlainText($parsedNodes, $token);
				break;
				default: $parsedNodes = $this->parsePlainText($parsedNodes, $token);
				break;
			}
			var_dump($parsedNodes); echo "\n\nPosition:" . $this->tokenizer->position . "\n\n";
		}
		return $parsedNodes;
	}
	protected function parseTag($openTag) {
		$savedPosition = $this->tokenizer->position;
		$tagName = substr($openTag, 1);
		if ($this->tokenizer->getNextTokenType() == Tokenizer::TOKEN_CLOSE_BRACKET) {
			$this->tokenizer->getNextToken(); //skip "]"
			while ($token = $this->tokenizer->getNextToken()) {
				if ($this->tokenizer->getcurrentTokenType() == Tokenizer::TOKEN_CLOSE_TAG &&
					$this->tokenizer->getnextTokenType() == Tokenizer::TOKEN_CLOSE_BRACKET &&
					substr($token, 2) == $tagName) {
					return $this->getPairTagNode($tagName, $tagContent);
				} else {
					$tagContent[] = $token;
				}
			} $this->tokenizer->position = $savedPosition;
			echo "\n FALSE \n";
			return false;
		} return false;
	}
	protected function getPairTagNode($tagName, $tagContent) {
		$node = new PairTag;
		$node->nodeKind = Node::EXTERNAL_NODE;
		$node->tagName = $tagName;
		$node->content = $this->parse($tagContent, true);
		return $node;
	}
	protected function parsePlainText($parsedNodes, $token) {
		if (isset(end($parsedNodes)->type) && end($parsedNodes)->type == Node::NODE_PLAIN_TEXT) {
			end($parsedNodes)->content = end($parsedNodes)->content . $token;
		} else {
			$node = new PlainText;
			$node->content = $token;
			$parsedNodes[] = $node;
		}
		return $parsedNodes;
	}
}
class Root {
	public function __construct() {
		$this->children = [];
	}
}
abstract class Node {
	abstract public function __construct();
	const INTERNAL_NODE = 1;
	const EXTERNAL_NODE = 2;
	const NODE_PLAIN_TEXT = 100;
	const NODE_SINGLE_TAG = 101;
	const NODE_PAIR_TAG = 102;
	//public $this->type;
	//public $this->nodeKind;
	//public $this->children = [];
}
class PairTag extends Node {
	public function __construct() {
		$this->type = self::NODE_PAIR_TAG;
		//$this->attributes;
		//$this->tagName;
		//$this->tagContent;
	}
}
class PlainText extends Node {
	public function __construct() {
		$this->type = self::NODE_PLAIN_TEXT;
		//$this->content;
	}
}
echo "<p><pre>";
$parser = new Parser;
$row = "Every [b]movements after sudden[/b] movements deja vu obtaction";
$three = $parser->getParsedThree($row);
echo "\n\n\n\n\n";
var_dump($three);