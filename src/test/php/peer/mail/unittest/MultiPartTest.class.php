<?php namespace peer\mail\unittest;

use peer\mail\{MimePart, MultiPart};
use test\{Assert, Test};

class MultiPartTest {

  #[Test]
  public function can_create() {
    new MultiPart();
  }

  #[Test]
  public function can_create_with_one() {
    new MultiPart(new MimePart());
  }

  #[Test]
  public function can_create_with_two() {
    new MultiPart(new MimePart(), new MimePart());
  }

  #[Test]
  public function all_parts() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    Assert::equals([$text, $html], (new MultiPart($text, $html))->getParts());
  }

  #[Test]
  public function text_part() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    Assert::equals($text, (new MultiPart($text, $html))->getPart(0));
  }

  #[Test]
  public function html_part() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    Assert::equals($html, (new MultiPart($text, $html))->getPart(1));
  }

  #[Test]
  public function non_existant_part() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    Assert::equals(null, (new MultiPart($text, $html))->getPart(2));
  }
}