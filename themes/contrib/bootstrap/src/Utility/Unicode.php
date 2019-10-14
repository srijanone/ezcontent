<?php

namespace Drupal\bootstrap\Utility;

use Drupal\bootstrap\Bootstrap;
use Drupal\Component\Utility\Unicode as CoreUnicode;
use Drupal\Component\Utility\Xss;

/**
 * Extends \Drupal\Component\Utility\Unicode.
 *
 * @ingroup utility
 */
class Unicode extends CoreUnicode {

  /**
   * Casts a value to a string, recursively if an array.
   *
   * @param mixed $value
   *   Any value.
   * @param string $delimiter
   *   The delimiter to use when joining multiple items in an array.
   *
   * @return string
   *   The cast string.
   */
  public static function castToString($value = NULL, $delimiter = '.') {
    if (is_object($value) && method_exists($value, '__toString')) {
      return (string) ($value->__toString() ?: '');
    }
    if (is_array($value)) {
      foreach ($value as $key => $item) {
        $value[$key] = static::castToString($item, $delimiter);
      }
      return implode($delimiter, array_filter($value));
    }
    // Handle scalar values.
    if (isset($value) && is_scalar($value) && !is_bool($value)) {
      return (string) $value;
    }
    return '';
  }

  /**
   * Extracts the hook name from a function name.
   *
   * @param string $string
   *   The function name to extract the hook name from.
   * @param string $suffix
   *   A suffix hook ending (like "alter") to also remove.
   * @param string $prefix
   *   A prefix hook beginning (like "form") to also remove.
   *
   * @return string
   *   The extracted hook name.
   */
  public static function extractHook($string, $suffix = NULL, $prefix = NULL) {
    $regex = '^(' . implode('|', array_keys(Bootstrap::getTheme()->getAncestry())) . ')';
    $regex .= $prefix ? '_' . $prefix : '';
    $regex .= $suffix ? '_|_' . $suffix . '$' : '';
    return preg_replace("/$regex/", '', $string);
  }

  /**
   * Converts a callback to a string representation.
   *
   * @param array|string $callback
   *   The callback to convert.
   * @param bool $array
   *   Flag determining whether or not to convert the callback to an array.
   *
   * @return string
   *   The converted callback as a string or an array if $array is specified.
   *
   * @see \Drupal\bootstrap\Bootstrap::addCallback()
   */
  public static function convertCallback($callback, $array = FALSE) {
    if (is_array($callback)) {
      if (is_object($callback[0])) {
        $callback[0] = get_class($callback[0]);
      }
      $callback = implode('::', $callback);
    }
    if ($callback[0] === '\\') {
      $callback = self::substr($callback, 1);
    }
    if ($array && self::strpos($callback, '::') !== FALSE) {
      $callback = explode('::', $callback);
    }
    return $callback;
  }

  /**
   * Escapes a delimiter in a string.
   *
   * Note: this is primarily useful in situations where dot notation is used
   * where the values also contain dots, like in a semantic version string.
   *
   * @param string $string
   *   The string to search in.
   * @param string $delimiter
   *   The delimiter to escape.
   *
   * @return string
   *   The escaped string.
   *
   * @see \Drupal\bootstrap\Utility\Unicode::splitDelimiter()
   */
  public static function escapeDelimiter($string, $delimiter = '.') {
    return str_replace($delimiter, "\\$delimiter", $string);
  }

  /**
   * Determines if a string of text is considered "simple".
   *
   * @param string $string
   *   The string of text to check "simple" criteria on.
   * @param int|false $length
   *   The length of characters used to determine whether or not $string is
   *   considered "simple". Set explicitly to FALSE to disable this criteria.
   * @param array|false $allowed_tags
   *   An array of allowed tag elements. Set explicitly to FALSE to disable this
   *   criteria.
   * @param bool $html
   *   A variable, passed by reference, that indicates whether or not the
   *   string contains HTML.
   *
   * @return bool
   *   Returns TRUE if the $string is considered "simple", FALSE otherwise.
   */
  public static function isSimple($string, $length = 250, $allowed_tags = NULL, &$html = FALSE) {
    // Typecast to a string (if an object).
    $string_clone = (string) $string;

    // Use the advanced drupal_static() pattern.
    static $drupal_static_fast;
    if (!isset($drupal_static_fast)) {
      $drupal_static_fast['strings'] = &drupal_static(__METHOD__);
    }
    $strings = &$drupal_static_fast['strings'];
    if (!isset($strings[$string_clone])) {
      $plain_string = strip_tags($string_clone);
      $simple = TRUE;
      if ($allowed_tags !== FALSE) {
        $filtered_string = Xss::filter($string_clone, $allowed_tags);
        $html = $filtered_string !== $plain_string;
        $simple = $simple && $string_clone === $filtered_string;
      }
      if ($length !== FALSE) {
        $simple = $simple && strlen($plain_string) <= intval($length);
      }
      $strings[$string_clone] = $simple;
    }
    return $strings[$string_clone];
  }

  /**
   * Splits a string by a specified delimiter, allowing them to be escaped.
   *
   * Note: this is primarily useful in situations where dot notation is used
   * where the values also contain dots, like in a semantic version string.
   *
   * @param string $string
   *   The string to split into parts.
   * @param string $delimiter
   *   The delimiter used to split the string.
   * @param bool $escapable
   *   Flag indicating whether the $delimiter can be escaped using a backward
   *   slash (\).
   *
   * @return array
   *   An array of strings, split where the specified $delimiter was present.
   *
   * @see \Drupal\bootstrap\Utility\Unicode::escapeDelimiter()
   * @see https://stackoverflow.com/a/6243797
   */
  public static function splitDelimiter($string, $delimiter = '.', $escapable = TRUE) {
    if (!$escapable) {
      return explode($delimiter, $string);
    }

    // Split based on delimiter.
    $parts = preg_split('~\\\\' . preg_quote($delimiter, '~') . '(*SKIP)(*FAIL)|\.~s', $string);

    // Iterate over the parts and remove backslashes from delimiters.
    return array_map(function ($string) use ($delimiter) {
      return str_replace("\\$delimiter", $delimiter, $string);
    }, $parts);
  }

}
