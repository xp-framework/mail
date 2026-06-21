<?php namespace peer\mail\unittest;

use peer\mail\{MimeMessage, MimePart};
use test\{Assert, Test};

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
    $fixture= $this->newFixture();
    $fixture->setHeader('X-Common-Header', 'test');
    Assert::equals(
      "Mime-Version: 1.0\n".
      "X-Common-Header: test\n".
      "Content-Type: multipart/mixed; boundary=\"------=_Part_4711Test\";\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$fixture->getDate()->toString('r')."\n",
      $fixture->getHeaderString()
    );
  }

  #[Test]
  public function boundary_accessors() {
    $fixture= $this->newFixture();
    $fixture->setBoundary('----=_Part_0815Test');
    Assert::equals('----=_Part_0815Test', $fixture->getBoundary());
  }

  #[Test]
  public function add_part_returns_added_part() {
    $fixture= $this->newFixture();
    $part= new MimePart();
    Assert::equals($part, $fixture->addPart($part));
  }

  #[Test]
  public function getParts_initially_returns_empty_array() {
    $fixture= $this->newFixture();
    Assert::equals([], $fixture->getParts());
  }

  #[Test]
  public function getParts_returns_added_part() {
    $fixture= $this->newFixture();
    $part= $fixture->addPart(new MimePart());
    Assert::equals([$part], $fixture->getParts());
  }

  #[Test]
  public function getParts_returns_added_parts() {
    $fixture= $this->newFixture();
    $part1= $fixture->addPart(new MimePart());
    $part2= $fixture->addPart(new MimePart());
    Assert::equals([$part1, $part2], $fixture->getParts());
  }

  #[Test]
  public function getPart_returns_added_part() {
    $fixture= $this->newFixture();
    $part= $fixture->addPart(new MimePart());
    Assert::equals($part, $fixture->getPart(0));
  }

  #[Test]
  public function getPart_returns_added_parts() {
    $fixture= $this->newFixture();
    $part1= $fixture->addPart(new MimePart());
    $part2= $fixture->addPart(new MimePart());
    Assert::equals([$part1, $part2], [$fixture->getPart(0), $fixture->getPart(1)]);
  }

  #[Test]
  public function setBody_sets_first_part() {
    $fixture= $this->newFixture();
    $fixture->setBody('Test');
    Assert::equals(new MimePart('Test', 'text/plain'), $fixture->getPart(0));
  }

  #[Test]
  public function setBody_removes_previously_added_parts() {
    $fixture= $this->newFixture();
    $fixture->addPart(new MimePart());
    $fixture->setBody('Test');
    Assert::equals(new MimePart('Test', 'text/plain'), $fixture->getPart(0));
  }

  #[Test]
  public function getBody_for_two_parts() {
    $fixture= $this->newFixture();
    $fixture->addPart(new MimePart('Test', 'text/plain'));
    $fixture->addPart(new MimePart('GIF89aXXXX', 'image/gif', '8bit', 'test.gif'));
    Assert::equals(
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
      $fixture->getBody()
    );
  }

  #[Test]
  public function one_text_part() {
    $fixture= $this->newFixture();
    $fixture->addPart(new MimePart('Part #1', 'text/plain'));

    Assert::equals(
      "Mime-Version: 1.0\n".
      "Content-Type: text/plain;\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$fixture->getDate()->toString('r')."\n",
      $fixture->getHeaderString()
    );
    Assert::equals('Part #1', $fixture->getBody());
  }

  #[Test]
  public function one_image_part() {
    $fixture= $this->newFixture();
    $fixture->addPart(new MimePart('Part #1', 'image/gif'));
    
    Assert::equals(
      "Mime-Version: 1.0\n".
      "Content-Type: image/gif;\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$fixture->getDate()->toString('r')."\n",
      $fixture->getHeaderString()
    );
    Assert::equals('Part #1', $fixture->getBody());
  }

  #[Test]
  public function two_text_parts() {
    $fixture= $this->newFixture();
    $fixture->addPart(new MimePart('Part #1', 'text/plain'));
    $fixture->addPart(new MimePart('Part #2', 'text/plain'));
    
    Assert::equals(
      "Mime-Version: 1.0\n".
      "Content-Type: multipart/mixed; boundary=\"------=_Part_4711Test\";\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$fixture->getDate()->toString('r')."\n",
      $fixture->getHeaderString()
    );
    Assert::equals(
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
      $fixture->getBody()
    );
  }

  #[Test]
  public function two_image_parts() {
    $fixture= $this->newFixture();
    $fixture->addPart(new MimePart('Part #1', 'image/gif'));
    $fixture->addPart(new MimePart('Part #2', 'image/gif'));
    
    Assert::equals(
      "Mime-Version: 1.0\n".
      "Content-Type: multipart/mixed; boundary=\"------=_Part_4711Test\";\n".
      "\tcharset=\"utf-8\"\n".
      "X-Priority: 3 (Normal)\n".
      "Date: ".$fixture->getDate()->toString('r')."\n",
      $fixture->getHeaderString()
    );
    Assert::equals(
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
      $fixture->getBody()
    );
  }
}