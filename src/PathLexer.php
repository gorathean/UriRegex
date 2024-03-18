<?php
declare(strict_types=1);

namespace Gorath\UriRegex;
use StdClass;

class PathLexer {
  private array $tokens = [];
  private string $str = '';
  private int $current_idx = 0;
  
  public function __toString() {
    return 'hello';
  }
  
  public function generateTokens(string $value) {
    $length = strlen($value);
    
    while ($this->current_idx < $length) {
      $current_char = $value[$this->current_idx];
      
      if ($current_char == '\\') {
        $this->pushToken('esc', $value[$this->current_idx ++], $this->current_idx ++);
        continue;
      }
      
      if ($current_char == '{') {
        $this->pushToken('open', $current_char, $this->current_idx ++);
        continue;
      }
      
      if ($current_char == '}') {
        $this->pushToken('close', $current_char, $this->current_idx ++);
        continue;
      }
      
      if ($current_char == '(') {
        $inner_idx = $this->current_idx + 1;
        $scope_level = 1;
        $content = '';
        
        if ($value[$inner_idx] == '?') {
          throw new Exception('Unexpected ? cannot treat regex identifier as capturing group at index ' . $inner_idx);
        }
        
        while ($inner_idx < $length) {
          $inner_char = $value[$inner_idx ++];
          if ($inner_char == '(') {
            $scope_level ++;
          }
          
          if ($inner_char == ')') {
            $scope_level --;
            
            if ($scope_level == 0) {
              break;
            }
          }
          
          $content .= $inner_char;
        }
        
        if ($scope_level) {
          throw new Error('Undetermined end of string, regex indentifier not closed at index ' . $inner_idx);
        }
        
        $this->pushToken('pattern', $content, $this->current_idx);
        $this->current_idx = $inner_idx;
        continue;
      }
      
      if ($current_char == '?') {
        $this->pushToken('optional', $current_char, $this->current_idx ++);
        continue;
      }
      
      if ($current_char == '*') {
        $this->pushToken('wild', $current_char, $this->current_idx ++);
        continue;
      }
      
      if (ord($current_char) == 32) {
        $this->pushToken('space', $current_char, $this->current_idx ++);
        continue;
      }
      
      if (
        ord($current_char) >= 97 && ord($current_char) <= 122
        || ord($current_char) >= 65 && ord($current_char) <= 90
        || ord($current_char) == 95
      ) {
        $this->str .= $current_char;
        $this->current_idx ++;
        continue;
      }
      
      $this->pushToken('char', $current_char, $this->current_idx ++);
    }
    
    $this->pushToken('end', '', $this->current_idx);
    return $this->tokens;
  }
  
  private function pushToken(string $name, string $value, int $idx) {
    $this->pushString();
    $token = new stdClass();
    $token->name = $name;
    $token->value = $value;
    $token->idx =  $idx;
    array_push($this->tokens, $token);
  }
  
  private function pushString() {
    $len = strlen($this->str);
    if ($len > 0) {
      $str = $this->str;
      $this->str = '';
      $this->pushToken('string', $str, $this->current_idx - $len);
    } 
  }
  
}

