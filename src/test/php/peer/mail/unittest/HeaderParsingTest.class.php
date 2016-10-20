<?php namespace peer\mail\unittest;

/**
 * Tests header parsing - Message::setHeaderString()
 */
abstract class HeaderParsingTest extends \unittest\TestCase {

  /**
   * Parse a string containing message headers
   *
   * @param  string $str
   * @return peer.mail.Message
   */
  protected abstract function parse($str);

  #[@test, @values([
  #  "Header:",
  #  "Header: "
  #])]
  public function empty_header($variant) {
    $this->assertEquals(['Header' => null], $this->parse($variant)->headers);
  }

  #[@test, @values([
  #  "Header: Line 1\n\tLine 2",
  #  "Header: Line 1\n Line 2"
  #])]
  public function line_continued($variant) {
    $this->assertEquals(['Header' => 'Line 1 Line 2'], $this->parse($variant)->headers);
  }
}