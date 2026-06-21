<?php namespace peer\mail\unittest;

use peer\mail\InternetAddress;
use test\{Assert, Test, Values};

class InternetAddressTest {

  #[Test]
  public function create_with_address() {
    $address= new InternetAddress('kiesel@example.com');
    Assert::equals('kiesel', $address->localpart);
    Assert::equals('example.com', $address->domain);
  }
  
  #[Test, Values(['Alex Kiesel <kiesel@example.com>', 'kiesel@example.com (Alex Kiesel)', '"Alex Kiesel" <kiesel@example.com>', '=?iso-8859-1?Q?Alex_Kiesel?= <kiesel@example.com>', '=?utf-8?Q?Alex_Kiesel?= <kiesel@example.com>', '=?utf-8?B?QWxleCBLaWVzZWw?= <kiesel@example.com>',])]
  public function parse_from_string($string) {
    $address= InternetAddress::fromString($string);
    Assert::equals('Alex Kiesel', $address->personal);
    Assert::equals('kiesel', $address->localpart);
    Assert::equals('example.com', $address->domain);
  }

  #[Test]
  public function parse_from_string_without_personal() {
    $address= InternetAddress::fromString('kiesel@example.com');
    Assert::equals('kiesel', $address->localpart);
    Assert::equals('example.com', $address->domain);
  }

  #[Test]
  public function colons_are_escaped_in_output() {
    Assert::equals(
      '=?utf-8?Q?I=3A=3ADev?= <idev@example.com>',
      (new InternetAddress('idev@example.com', 'I::Dev'))->toString()
    );
  }

  #[Test]
  public function umlaut_are_escaped_in_output() {
    Assert::equals(
      '=?utf-8?Q?M=C3=BCcke?= <muecke@example.com>',
      (new InternetAddress('muecke@example.com', 'Mücke'))->toString()
    );
  }

  #[Test]
  public function umlaut_are_escaped_and_iso_encoded_in_output() {
    Assert::equals(
      '=?iso-8859-1?Q?M=FCcke?= <muecke@example.com>',
      (new InternetAddress('muecke@example.com', 'Mücke'))->toString('iso-8859-1')
    );
  }

  #[Test]
  public function umlaut_are_escaped_and_utf8_encoded_in_output() {
    Assert::equals(
      '=?utf-8?Q?M=C3=BCcke?= <muecke@example.com>',
      (new InternetAddress('muecke@example.com', 'Mücke'))->toString('utf-8')
    );
  }
  
  #[Test]
  public function space_characters_are_escaped_in_output() {
    Assert::equals(
      '=?utf-8?Q?Alex_Kiesel?= <kiesel@example.com>', 
      (new InternetAddress('kiesel@example.com', 'Alex Kiesel'))->toString()
    );
  }
  
  #[Test]
  public function get_address_in_raw_format() {
    Assert::equals(
      'kiesel@example.com', 
      (new InternetAddress('kiesel@example.com', 'Alex Kiesel'))->getAddress()
    );
  }    
}