<?php namespace peer\mail\unittest;

use peer\mail\MimePart;
use test\{Assert, Test, Values};

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

  #[Test, Values(['Content-Type', 'Content-Transfer-Encoding', 'Content-Disposition'])]
  public function ignored($header) {
    Assert::equals([], $this->parse($header.': Test')->headers);
  }
}