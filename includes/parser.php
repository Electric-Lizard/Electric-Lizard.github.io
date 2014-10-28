<?php
require_once "tokenizer.php";
require_once "nodes.php";
class Parser {
	public function __construct() {
		$this->validTags = array("b", "i", "u", "s", "left", "center", "right", "font", "size", "color", "url", "img", "spoiler");
		$this->tagsWithAttributes = array("font", "size", "color");
		$this->tagsWithoutAttributes = array("b", "i", "u", "s", "left", "center", "right");
		$this->tokenizer = new Tokenizer;
	}
	public function getParsedThree($rawText) {
		$this->tokenizer->tokenize($rawText);
		$three = new Root;
		$three->content = $this->parse();
		return $three;
	}
	protected function parse($parents = []) {
		$parsedNodes = []; // root children
		while ($token = $this->tokenizer->getNextToken()) {
			switch ($this->tokenizer->getCurrentTokenType()) {
				case Tokenizer::TOKEN_OPEN_TAG:
				$tag = $this->parseTag($token, $parents);
				$tag? $parsedNodes[] = $tag: $parsedNodes = $this->parsePlainText($parsedNodes, $token);
				break;
				case Tokenizer::TOKEN_CLOSE_TAG:
				$tagName = strtolower(substr($token, 2));
				if ($this->tokenizer->getNextTokenType() == Tokenizer::TOKEN_CLOSE_BRACKET && in_array($tagName, $parents)) {
					if (end($parents) == $tagName) {
						unset($parents[key($parents)]);
						$this->tokenizer->getNextToken(); //skip "]"
						return $parsedNodes;
					} else {
						$this->tokenizer->position -= 1;
						unset($parents[key($parents)]);
						return $parsedNodes;
					}
				} else $parsedNodes = $this->parsePlainText($parsedNodes, $token);
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
	protected function parseTag($openTag, $parents) {
		$savedPosition = $this->tokenizer->position;
		$tagName = strtolower(substr($openTag, 1));
		$attrValue = null;
		$parents[] = $tagName;
		if (!in_array($tagName, $this->validTags)) return false;
		if (in_array($tagName, $this->tagsWithAttributes) &&
			$this->tokenizer->getNextTokenType() != Tokenizer::TOKEN_ATTR_VALUE) return false;
		if (in_array($tagName, $this->tagsWithoutAttributes) &&
			$this->tokenizer->getNextTokenType() != Tokenizer::TOKEN_CLOSE_BRACKET) return false;
		if ($this->tokenizer->getNextTokenType() == Tokenizer::TOKEN_ATTR_VALUE) $attrValue = strtolower(substr($this->tokenizer->getNextToken(), 1));
		if ($this->tokenizer->getNextTokenType() == Tokenizer::TOKEN_CLOSE_BRACKET) {
			$this->tokenizer->getNextToken(); //skip "]"
		} else {
			$this->tokenizer->position = $savedPosition;
			return false;
		}

		$tagContent = $this->parse($parents);
		return $this->getPairTagNode($tagName, $tagContent, $attrValue);
	}
	protected function getPairTagNode($tagName, $tagContent, $attrValue = null) {
		$node = new PairTag;
		$node->tagName = $tagName;
		if (isset($attrValue)) $node->attrValue = $attrValue;
		$position = $this->tokenizer->position;
		$tokens = $this->tokenizer->tokens;
		$node->content = $tagContent;
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