<?php
declare(strict_types=1);

use Gorath\UriRegex\PathLexer;
use Gorath\UriRegex\PathComposer;

require './vendor/autoload.php';

$path = '/path/~/{which(\w+)}?/';
$list = (new PathComposer(PathLexer::class))->parse($path);

print_r($list);