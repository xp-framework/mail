<?php namespace peer\mail\unittest;

use peer\mail\store\ImapStore;
use test\verify\Runtime;
use test\{Assert, Before, Test};

/**
 * TestCase for peer.mail.store.ImapStore
 *
 * @see  xp://peer.mail.store.ImapStore
 * @see  xp://peer.mail.store.CclientStore
 */
#[Runtime(extensions: ['imap'])]
class ImapStoreTest {
  private $fixture;

  /** @return void */
  #[Before]
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
  
  #[Test]
  public function connectImap() {
    $this->fixture->connect('imap://example.org');
    Assert::equals('{example.org:143/imap}', $this->fixture->connect['mbx']);
  }
  
  #[Test]
  public function connectImaps() {
    $this->fixture->connect('imaps://example.org');
    Assert::equals('{example.org:993/imap/ssl}', $this->fixture->connect['mbx']);
  }

  #[Test]
  public function connectImapt() {
    $this->fixture->connect('imapt://example.org');
    Assert::equals('{example.org:993/imap/tls}', $this->fixture->connect['mbx']);
  }

  #[Test]
  public function connectImapsNoValidate() {
    $this->fixture->connect('imaps://example.org?novalidate-cert=1');
    Assert::equals('{example.org:993/imap/ssl/novalidate-cert}', $this->fixture->connect['mbx']);
  }
  
  #[Test]
  public function connectImaptNoValidate() {
    $this->fixture->connect('imapt://example.org?novalidate-cert=1');
    Assert::equals('{example.org:993/imap/tls/novalidate-cert}', $this->fixture->connect['mbx']);
  }
  
  #[Test]
  public function connectImapNonStandardPort() {
    $this->fixture->connect('imap://example.org:566');
    Assert::equals('{example.org:566/imap}', $this->fixture->connect['mbx']);
  }
}