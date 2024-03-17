<?php
declare(strict_types=1);

use Gorath\UriRegex\PathLexer;
use Gorath\UriRegex\PathComposer;

require './vendor/autoload.php';

$config = [
  'default_pattern' => '[a-z_]',
  'allow_wildcards' => false,
  'strict'          => true,
  'allow_spaces'    => true
];

$path = '/path/~/{ which }?/(\d*)?';
$list = (new PathComposer(PathLexer::class, $config))
  ->parse($path);

print_r($list);