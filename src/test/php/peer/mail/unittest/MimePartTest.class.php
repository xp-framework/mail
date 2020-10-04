<?php namespace peer\mail\unittest;

use peer\mail\MimePart;
use unittest\Test;

class MimePartTest extends \unittest\TestCase {

  #[Test]
  public function can_create() {
    new MimePart();
  }

  #[Test]
  public function can_create_with_body() {
    new MimePart('Test');
  }

  #[Test]
  public function can_create_with_body_and_content_type() {
    new MimePart('Test', 'text/plain');
  }

  #[Test]
  public function body() {
    $this->assertEquals('Test', (new MimePart('Test', 'text/plain'))->getBody());
  }

  #[Test]
  public function content_type() {
    $this->assertEquals('text/plain', (new MimePart('Test', 'text/plain'))->getContentType());
  }

  #[Test]
  public function name() {
    $this->assertEquals('test.txt', (new MimePart('Test', 'text/plain', null, 'test.txt'))->getName());
  }

  #[Test]
  public function encoding() {
    $this->assertEquals(MIME_ENC_BASE64, (new MimePart('VGVzdA==', 'text/plain', MIME_ENC_BASE64))->getEncoding());
  }

  #[Test]
  public function is_not_an_attachment() {
    $this->assertFalse((new MimePart('Test', 'text/plain'))->isAttachment());
  }

  #[Test]
  public function given_a_name_is_an_attachment() {
    $this->assertTrue((new MimePart('Test', 'text/plain', null, 'test.txt'))->isAttachment());
  }

  #[Test]
  public function is_inline() {
    $this->assertTrue((new MimePart('Test', 'text/plain'))->isInline());
  }

  #[Test]
  public function given_a_name_is_not_inline() {
    $this->assertTrue((new MimePart('Test', 'text/plain', null, 'test.txt'))->isAttachment());
  }

  #[Test]
  public function base64_encoded() {
    $this->assertEquals('Test', (new MimePart('VGVzdA==', 'text/plain', MIME_ENC_BASE64))->getBody(true));
  }

  #[Test]
  public function quoted_printable() {
    $this->assertEquals('Täst', (new MimePart('T=C3=A4st', 'text/plain', MIME_ENC_QPRINT))->getBody(true));
  }

  #[Test]
  public function eightbit_encoding() {
    $this->assertEquals('Test', (new MimePart('Test', 'text/plain', MIME_ENC_8BIT))->getBody(true));
  }

  #[Test]
  public function sevenbit_encoding() {
    $this->assertEquals('Test', (new MimePart('Test', 'text/plain', MIME_ENC_7BIT))->getBody(true));
  }
}