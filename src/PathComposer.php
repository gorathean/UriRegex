<?php
declare(strict_types=1);

namespace Gorathean\UriRegex;

use Gorathean\UriRegex\PathLexer;
use Exception;

class PathComposer {
  private PathLexer $lexer_instance;
  private array $tokens = [];
  private int $length   = 0;
  private int $idx      = 0;
  
  public function __construct
  (
    private PathLexer | string $lexer,
    public array $config
  ) {
    $this->lexer_instance = gettype($lexer) == 'string' 
      ? new $lexer() 
      : $lexer;
  }
  
  public function parse(string $path) {
    $instance = new PathComposer($this->lexer, $this->config);
    return $instance->compose($path);
  }
  
  private function compose(string $path) {
    $this->tokens = $this->lexer_instance->generateTokens($path);
    $this->length = count($this->tokens);
    
    //configs
    $default_pattern = $this->config['default_pattern'] ?? '[^#?\\/]';
    
    $parse_list = [];
    
    while ($this->idx < $this->length) {
      $char     = $this->should_take('char');
      $open     = $this->should_take('open');
      $pattern  = $this->should_take('pattern');
      
      if ($open || $pattern) {
        $prefix = $char ?? '';
        if ($prefix && $prefix != '/') {
          array_push($parse_list, $prefix);
          $prefix = '';
        }
        
        $variables = [
          'prefix'   => $prefix,
          'name'     => $open ? $this->must_take('string') : 0,
          'pattern'  => $this->should_take('pattern') ?? $pattern ?? $default_pattern,
          'optional' => $pattern ? $this->should_take('optional') : ''
        ];
        
        if ($open) {
          $this->must_take('close');
          $variables['optional'] = $this->should_take('optional') ?? '';
        }
        
        array_push($parse_list, $variables);
        continue;
      }
      
      $paths = $char ?? $this->should_take('string')
      ?? $this->should_take('space')
      ?? $this->should_take('esc');
      
      if ($paths) {
        array_push($parse_list, $paths);
        continue;
      }
      
      if ($this->should_take('wild')) {
        array_push($parse_list, [ 
          'name'     => 'wild', 
          'pattern'  => '(?:[^#?\\/]*)',
        ]);
        continue;
      }
      
      break;
    }
    
    $this->must_take('end');
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
