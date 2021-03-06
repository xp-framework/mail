<?php namespace peer\mail\unittest;

use peer\mail\{MimeMessage, MimePart};
use unittest\Test;

/**
 * Tests MimeMessage class
 */
class MimeMessageTest extends AbstractMessageTest {

  /**
   * Returns a new fixture
   *
   * @return  peer.mail.Message
   */
  protected function newFixture() {
    $fixture= new MimeMessage();
    $fixture->setBoundary('------=_Part_4711Test');
    return $fixture;
  }

  #[Test]
  public function default_headers_returned_by_getHeaderString() {
    $this->fixture->setHeader('X-Common-Header', 'test');
    $this->assertEquals(
      "Mime-Version: 1.0\n".
      "X-Common-Header: test\n".
      "Content-Type: multipart/mixed; boundary=\"------=_Part_4711Test\";\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
  }

  #[Test]
  public function boundary_accessors() {
    $this->fixture->setBoundary('----=_Part_0815Test');
    $this->assertEquals('----=_Part_0815Test', $this->fixture->getBoundary());
  }

  #[Test]
  public function add_part_returns_added_part() {
    $part= new MimePart();
    $this->assertEquals($part, $this->fixture->addPart($part));
  }

  #[Test]
  public function getParts_initially_returns_empty_array() {
    $this->assertEquals([], $this->fixture->getParts());
  }

  #[Test]
  public function getParts_returns_added_part() {
    $part= $this->fixture->addPart(new MimePart());
    $this->assertEquals([$part], $this->fixture->getParts());
  }

  #[Test]
  public function getParts_returns_added_parts() {
    $part1= $this->fixture->addPart(new MimePart());
    $part2= $this->fixture->addPart(new MimePart());
    $this->assertEquals([$part1, $part2], $this->fixture->getParts());
  }

  #[Test]
  public function getPart_returns_added_part() {
    $part= $this->fixture->addPart(new MimePart());
    $this->assertEquals($part, $this->fixture->getPart(0));
  }

  #[Test]
  public function getPart_returns_added_parts() {
    $part1= $this->fixture->addPart(new MimePart());
    $part2= $this->fixture->addPart(new MimePart());
    $this->assertEquals([$part1, $part2], [$this->fixture->getPart(0), $this->fixture->getPart(1)]);
  }

  #[Test]
  public function setBody_sets_first_part() {
    $this->fixture->setBody('Test');
    $this->assertEquals(new MimePart('Test', 'text/plain'), $this->fixture->getPart(0));
  }

  #[Test]
  public function setBody_removes_previously_added_parts() {
    $this->fixture->addPart(new MimePart());
    $this->fixture->setBody('Test');
    $this->assertEquals(new MimePart('Test', 'text/plain'), $this->fixture->getPart(0));
  }

  #[Test]
  public function getBody_for_two_parts() {
    $this->fixture->addPart(new MimePart('Test', 'text/plain'));
    $this->fixture->addPart(new MimePart('GIF89aXXXX', 'image/gif', '8bit', 'test.gif'));
    $this->assertEquals(
      "This is a multi-part message in MIME format.\n".
      "\n".
      "--------=_Part_4711Test\n".
      "Content-Type: text/plain; charset=\"utf-8\"\n".
      "\n".
      "Test\n".
      "\n".
      "--------=_Part_4711Test\n".
      "Content-Type: image/gif; name=test.gif\n".
      "Content-Transfer-Encoding: 8bit\n".
      "Content-Disposition: attachment; filename=\"test.gif\"\n".
      "\n".
      "GIF89aXXXX\n".
      "\n".
      "--------=_Part_4711Test--\n",
      $this->fixture->getBody()
    );
  }

  #[Test]
  public function one_text_part() {
    $this->fixture->addPart(new MimePart('Part #1', 'text/plain'));

    $this->assertEquals(
      "Mime-Version: 1.0\n".
      "Content-Type: text/plain;\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
    $this->assertEquals('Part #1', $this->fixture->getBody());
  }

  #[Test]
  public function one_image_part() {
    $this->fixture->addPart(new MimePart('Part #1', 'image/gif'));
    
    $this->assertEquals(
      "Mime-Version: 1.0\n".
      "Content-Type: image/gif;\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
    $this->assertEquals('Part #1', $this->fixture->getBody());
  }

  #[Test]
  public function two_text_parts() {
    $this->fixture->addPart(new MimePart('Part #1', 'text/plain'));
    $this->fixture->addPart(new MimePart('Part #2', 'text/plain'));
    
    $this->assertEquals(
      "Mime-Version: 1.0\n".
      "Content-Type: multipart/mixed; boundary=\"------=_Part_4711Test\";\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
    $this->assertEquals(
      "This is a multi-part message in MIME format.\n".
      "\n".
      "--------=_Part_4711Test\n".
      "Content-Type: text/plain; charset=\"utf-8\"\n".
      "\n".
      "Part #1\n".
      "\n".
      "--------=_Part_4711Test\n".
      "Content-Type: text/plain; charset=\"utf-8\"\n".
      "\n".
      "Part #2\n".
      "\n".
      "--------=_Part_4711Test--\n",
      $this->fixture->getBody()
    );
  }

  #[Test]
  public function two_image_parts() {
    $this->fixture->addPart(new MimePart('Part #1', 'image/gif'));
    $this->fixture->addPart(new MimePart('Part #2', 'image/gif'));
    
    $this->assertEquals(
      "Mime-Version: 1.0\n".
      "Content-Type: multipart/mixed; boundary=\"------=_Part_4711Test\";\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$this->fixture->getDate()->toString('r')."\n",
      $this->fixture->getHeaderString()
    );
    $this->assertEquals(
      "This is a multi-part message in MIME format.\n".
      "\n".
      "--------=_Part_4711Test\n".
      "Content-Type: image/gif; charset=\"utf-8\"\n".
      "\n".
      "Part #1\n".
      "\n".
      "--------=_Part_4711Test\n".
      "Content-Type: image/gif; charset=\"utf-8\"\n".
      "\n".
      "Part #2\n".
      "\n".
      "--------=_Part_4711Test--\n",
      $this->fixture->getBody()
    );
  }
}