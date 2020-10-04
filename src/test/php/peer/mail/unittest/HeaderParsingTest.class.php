<?php namespace peer\mail\unittest;

use unittest\{Test, Values, TestCase};

/**
 * Tests header parsing - Message::setHeaderString()
 */
abstract class HeaderParsingTest extends TestCase {

  /**
   * Parse a string containing message headers
   *
   * @param  string $str
   * @return peer.mail.Message
   */
  protected abstract function parse($str);

  #[Test, Values(["Header: Value", "Header:Value"])]
  public function header($variant) {
    $this->assertEquals(['Header' => 'Value'], $this->parse($variant)->headers);
  }

  #[Test, Values(["Header:", "Header: "])]
  public function empty_header($variant) {
    $this->assertEquals(['Header' => null], $this->parse($variant)->headers);
  }

  #[Test, Values(["Header: Line 1\n\tLine 2", "Header: Line 1\n Line 2"])]
  public function line_continued($variant) {
    $this->assertEquals(['Header' => 'Line 1 Line 2'], $this->parse($variant)->headers);
  }
}