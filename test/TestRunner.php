#!/usr/bin/php
<?php

require_once(__DIR__ . '/autoload.php');

$md = "";

while (!\feof(STDIN)) {
  $md .= \fread(STDIN, 4096);
}

$parser = new \nexxes\stmd\Parser();
echo $parser->parseString($md);
echo PHP_EOL;
