<?php namespace peer\mail\unittest;

use peer\mail\MimePart;

class MimePartTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new MimePart();
  }

  #[@test]
  public function can_create_with_body() {
    new MimePart('Test');
  }

  #[@test]
  public function can_create_with_body_and_content_type() {
    new MimePart('Test', 'text/plain');
  }

  #[@test]
  public function body() {
    $this->assertEquals('Test', (new MimePart('Test', 'text/plain'))->getBody());
  }

  #[@test]
  public function content_type() {
    $this->assertEquals('text/plain', (new MimePart('Test', 'text/plain'))->getContentType());
  }

  #[@test]
  public function is_not_an_attachment() {
    $this->assertFalse((new MimePart('Test', 'text/plain'))->isAttachment());
  }

  #[@test]
  public function given_a_name_is_an_attachment() {
    $this->assertTrue((new MimePart('Test', 'text/plain', null, 'test.txt'))->isAttachment());
  }

  #[@test]
  public function is_inline() {
    $this->assertTrue((new MimePart('Test', 'text/plain'))->isInline());
  }

  #[@test]
  public function given_a_name_is_not_inline() {
    $this->assertTrue((new MimePart('Test', 'text/plain', null, 'test.txt'))->isAttachment());
  }

  #[@test]
  public function base64_encoded() {
    $this->assertEquals('Test', (new MimePart('VGVzdA==', 'text/plain', MIME_ENC_BASE64))->getBody(true));
  }

  #[@test]
  public function quoted_printable() {
    $this->assertEquals('Täst', (new MimePart('T=C3=A4st', 'text/plain', MIME_ENC_QPRINT))->getBody(true));
  }

  #[@test]
  public function eightbit_encoding() {
    $this->assertEquals('Test', (new MimePart('Test', 'text/plain', MIME_ENC_8BIT))->getBody(true));
  }

  #[@test]
  public function sevenbit_encoding() {
    $this->assertEquals('Test', (new MimePart('Test', 'text/plain', MIME_ENC_7BIT))->getBody(true));
  }
}