<?php
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
	const NODE_SMILE = 103;
	const NODE_URL = 104;
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