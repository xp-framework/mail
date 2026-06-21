<?php namespace peer\mail\unittest;

use peer\mail\MimePart;
use test\{Assert, Test};

class MimePartTest {

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
    Assert::equals('Test', (new MimePart('Test', 'text/plain'))->getBody());
  }

  #[Test]
  public function content_type() {
    Assert::equals('text/plain', (new MimePart('Test', 'text/plain'))->getContentType());
  }

  #[Test]
  public function name() {
    Assert::equals('test.txt', (new MimePart('Test', 'text/plain', null, 'test.txt'))->getName());
  }

  #[Test]
  public function encoding() {
    Assert::equals(MIME_ENC_BASE64, (new MimePart('VGVzdA==', 'text/plain', MIME_ENC_BASE64))->getEncoding());
  }

  #[Test]
  public function is_not_an_attachment() {
    Assert::false((new MimePart('Test', 'text/plain'))->isAttachment());
  }

  #[Test]
  public function given_a_name_is_an_attachment() {
    Assert::true((new MimePart('Test', 'text/plain', null, 'test.txt'))->isAttachment());
  }

  #[Test]
  public function is_inline() {
    Assert::true((new MimePart('Test', 'text/plain'))->isInline());
  }

  #[Test]
  public function given_a_name_is_not_inline() {
    Assert::true((new MimePart('Test', 'text/plain', null, 'test.txt'))->isAttachment());
  }

  #[Test]
  public function base64_encoded() {
    Assert::equals('Test', (new MimePart('VGVzdA==', 'text/plain', MIME_ENC_BASE64))->getBody(true));
  }

  #[Test]
  public function quoted_printable() {
    Assert::equals('Täst', (new MimePart('T=C3=A4st', 'text/plain', MIME_ENC_QPRINT))->getBody(true));
  }

  #[Test]
  public function eightbit_encoding() {
    Assert::equals('Test', (new MimePart('Test', 'text/plain', MIME_ENC_8BIT))->getBody(true));
  }

  #[Test]
  public function sevenbit_encoding() {
    Assert::equals('Test', (new MimePart('Test', 'text/plain', MIME_ENC_7BIT))->getBody(true));
  }
}