<?php
session_start();
require_once "includes/parser.php";
$parser = new Parser;
if (!empty($_POST["someRow"])) {
	$row = $_POST["someRow"];
	$three = $parser->parse($row);
}
?>
<form action="" method="POST">
	<textarea name="someRow"></textarea><br>
	<input type="submit">
</form>
<?php if (!empty($three)): ?>
	<p><pre><?php var_dump($three); ?></pre></p>
<?php endif; ?>