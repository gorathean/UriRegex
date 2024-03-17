<?php
declare(strict_types=1);

namespace Gorath\UriRegex;
use Gorath\UriRegex\PathLexer;

class PathComposer {
  private PathLexer $lexer;
  
  public function __construct(PathLexer $lexer) {
    $this->lexer = new $lexer();
  }
  
  public function parse(string | array $path) {
    foreach ($this->lexer->lexer($path) as $tokens) {
      $instance = new PathComposer();
      $instance->compose($tokens);
    }
  }
  
  public function compose(array $tokens) {
    
  }
}