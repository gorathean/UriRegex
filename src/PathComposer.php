<?php
declare(strict_types=1);

namespace Gorath\UriRegex;

use Gorath\UriRegex\PathLexer;
use Exception;

class PathComposer {
  private PathLexer $lexer;
  private array $tokens = [];
  private int $length   = 0;
  private int $idx      = 0;
  
  public function __construct
  (
    PathLexer | string $lexer, 
    protected array $config
  ) {
    $this->lexer = gettype($lexer) == 'string' 
      ? new $lexer() 
      : $lexer;
  }
  
  public function parse(string | array $path) {
    $this->tokens = $this->lexer->generateTokens($path);
    $this->length = count($this->tokens);
    
    //configs
    $default_pattern = $this->config['default_pattern'] ?? '[^#/?]';
    $allow_wildcards = $this->config['allow_wildcards'] ?? true;
    $allow_spaces    = $this->config['allow_spaces'] ?? true;
    
    $parse_list = [];
    
    while ($this->idx < $this->length) {
      $char    = $this->should_take('char');
      $open    = $this->should_take('open');
      $pattern = $this->should_take('pattern');
      
      if ($open || $pattern) {
        $prefix = $char ?? '';
        if ($prefix != '/') {
          array_push($parse_list, '/');
          $prefix = '';
        }
        
        $variables = [
          'prefix' => $prefix,
          'name' => $open ? $this->must_take('string') : 0,
          'pattern' => $this->should_take('pattern') ?? $pattern ?? $default_pattern,
        ];
        
        if ($allow_wildcards)
          $variables['optional'] = $pattern ? $this->should_take('optional') : '';
        
        if ($open) {
          $this->must_take('close');
          if ($allow_wildcards) 
            $variables['optional'] = $this->should_take('optional') ?? '';
        }
        
        array_push($parse_list, $variables);
        continue;
      }
      
      $this->idx ++;
      //break;
    }
    
    return $parse_list;
  }
  
  private function should_take (string $name, bool $ignore_space = true): ?string {
    $idx = $this->idx;
    while ($idx < $this->length && $this->tokens[$idx]->name == 'space' && $ignore_space) {
      $idx ++;
    }
    
    if ($idx < $this->length && $this->tokens[$idx]->name == $name) {
      $this->idx = $idx;
      return $this->tokens[$this->idx ++]->value;
    }
    
    $this->idx = $idx;
    return null;
  }
  
  private function must_take (string $name, bool $ignore_space = true): Error | string {
    $value = '';
    if ($this->idx < $this->length && is_null($value = $this->should_take($name, $ignore_space))) {
      $current = $this->tokens[$this->idx];
      throw new Exception('Unexcepted "' . $current->value . '" at index ' . $current->idx . '. expecting ' . $name);
    }
    
    return $value;
  }
}
