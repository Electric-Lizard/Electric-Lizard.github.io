<?php
session_start();
require_once "includes/parser.php";
$parser = new Parser;
class ParseHandler {
	public function getRow($three) {
		$row = '';
		while ($node = $three->getNode()) {
			$row .= $this->getHtmlFromNode($node);
		}
		return $row;
	}
	protected function getHtmlFromNode($node) {
		switch ($node->type) {
			case Node::NODE_PLAIN_TEXT:
			$html = $node->content;
			break;
			case Node::NODE_TAG:
			$html = $this->getHtmlFromTag($node);
			break;
			case Node::NODE_SMILE:
			if (isset($node->title)) {
				$html = "<img src=\"{$node->src}\" alt=\"{$node->code}\" title=\"{$node->title}\">";
			} else $html = "<img src=\"{$node->src}\" alt=\"{$node->code}\">";
			break;
			case Node::NODE_URL:
			$html = $this->getHtmlFromUrl($node);
			break;
		}
		return $html;
	}
	protected function getHtmlFromUrl($node) {
		switch ($node->urlType) {
			case 'url':
			$html = "<a href=\"{$node->href}\">{$node->href}</a>";
			break;
			case 'YouTube':
			$html = "<iframe width=\"560\" height=\"315\" src=\"//www.youtube.com/embed/{$node->id}\" frameborder=\"0\" allowfullscreen></iframe>";
			break;
		}
		return $html;
	}
	protected function gethtmlFromTag($node) {
		$content = "";
		foreach ($node->content as $child) {
			$content .= $this->gethtmlFromNode($child);
		}
		switch ($node->tagName) {
			case 'b':
			case 'i':
			case 'u':
			case 's':
			$html = "<{$node->tagName}>$content</{$node->tagName}>";
			break;
			case 'left':
			case 'center':
			case 'right':
			$html = "<div style=\"text-align:{$node->tagName};\">$content</div>";
			break;
			case 'font':
			$html = "<span style=\"font-family:{$node->attrValue};\">$content</span>";
			case 'size':
			if (preg_match('/(\\d{1-2})pt/', $node->attrValue, $matches) && $matches[1] <= 72) {
				$html = "<span style=\"font-size:{$node->attrValue};\">$content</span>";
			} //else $html = "[size={$node->attrValue}]$content[/size]";
			break;
			case 'color':
			$html = "<span style=\"color:{$node->attrValue};\">$content</span>";
			break;
			case 'url':
			if (isset($node->attrValue)) {
				if (!preg_match('/^https?:\/\//', $node->attrValue)) {
					$url = "http://".$node->attrValue;
				} else $url = $node->attrValue;
			} else {
				if (!preg_match('/^https?:\/\//', $node->attrValue)) {
					$url = "http://".$content;
				} else $url = $content;
			}
			$html = "<a href=\"$url\">{$content}<a>";
			break;
			case 'img':
			$html = "<img src=\"{$content}\">";
			break;
			case 'spoiler':
			if (isset($node->attrValue)) {
				$spoilerName = $node->attrValue;
			} else $spoilerName = "Spoiler";
			$html = "<div class=\"spoiler\"><span class=\"spoiler-head\">$spoilerName</span><div class=\"spoiler-content\">{$content}</div></div>";
			break;
		}
		return $html;
	}
}
if (!empty($_POST["someRow"])) {
	$row = $_POST["someRow"];
	$three = $parser->getParsedThree($row);
	$handler = new ParseHandler;
	$row = $handler->getRow($three);
}
?>
<form action="" method="POST">
	<textarea name="someRow"></textarea><br>
	<input type="submit">
</form>
<?php if (!empty($three)): ?>
	<p><pre><?php var_dump($three); echo "\n\n\n$row"; ?></p>
<?php endif; ?>