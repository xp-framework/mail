<?php namespace peer\mail\unittest;

use peer\mail\InternetAddress;
use unittest\{Test, Values};
use util\Date;

/**
 * Tests Message class
 */
abstract class AbstractMessageTest extends \unittest\TestCase {
  protected $fixture= null;

  /**
   * Returns a new fixture
   *
   * @return  peer.mail.Message
   */
  protected abstract function newFixture();

  /**
   * Setup
   */
  public function setUp() {
    $this->fixture= $this->newFixture();
  }

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
    $r= new InternetAddress('thekid@example.com');
    $this->fixture->addRecipient($type, $r);
    $this->assertEquals($r, $this->fixture->getRecipient($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipient_for_multiple_recipients($type) {
    $r1= new InternetAddress('thekid@example.com');
    $r2= new InternetAddress('alex@example.com');
    $this->fixture->addRecipient($type, $r1);
    $this->fixture->addRecipient($type, $r2);
    $this->assertEquals($r1, $this->fixture->getRecipient($type));
    $this->assertEquals($r2, $this->fixture->getRecipient($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipients_initially_returns_empty_array($type) {
    $this->assertEquals([], $this->fixture->getRecipients($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipients_returns_recipients_added_via_addRecipient($type) {
    $r1= new InternetAddress('thekid@example.com');
    $r2= new InternetAddress('alex@example.com');
    $this->fixture->addRecipient($type, $r1);
    $this->fixture->addRecipient($type, $r2);
    $this->assertEquals([$r1, $r2], $this->fixture->getRecipients($type));
  }

  #[Test, Values('recipientTypes')]
  public function getRecipients_returns_recipients_added_via_addRecipients($type) {
    $r1= new InternetAddress('thekid@example.com');
    $r2= new InternetAddress('alex@example.com');
    $this->fixture->addRecipients($type, [$r1, $r2]);
    $this->assertEquals([$r1, $r2], $this->fixture->getRecipients($type));
  }

  #[Test]
  public function getHeader_returns_null_if_header_doesnt_exist() {
    $this->assertNull($this->fixture->getHeader('X-Common-Header'));
  }

  #[Test]
  public function getHeader_returns_added_header() {
    $this->fixture->setHeader('X-Common-Header', 'test');
    $this->assertEquals('test', $this->fixture->getHeader('X-Common-Header'));
  }

  #[Test, Values(['x-common-header', 'X-COMMON-HEADER', 'X-common-header'])]
  public function getHeader_returns_added_header_case_insensitively($variant) {
    $this->fixture->setHeader('X-Common-Header', 'test');
    $this->assertEquals('test', $this->fixture->getHeader($variant));
  }

  #[Test]
  public function subject_accessors() {
    $this->fixture->setSubject('Hello World');
    $this->assertEquals('Hello World', $this->fixture->getSubject());
  }

  #[Test]
  public function message_id_accessors() {
    $this->fixture->setMessageId('1234');
    $this->assertEquals('1234', $this->fixture->getMessageId());
  }

  #[Test]
  public function date_accessors() {
    $d= Date::now();
    $this->fixture->setDate($d);
    $this->assertEquals($d, $this->fixture->getDate());
  }

  #[Test]
  public function encoding_accessors() {
    $this->fixture->setEncoding('8bit');
    $this->assertEquals('8bit', $this->fixture->getEncoding());
  }

  #[Test]
  public function charset_accessors() {
    $this->fixture->setCharset('utf-8');
    $this->assertEquals('utf-8', $this->fixture->getCharset());
  }

  #[Test]
  public function content_type_accessors() {
    $this->fixture->setContentType('text/plain');
    $this->assertEquals('text/plain', $this->fixture->getContentType());
  }

  #[Test]
  public function mime_version_accessors() {
    $this->fixture->setMimeVersion('1.0');
    $this->assertEquals('1.0', $this->fixture->getMimeVersion());
  }

  #[Test]
  public function unencoded_body() {
    $this->fixture->setBody('Hello World');
    $this->assertEquals('Hello World', $this->fixture->getBody());
  }

  #[Test]
  public function base64_encoded_body() {
    $this->fixture->setEncoding('base64');
    $this->fixture->setBody('SGVsbG8gV29ybGQ=');
    $this->assertEquals('Hello World', $this->fixture->getBody(true));
  }

  #[Test]
  public function quoted_printable_encoded_body() {
    $this->fixture->setEncoding('quoted-printable');
    $this->fixture->setBody('Hello World=3D');
    $this->assertEquals('Hello World=', $this->fixture->getBody(true));
  }
}