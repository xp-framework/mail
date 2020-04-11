<?php namespace peer\mail\unittest;

use peer\mail\store\ImapStore;
use unittest\TestCase;
use unittest\actions\ExtensionAvailable;

/**
 * TestCase for peer.mail.store.ImapStore
 *
 * @see  xp://peer.mail.store.ImapStore
 * @see  xp://peer.mail.store.CclientStore
 */
#[@action(new ExtensionAvailable('imap'))]
class ImapStoreTest extends TestCase {

  /**
   * Sets up test case
   */
  public function setUp() {
    $this->fixture= newinstance(ImapStore::class, [], [
      'connect' => [],
      '_connect' => function($mbx, $user, $pass, $flags) {
        $this->connect= [
          'mbx'   => $mbx,
          'user'  => $user,
          'pass'  => $pass,
          'flags' => $flags
        ];
        return true;
      }
    ]);
  }
  
  /**
   * Test parsing of DSN for imap
   *
   */
  #[@test]
  public function connectImap() {
    $this->fixture->connect('imap://example.org');
    $this->assertEquals('{example.org:143/imap}', $this->fixture->connect['mbx']);
  }
  
  /**
   * Test parsing of DSN for imaps
   *
   */
  #[@test]
  public function connectImaps() {
    $this->fixture->connect('imaps://example.org');
    $this->assertEquals('{example.org:993/imap/ssl}', $this->fixture->connect['mbx']);
  }

  /**
   * Test parsing of DSN for imapt
   *
   */
  #[@test]
  public function connectImapt() {
    $this->fixture->connect('imapt://example.org');
    $this->assertEquals('{example.org:993/imap/tls}', $this->fixture->connect['mbx']);
  }

  /**
   * Test parsing of DSN for imaps without validating certificate
   *
   */
  #[@test]
  public function connectImapsNoValidate() {
    $this->fixture->connect('imaps://example.org?novalidate-cert=1');
    $this->assertEquals('{example.org:993/imap/ssl/novalidate-cert}', $this->fixture->connect['mbx']);
  }
  
  /**
   * Test parsing of DSN for imapt without validating certificate
   *
   */
  #[@test]
  public function connectImaptNoValidate() {
    $this->fixture->connect('imapt://example.org?novalidate-cert=1');
    $this->assertEquals('{example.org:993/imap/tls/novalidate-cert}', $this->fixture->connect['mbx']);
  }
  
  /**
   * Test parsing of DSN with nondefault port
   *
   */
  #[@test]
  public function connectImapNonStandardPort() {
    $this->fixture->connect('imap://example.org:566');
    $this->assertEquals('{example.org:566/imap}', $this->fixture->connect['mbx']);
  }
}