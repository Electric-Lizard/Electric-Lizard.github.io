<?php
require_once "tokenizer.php";
require_once "nodes.php";
class Parser {
	public function __construct() {
		$this->validTags = array("b", "i", "u", "s", "left", "center", "right", "font", "size", "color", "url", "img", "spoiler");
		$this->tagsWithAttributes = array("font", "size", "color");
		$this->tagsWithoutAttributes = array("b", "i", "u", "s", "left", "center", "right");
	}
	public function getParsedThree($rawText) {
		$three = new Root;
		$three->content = $this->parse($rawText);
		return $three;
	}
	protected function parse($rawText, $isTokenized = false) {
		$this->tokenizer = new Tokenizer;
		$parsedNodes = []; // root children
		$isTokenized? $this->tokenizer->tokens = $rawText: $this->tokenizer->tokenize($rawText);
		while ($token = $this->tokenizer->getNextToken()) {
			switch ($this->tokenizer->getCurrentTokenType()) {
				case Tokenizer::TOKEN_OPEN_TAG:
				$tag = $this->parseTag($token);
				$tag? $parsedNodes[] = $tag: $parsedNodes = $this->parsePlainText($parsedNodes, $token);
				break;
				case Tokenizer::TOKEN_SMILE:
				$smile = $this->parseSmile($token);
				$smile? $parsedNodes[] = $smile: $parsedNodes = $this->parsePlainText($parsedNodes, $token);
				break;
				case Tokenizer::TOKEN_URL:
				$parsedNodes[] = $this->parseUrl($token);
				break;
				default: $parsedNodes = $this->parsePlainText($parsedNodes, $token);
				break;
			}
		}
		return $parsedNodes;
	}
	protected function parseTag($openTag) {
		$savedPosition = $this->tokenizer->position;
		$tagName = substr($openTag, 1);
		if (!in_array(strtolower($tagName), $this->validTags)) return false;
		if ($this->tokenizer->getNextTokenType() == Tokenizer::TOKEN_ATTR_VALUE) $attrValue = substr($this->tokenizer->getNextToken(), 1);
		if ($this->tokenizer->getNextTokenType() == Tokenizer::TOKEN_CLOSE_BRACKET) {
			$this->tokenizer->getNextToken(); //skip "]"
			while ($token = $this->tokenizer->getNextToken()) {
				if ($this->tokenizer->getcurrentTokenType() == Tokenizer::TOKEN_CLOSE_TAG &&
					$this->tokenizer->getnextTokenType() == Tokenizer::TOKEN_CLOSE_BRACKET &&
					substr($token, 2) == $tagName) {
					$this->tokenizer->getNextToken(); //skip "]"
					if (isset($attrValue)) {
						return $this->getPairTagNode($tagName, $tagContent, $attrValue);
					} else return $this->getPairTagNode($tagName, $tagContent);
				} else {
					$tagContent[] = $token;
				}
			}
			$this->tokenizer->position = $savedPosition;
			return false;
		} else {
			$this->tokenizer->position = $savedPosition;
			return false;
		}
	}
	protected function getPairTagNode($tagName, $tagContent, $attrValue = null) {
		$node = new PairTag;
		$node->nodeKind = Node::EXTERNAL_NODE;
		$node->tagName = $tagName;
		if (isset($attrValue)) $node->attrValue = $attrValue;
		$position = $this->tokenizer->position;
		$tokens = $this->tokenizer->tokens;
		$node->content = $this->parse($tagContent, true);
		$this->tokenizer->position = $position;
		$this->tokenizer->tokens = $tokens;
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
	protected function parseSmile($token) {
		require "smiles.php";
		$this->mainSmiles = $mainSmiles;
		foreach ($mainSmiles as $smile) {
			if ($smile["code"] == $token) {
				$node = new Smile;
				$node->code = $smile["code"];
				$node->src = $smile["src"];
				if (isset($smile["title"])) $node->title = $smile["title"];
				return $node;
			}
		}
		return false;
	}
	protected function parseUrl($token) {
		if (preg_match('%^(?<![\'"=])https?://www.youtube.com/watch?.*v=([^\\s\\[\\]\\(\\)\\<\\>]+)$%', $token, $matches)) {
			$urlType = "YouTube";
			$id = $matches[1];
		} elseif (preg_match('%^(?<![\'"=])https?://youtu.be/([^\\s\\[\\]\\(\\)\\<\\>]+)$%', $token, $matches)) {
			$urlType = "YouTube";
			$id = $matches[1];
		} else {
			$urlType = "url";
		}
		$node = new Url;
		$node->href = $token;
		$node->urlType = $urlType;
		if (isset($id)) $node->id = preg_replace('/&/', '?', $id, 1);
		return $node;
	}
}