<?php
declare(strict_types=1);

namespace Gorath\UriRegex;
use Gorath\UriRegex\PathLexer;

class PathComposer {
  private PathLexer $lexer;
  private int $length = 0;
  private int $idx = 0;
  
  public function __construct(PathLexer $lexer) {
    $this->lexer = new $lexer();
  }
  
  public function parse(string | array $path) {
    $tokens = $this->lexer->lexer($path);
    $this->length = count($tokens);
    
    while ($this->idx < $this->length) {
      break;
    }
  }
  
  private function should_take (string $name, bool $ignore_space = true): ?string {
    $idx = $this->idx;
    while ($idx < $this->len && $this->tokens[$idx]['name'] == 'space' && $ignore_space) {
      $idx ++;
    }
    
    if ($idx < $this->len && $this->tokens[$idx]['name'] == $name) {
      $this->idx = $idx;
      return $this->tokens[$this->idx ++]['value'];
    }
    
    $this->idx = $idx;
    return null;
  }
  
  private function must_take (string $name, bool $ignore_space = true): Error | string {
    $value = '';
    if ($this->idx < $this->len && is_null($value = $this->should_take($name, $ignore_space))) {
      $current = $this->tokens[$this->idx];
      throw new Exception('Unexcepted "' . $current['value'] . '" at index ' . $current['idx'] . '. expecting ' . $name);
    }
    
    return $value;
  }
}
