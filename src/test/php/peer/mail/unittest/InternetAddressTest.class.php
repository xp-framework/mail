<?php namespace peer\mail\unittest;

use peer\mail\InternetAddress;
use unittest\{Test, TestCase, Values};

/**
 * Testcase for InternetAddress class
 */
class InternetAddressTest extends TestCase {

  #[Test]
  public function create_with_address() {
    $address= new InternetAddress('kiesel@example.com');
    $this->assertEquals('kiesel', $address->localpart);
    $this->assertEquals('example.com', $address->domain);
  }
  
  #[Test, Values(['Alex Kiesel <kiesel@example.com>', 'kiesel@example.com (Alex Kiesel)', '"Alex Kiesel" <kiesel@example.com>', '=?iso-8859-1?Q?Alex_Kiesel?= <kiesel@example.com>', '=?utf-8?Q?Alex_Kiesel?= <kiesel@example.com>', '=?utf-8?B?QWxleCBLaWVzZWw?= <kiesel@example.com>',])]
  public function parse_from_string($string) {
    $address= InternetAddress::fromString($string);
    $this->assertEquals('Alex Kiesel', $address->personal);
    $this->assertEquals('kiesel', $address->localpart);
    $this->assertEquals('example.com', $address->domain);
  }

  #[Test]
  public function parse_from_string_without_personal() {
    $address= InternetAddress::fromString('kiesel@example.com');
    $this->assertEquals('kiesel', $address->localpart);
    $this->assertEquals('example.com', $address->domain);
  }

  #[Test]
  public function colons_are_escaped_in_output() {
    $this->assertEquals(
      '=?utf-8?Q?I=3A=3ADev?= <idev@example.com>',
      (new InternetAddress('idev@example.com', 'I::Dev'))->toString()
    );
  }

  #[Test]
  public function umlaut_are_escaped_in_output() {
    $this->assertEquals(
      '=?utf-8?Q?M=C3=BCcke?= <muecke@example.com>',
      (new InternetAddress('muecke@example.com', 'Mücke'))->toString()
    );
  }

  #[Test]
  public function umlaut_are_escaped_and_iso_encoded_in_output() {
    $this->assertEquals(
      '=?iso-8859-1?Q?M=FCcke?= <muecke@example.com>',
      (new InternetAddress('muecke@example.com', 'Mücke'))->toString('iso-8859-1')
    );
  }

  #[Test]
  public function umlaut_are_escaped_and_utf8_encoded_in_output() {
    $this->assertEquals(
      '=?utf-8?Q?M=C3=BCcke?= <muecke@example.com>',
      (new InternetAddress('muecke@example.com', 'Mücke'))->toString('utf-8')
    );
  }
  
  #[Test]
  public function space_characters_are_escaped_in_output() {
    $this->assertEquals(
      '=?utf-8?Q?Alex_Kiesel?= <kiesel@example.com>', 
      (new InternetAddress('kiesel@example.com', 'Alex Kiesel'))->toString()
    );
  }
  
  #[Test]
  public function get_address_in_raw_format() {
    $this->assertEquals(
      'kiesel@example.com', 
      (new InternetAddress('kiesel@example.com', 'Alex Kiesel'))->getAddress()
    );
  }    
}