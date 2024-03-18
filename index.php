<?php
declare(strict_types=1);

use Gorathean\UriRegex\PathLexer;
use Gorathean\UriRegex\PathComposer;
use Gorathean\UriRegex\PathRegex;

require './vendor/autoload.php';

class UriRegex {
  private PathRegex $regex;
  
  public function __construct
  (
    readonly string $pattern, 
    array $config = []
  ) {
    $this->regex = new PathRegex(new PathComposer(PathLexer::class, $config));
  }
  
  public function match(string $uri) {
    $exp = $this->regex->toRegex($this->pattern);
    $reg = preg_match($exp, $uri, $match);
    
    return [
      'match'  => $reg,
      'params' => array_filter($match ?? [], fn($value) => is_string($value), ARRAY_FILTER_USE_KEY),
      'path'   => $uri
    ];
  }
  
  public function getRegex() {
    return $this->regex->toRegex($this->pattern);
  }
}

