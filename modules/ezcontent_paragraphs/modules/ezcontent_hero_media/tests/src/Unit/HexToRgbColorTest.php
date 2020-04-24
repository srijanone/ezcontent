<?php

namespace Drupal\Tests\ezcontent_hero_media\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ezcontent_hero_media\HexToRgba;

/**
 * Unit test case for hex to rgb convert.
 *
 * @group ezcontent_smart_article
 *
 * @coversDefaultClass \Drupal\ezcontent_hero_media\HexToRgba
 */
class HexToRgbColorTest extends UnitTestCase {

  /**
   * @covers Drupal\ezcontent_hero_media\HexToRgba::hex2rgba
   */
  public function testHexToRgbColor() {
    $hex_to_rgb = new HexToRgba();
    $color = $hex_to_rgb->hex2rgba('#213c52', 0.37);
    $this->assertEquals('rgba(33,60,82,0.37)', $color);
  }

}
