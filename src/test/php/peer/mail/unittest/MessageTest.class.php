<?php namespace peer\mail\unittest;

use peer\mail\Message;
use unittest\Test;

/**
 * Tests Message class
 */
class MessageTest extends AbstractMessageTest {

  /**
   * Returns a new fixture
   *
   * @return  peer.mail.Message
   */
  protected function newFixture() {
    return new Message();
  }

  #[Test]
  public function default_headers_returned_by_getHeaderString() {
    $this->fixture->setHeader('X-Common-Header', 'test');
    $this->assertEquals(
      "X-Common-Header: test\n".
      "Content-Type: text/plain;\n".
      "\tcharset=\"utf-8\"\n".
      "Mime-Version: 1.0\n".
      "Content-Transfer-Encoding: 8bit\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
  }
}