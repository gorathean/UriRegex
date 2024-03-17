<?php
declare(strict_types=1);
use Gorath\UriRegex\PathLexer;

require './vendor/autoload.php';

$path = '/path/~/{which(\w+)}?/';
$lexer = (new PathLexer)->lexer([
  $path,
  //'/vendor/{lib}'
]);

print_r($lexer);