<?php namespace peer\mail\unittest;

use peer\mail\InternetAddress;
use test\{Assert, Test, Values};
use util\Date;

abstract class AbstractMessageTest {

  /**
   * Returns a new fixture
   *
   * @return  peer.mail.Message
   */
  protected abstract function newFixture();

  /**
   * Returns recipient types
   *
   * @return string[]
   */
  protected function recipientTypes() {
    return ['to', 'cc', 'bcc'];
  }

  #[Test, Values('recipientTypes')]
  public function getRecipient_for_single_recipient($type) {
    $fixture= $this->newFixture();
    $r= new InternetAddress('thekid@example.com');
    $fixture->addRecipient($type, $r);
    Assert::equals($r, $fixture->getRecipient($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipient_for_multiple_recipients($type) {
    $fixture= $this->newFixture();
    $r1= new InternetAddress('thekid@example.com');
    $r2= new InternetAddress('alex@example.com');
    $fixture->addRecipient($type, $r1);
    $fixture->addRecipient($type, $r2);
    Assert::equals($r1, $fixture->getRecipient($type));
    Assert::equals($r2, $fixture->getRecipient($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipients_initially_returns_empty_array($type) {
    $fixture= $this->newFixture();
    Assert::equals([], $fixture->getRecipients($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipients_returns_recipients_added_via_addRecipient($type) {
    $fixture= $this->newFixture();
    $r1= new InternetAddress('thekid@example.com');
    $r2= new InternetAddress('alex@example.com');
    $fixture->addRecipient($type, $r1);
    $fixture->addRecipient($type, $r2);
    Assert::equals([$r1, $r2], $fixture->getRecipients($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipients_returns_recipients_added_via_addRecipients($type) {
    $fixture= $this->newFixture();
    $r1= new InternetAddress('thekid@example.com');
    $r2= new InternetAddress('alex@example.com');
    $fixture->addRecipients($type, [$r1, $r2]);
    Assert::equals([$r1, $r2], $fixture->getRecipients($type));
  }

  #[Test]
  public function getHeader_returns_null_if_header_doesnt_exist() {
    $fixture= $this->newFixture();
    Assert::null($fixture->getHeader('X-Common-Header'));
  }

  #[Test]
  public function getHeader_returns_added_header() {
    $fixture= $this->newFixture();
    $fixture->setHeader('X-Common-Header', 'test');
    Assert::equals('test', $fixture->getHeader('X-Common-Header'));
  }

  #[Test, Values(['x-common-header', 'X-COMMON-HEADER', 'X-common-header'])]
  public function getHeader_returns_added_header_case_insensitively($variant) {
    $fixture= $this->newFixture();
    $fixture->setHeader('X-Common-Header', 'test');
    Assert::equals('test', $fixture->getHeader($variant));
  }

  #[Test]
  public function subject_accessors() {
    $fixture= $this->newFixture();
    $fixture->setSubject('Hello World');
    Assert::equals('Hello World', $fixture->getSubject());
  }

  #[Test]
  public function message_id_accessors() {
    $fixture= $this->newFixture();
    $fixture->setMessageId('1234');
    Assert::equals('1234', $fixture->getMessageId());
  }

  #[Test]
  public function date_accessors() {
    $fixture= $this->newFixture();
    $d= Date::now();
    $fixture->setDate($d);
    Assert::equals($d, $fixture->getDate());
  }

  #[Test]
  public function encoding_accessors() {
    $fixture= $this->newFixture();
    $fixture->setEncoding('8bit');
    Assert::equals('8bit', $fixture->getEncoding());
  }

  #[Test]
  public function charset_accessors() {
    $fixture= $this->newFixture();
    $fixture->setCharset('utf-8');
    Assert::equals('utf-8', $fixture->getCharset());
  }

  #[Test]
  public function content_type_accessors() {
    $fixture= $this->newFixture();
    $fixture->setContentType('text/plain');
    Assert::equals('text/plain', $fixture->getContentType());
  }

  #[Test]
  public function mime_version_accessors() {
    $fixture= $this->newFixture();
    $fixture->setMimeVersion('1.0');
    Assert::equals('1.0', $fixture->getMimeVersion());
  }

  #[Test]
  public function unencoded_body() {
    $fixture= $this->newFixture();
    $fixture->setBody('Hello World');
    Assert::equals('Hello World', $fixture->getBody());
  }

  #[Test]
  public function base64_encoded_body() {
    $fixture= $this->newFixture();
    $fixture->setEncoding('base64');
    $fixture->setBody('SGVsbG8gV29ybGQ=');
    Assert::equals('Hello World', $fixture->getBody(true));
  }

  #[Test]
  public function quoted_printable_encoded_body() {
    $fixture= $this->newFixture();
    $fixture->setEncoding('quoted-printable');
    $fixture->setBody('Hello World=3D');
    Assert::equals('Hello World=', $fixture->getBody(true));
  }
}