<?php namespace peer\mail\unittest;

use peer\mail\MimePart;

class MimePartHeaderParsingTest extends HeaderParsingTest {

  /**
   * Parse a string containing MimePart headers
   *
   * @param  string $str
   * @return peer.mail.MimePart
   */
  protected function parse($str) {
    $m= new MimePart();
    $m->setHeaderString($str."\n\n");
    return $m;
  }

  #[@test, @values(['Content-Type', 'Content-Transfer-Encoding', 'Content-Disposition'])]
  public function ignored($header) {
    $this->assertEquals([], $this->parse($header.': Test')->headers);
  }
}