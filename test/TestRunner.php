#!/usr/bin/php
<?php

require_once(__DIR__ . '/../vendor/autoload.php');

$md = "";

while (!\feof(STDIN)) {
  $md .= \fread(STDIN, 4096);
}

$parser = new \nexxes\stmd\Parser();
$struct = $parser->parseString($md);

foreach ($struct AS $elem) {
	echo $elem;
}
