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

  /** @return void */
  public function setUp() {
    $this->fixture= new class() extends ImapStore {
      public $connect;

      public function _connect($mbx, $user, $pass, $flags) {
        $this->connect= [
          'mbx'   => $mbx,
          'user'  => $user,
          'pass'  => $pass,
          'flags' => $flags
        ];
        return true;
      }
    };
  }
  
  #[@test]
  public function connectImap() {
    $this->fixture->connect('imap://example.org');
    $this->assertEquals('{example.org:143/imap}', $this->fixture->connect['mbx']);
  }
  
  #[@test]
  public function connectImaps() {
    $this->fixture->connect('imaps://example.org');
    $this->assertEquals('{example.org:993/imap/ssl}', $this->fixture->connect['mbx']);
  }

  #[@test]
  public function connectImapt() {
    $this->fixture->connect('imapt://example.org');
    $this->assertEquals('{example.org:993/imap/tls}', $this->fixture->connect['mbx']);
  }

  #[@test]
  public function connectImapsNoValidate() {
    $this->fixture->connect('imaps://example.org?novalidate-cert=1');
    $this->assertEquals('{example.org:993/imap/ssl/novalidate-cert}', $this->fixture->connect['mbx']);
  }
  
  #[@test]
  public function connectImaptNoValidate() {
    $this->fixture->connect('imapt://example.org?novalidate-cert=1');
    $this->assertEquals('{example.org:993/imap/tls/novalidate-cert}', $this->fixture->connect['mbx']);
  }
  
  #[@test]
  public function connectImapNonStandardPort() {
    $this->fixture->connect('imap://example.org:566');
    $this->assertEquals('{example.org:566/imap}', $this->fixture->connect['mbx']);
  }
}