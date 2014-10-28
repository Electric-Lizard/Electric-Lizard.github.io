<?php
class Root {
	public function __construct() {
		$this->content = [];
		$this->position = 0;
	}
	public function getNode() {
		if ($this->position < count($this->content)) {
			return $this->content[$this->position++];
		} else return false;
	}
}
abstract class Node {
	abstract public function __construct();
	const NODE_PLAIN_TEXT = 100;
	const NODE_TAG = 101;
	const NODE_SMILE = 102;
	const NODE_URL = 103;
	//public $this->type;
	//public $this->nodeKind;
	//public $this->children = [];
}
class PairTag extends Node {
	public function __construct() {
		$this->type = self::NODE_TAG;
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
class Smile extends Node {
	public function __construct() {
		$this->type = self::NODE_SMILE;
		//$this->code;
		//$this->src;
		//$this->title;
	}
}
class Url extends Node {
	public function __construct() {
		$this->type = self::NODE_URL;
		//$this->href;
		//$this->urlType;
		//$this->id;
	}
}