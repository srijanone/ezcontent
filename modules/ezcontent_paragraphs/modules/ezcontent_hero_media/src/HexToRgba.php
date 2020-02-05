<?php

namespace Drupal\ezcontent_hero_media;

/**
 * Converts hex code to rgba color.
 */
class HexToRgba {

  /**
   * Get rgba color from hex code.
   */
  public function hex2rgba($color, $opacity = FALSE) {
    $default = 'rgb(0,0,0)';
    // Return default if no color provided.
    if (empty($color)) {
      return $default;
    }
    // Validate provided color if it is a valid hex color starting with #.
    if ($color[0] == '#') {
      $color = substr($color, 1);
    }
    // Check if hex color has 6 0r 3 letter.
    if (strlen($color) == 6) {
      $hex = [
        $color[0] . $color[1],
        $color[2] . $color[3],
        $color[4] . $color[5],
      ];
    }
    elseif (strlen($color) == 3) {
      $hex = [
        $color[0] . $color[0],
        $color[1] . $color[1],
        $color[2] . $color[2],
      ];
    }
    else {
      return $default;
    }
    $rgb = array_map('hexdec', $hex);
    // Check if opacity is set.
    if ($opacity) {
      if (abs($opacity) > 1) {
        $opacity = 1.0;
      }
      $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
    }
    else {
      $output = 'rgb(' . implode(",", $rgb) . ')';
    }
    return $output;
  }

}
