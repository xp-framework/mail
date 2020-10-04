<?php namespace peer\mail\unittest;

use peer\mail\{InternetAddress, Message};
use unittest\{Test, Values};
use util\Date;

class MessageHeaderParsingTest extends HeaderParsingTest {

  /**
   * Parse a string containing message headers
   *
   * @param  string $str
   * @return peer.mail.Message
   */
  protected function parse($str) {
    $m= new Message();
    $m->setHeaderString($str."\n\n");
    return $m;
  }

  #[Test]
  public function from_email() {
    $this->assertEquals(
      new InternetAddress('a@example.com'),
      $this->parse('From: a@example.com')->getFrom()
    );
  }

  #[Test, Values([['to', 'To: b@example.com'], ['cc', 'Cc: b@example.com']])]
  public function recipient_email($type, $header) {
    $this->assertEquals(
      [new InternetAddress('b@example.com')],
      $this->parse($header)->getRecipients($type)
    );
  }

  #[Test, Values([['to', 'To: a@example.com, b@example.com'], ['cc', 'Cc: a@example.com, b@example.com']])]
  public function recipient_emails_separated_by_commas($type, $header) {
    $this->assertEquals(
      [new InternetAddress('a@example.com'), new InternetAddress('b@example.com')],
      $this->parse($header)->getRecipients($type)
    );
  }

  #[Test, Values([['to', "To: a@example.com\nTo: b@example.com"], ['cc', "Cc: a@example.com\nCc: b@example.com"]])]
  public function multiple_recipient_headers($type, $header) {
    $this->assertEquals(
      [new InternetAddress('a@example.com'), new InternetAddress('b@example.com')],
      $this->parse($header)->getRecipients($type)
    );
  }

  #[Test, Values([['to', 'To: A <a@example.com>, B <b@example.com>'], ['cc', 'Cc: A <a@example.com>, B <b@example.com>']])]
  public function recipient_emails_with_names($type, $header) {
    $this->assertEquals(
      [new InternetAddress('a@example.com', 'A'), new InternetAddress('b@example.com', 'B')],
      $this->parse($header)->getRecipients($type)
    );
  }

  #[Test, Values([['to', 'To: "A, B" <a@example.com>, "B, A" <b@example.com>'], ['cc', 'Cc: "A, B" <a@example.com>, "B, A" <b@example.com>']])]
  public function recipient_emails_with_quoted_names($type, $header) {
    $this->assertEquals(
      [new InternetAddress('a@example.com', 'A, B'), new InternetAddress('b@example.com', 'B, A')],
      $this->parse($header)->getRecipients($type)
    );
  }

  #[Test]
  public function subject() {
    $this->assertEquals(
      'Hello World',
      $this->parse('Subject: Hello World')->getSubject()
    );
  }

  #[Test]
  public function quoted_printable_iso_encoded_subject() {
    $this->assertEquals(
      'Hello World',
      $this->parse('Subject: =?iso-8859-1?Q?Hello_World?=')->getSubject()
    );
  }

  #[Test]
  public function quoted_printable_utf8_encoded_subject() {
    $this->assertEquals(
      'Hällo',
      $this->parse('Subject: =?utf-8?Q?Hällo?=')->getSubject()
    );
  }

  #[Test, Values(["Subject: =?utf-8?Q?Hällo?=\n\t=?utf-8?Q?Wörld?=", "Subject: =?utf-8?Q?Hällo?=\n =?utf-8?Q?Wörld?="])]
  public function quoted_printable_multiline_subject($subject) {
    $this->assertEquals(
      'Hällo Wörld',
      $this->parse($subject)->getSubject()
    );
  }

  #[Test, Values(['Content-Type: text/plain', 'Content-type: text/plain', 'content-type: text/plain', 'CONTENT-TYPE: text/plain'])]
  public function content_type_parsed($variant) {
    $this->assertEquals('text/plain', $this->parse($variant)->getContentType());
  }

  #[Test, Values(['Mime-Version: 1.0', 'Mime-version: 1.0', 'mime-version: 1.0', 'MIME-VERSION: 1.0'])]
  public function mime_version_parsed($variant) {
    $this->assertEquals('1.0', $this->parse($variant)->getMimeVersion());
  }

  #[Test, Values(['Content-Transfer-Encoding: 7bit', 'Content-transfer-encoding: 7bit', 'content-transfer-encoding: 7bit', 'CONTENT-TRANSFER-ENCODING: 7bit'])]
  public function content_transfer_encoding_parsed($variant) {
    $this->assertEquals('7bit', $this->parse($variant)->getEncoding());
  }

  #[Test, Values(['Date: Sat, 7 Jun 2005 12:34:34 -0600 (MDT)', 'date: Sat, 7 Jun 2005 12:34:34 -0600 (MDT)', 'DATE: Sat, 7 Jun 2005 12:34:34 -0600 (MDT)'])]
  public function date_parsed($variant) {
    $this->assertEquals(new Date('Sat, 7 Jun 2005 12:34:34 -0600 (MDT)'), $this->parse($variant)->getDate());
  }

  #[Test, Values(['Message-ID: <20050329231145.62086.mail@mail.emailprovider.com>', 'Message-id: <20050329231145.62086.mail@mail.emailprovider.com>', 'message-id: <20050329231145.62086.mail@mail.emailprovider.com>', 'MESSAGE-ID: <20050329231145.62086.mail@mail.emailprovider.com>'])]
  public function message_id_parsed($variant) {
    $this->assertEquals('<20050329231145.62086.mail@mail.emailprovider.com>', $this->parse($variant)->getMessageId());
  }

  #[Test, Values(['X-Priority: 3 (Normal)', 'X-priority: 3 (Normal)', 'x-priority: 3 (Normal)', 'X-PRIORITY: 3 (Normal)'])]
  public function priority_parsed($variant) {
    $this->assertEquals(3, $this->parse($variant)->priority);
  }
}