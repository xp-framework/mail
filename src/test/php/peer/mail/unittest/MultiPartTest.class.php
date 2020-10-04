<?php namespace peer\mail\unittest;

use peer\mail\{MimePart, MultiPart};
use unittest\Test;

class MultiPartTest extends \unittest\TestCase {

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
    $this->assertEquals([$text, $html], (new MultiPart($text, $html))->getParts());
  }

  #[Test]
  public function text_part() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    $this->assertEquals($text, (new MultiPart($text, $html))->getPart(0));
  }

  #[Test]
  public function html_part() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    $this->assertEquals($html, (new MultiPart($text, $html))->getPart(1));
  }

  #[Test]
  public function non_existant_part() {
    $text= new MimePart('Text', 'text/plain');
    $html= new MimePart('<html/>', 'text/html');
    $this->assertEquals(null, (new MultiPart($text, $html))->getPart(2));
  }
}