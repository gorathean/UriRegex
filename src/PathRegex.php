<?php
declare(strict_types=1);
namespace Gorathean\UriRegex;

use Gorathean\UriRegex\PathComposer;

class PathRegex {
  public function __construct(
    private PathComposer $parser
  ) {}
  
  public function toRegex(string $uri_expression) {
    $list    = $this->parser->parse($uri_expression);
    $length  = count($list);
    
    $config  = $this->parser->config;
    $strict  = array_key_exists('strict', $config) ? $config['strict'] : true;
    $trailer = array_key_exists('trailer', $config) ? $config['trailer'] : true;
    
    $pattern = '';
    
    for ($idx = 0; $idx < $length; $idx ++) {
      $path = $list[$idx];
      
      if (is_string($path)) {
        $pattern .= $this->escape_string($path);
        continue;
      }
      
      if ($path['name'] == 'wild') {
        $pattern .= $path['pattern'];
        continue;
      }
      
      $param_pattern =  (is_string($path['name']) ? '(?<' . $path['name'] .  '>' : '(?:') . $path['pattern'] . '+)';
      $pattern .= (! $path['prefix']) 
        ? $param_pattern 
        : '(?:\/' . $param_pattern . ($path['optional'] ? '?)' : ')');
      
      if ($path['optional']) {
        $pattern .= '?';
      }
    }
    
    if ($trailer && strlen($pattern)) {
      if ($pattern[strlen($pattern) - 1] == '/') {
        $pattern .= '?';
      } else {
        $pattern .= '\/?';
      }
    }
    
    return '/^' . $pattern . '$/';
  }
  
  private function escape_string (string $value): string {
    $pattern = '/[\\/\\\{#\\+\\?:<\\*(>.^)$}]/';
    return preg_replace_callback($pattern, fn($match) => '\\' . $match[0], $value);
  }
}

