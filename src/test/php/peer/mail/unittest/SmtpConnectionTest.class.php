<?php namespace peer\mail\unittest;

use lang\{FormatException, IllegalArgumentException};
use peer\mail\transport\{SmtpConnection, TransportException};
use peer\mail\{InternetAddress, Message};
use peer\{Socket, SocketException, URL};
use unittest\{Expect, Test, Values};

class SmtpConnectionTest extends \unittest\TestCase {

  #[Test, Values(['smtp://smtp.example.com', 'smtp://smtp.example.com:2525', 'esmtp://user:pass@smtp.example.com:25/?auth=plain', 'esmtp://user:pass@smtp.example.com:25/?auth=login', 'esmtp://user:pass@smtp.example.com:25/?starttls=never', 'esmtp://user:pass@smtp.example.com:25/?starttls=auto', 'esmtp://user:pass@smtp.example.com:25/?starttls=always'])]
  public function can_create_with($dsn) {
    new SmtpConnection($dsn);
  }

  #[Test]
  public function can_create_with_url() {
    new SmtpConnection(new URL('smtp://localhost'));
  }

  #[Test, Expect(FormatException::class), Values(['', 'localhost:25', 'smtp://'])]
  public function malformed($dsn) {
    new SmtpConnection($dsn);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unsupported_scheme() {
    new SmtpConnection('http://localhost:25');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unsupported_authentication() {
    new SmtpConnection('smtp://user:pass@localhost:25/?auth=illegal');
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unsupported_starttls() {
    new SmtpConnection('esmtp://localhost:25/?starttls=illegal');
  }

  #[Test, Values(['smtp://smtp.example.com', 'smtp://smtp.example.com:25', 'esmtp://user:pass@smtp.example.com/?auth=plain', 'esmtp://user:pass@smtp.example.com:25/?auth=login'])]
  public function server($dsn) {
    $this->assertEquals('smtp.example.com:25', (new SmtpConnection($dsn))->server());
  }

  #[Test]
  public function intially_not_connected() {
    $this->assertFalse((new SmtpConnection('smtp://test'))->connected());
  }

  #[Test]
  public function connected() {
    $conn= new SmtpConnection('smtp://test?helo=tester', new class('test', 25) extends Socket {
      private $connected= false;
      private $answers= [
        '220 test (mreue101) ESMTP Service ready',
        '250 test Hello tester'
      ];

      public function isConnected() { return $this->connected; }
      public function connect($timeout= 2) { $this->connected= true; }
      public function close() { $this->connected= false; }
      public function read($n= 8192) { return array_shift($this->answers)."\r\n"; }
      public function write($bytes) { }
    });
    $conn->connect();
    $this->assertTrue($conn->connected());
  }

  #[Test, Expect(TransportException::class)]
  public function connection_failed() {
    $conn= new SmtpConnection('smtp://test?helo=tester', new class('test', 25) extends Socket {
      public function isConnected() { return false; }
      public function connect($timeout= 2) { throw new SocketException('Cannot connect'); }
      public function close() { }
      public function read($n= 8192) { }
      public function write($bytes) { }
    });
    $conn->connect();
  }

  #[Test]
  public function smtp_dialog() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250 test Hello tester'
    ];
    $conn= new SmtpConnection('smtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
    $conn->close();

    $this->assertEquals(['HELO tester', 'QUIT'], $commands);
    $this->assertEquals('test (mreue101) ESMTP Service ready', $conn->banner());
    $this->assertEquals([], $conn->capabilities());
  }

  #[Test]
  public function esmtp_dialog() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250-test Hello tester',
      '250-SIZE 69920427',
      '250 AUTH LOGIN PLAIN'
    ];
    $conn= new SmtpConnection('esmtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
    $conn->close();

    $this->assertEquals(['EHLO tester', 'QUIT'], $commands);
    $this->assertEquals('test (mreue101) ESMTP Service ready', $conn->banner());
    $this->assertEquals(['SIZE 69920427', 'AUTH LOGIN PLAIN'], $conn->capabilities());
  }

  #[Test]
  public function auth_plain() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250-test Hello tester',
      '250 AUTH LOGIN PLAIN',
      '235 Authenticated'
    ];
    $conn= new SmtpConnection('esmtp://user:pass@test?auth=plain&helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
    $conn->close();

    $this->assertEquals(['EHLO tester', 'AUTH PLAIN AHVzZXIAcGFzcw==', 'QUIT'], $commands);
  }

  #[Test]
  public function auth_login() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250-test Hello tester',
      '250 AUTH LOGIN PLAIN',
      '334 VXNlcm5hbWU6',
      '334 UGFzc3dvcmQ6',
      '235 Authenticated'
    ];
    $conn= new SmtpConnection('esmtp://user:pass@test?auth=login&helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
    $conn->close();

    $this->assertEquals(['EHLO tester', 'AUTH LOGIN', 'dXNlcg==', 'cGFzcw==', 'QUIT'], $commands);
  }

  #[Test, Expect(TransportException::class)]
  public function dialog_refused() {
    $commands= [];
    $answers= [
      '500 I do not like you'
    ];
    $conn= new SmtpConnection('smtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
  }

  #[Test, Expect(TransportException::class)]
  public function authentication_refused() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250-test Hello tester',
      '250 AUTH LOGIN PLAIN',
      '535 Authentication credentials invalid'
    ];
    $conn= new SmtpConnection('esmtp://user:pass@test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
  }

  #[Test, Expect(TransportException::class)]
  public function starttls_refused() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250-test Hello tester',
      '250 STARTTLS',
      '454 TLS not available due to temporary reason'
    ];
    $conn= new SmtpConnection('esmtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
  }

  #[Test, Expect(TransportException::class)]
  public function starttls_failure() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250-test Hello tester',
      '250 STARTTLS',
      '220 Go ahead'
    ];
    $conn= new SmtpConnection('esmtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));
    $conn->connect();
  }

  #[Test]
  public function sending() {
    $commands= [];
    $answers= [
      '220 test (mreue101) ESMTP Service ready',
      '250 test Hello tester',
      '250 Requested mail action okay, completed',
      '250 OK',
      '354 Start mail input; end with <CRLF>.<CRLF>',
      '250 Requested mail action okay, completed: id=0MRQIm-1bQdor2WfA-00Sb7F'
    ];
    $conn= new SmtpConnection('smtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'connected'   => false,
      'isConnected' => function() { return $this->connected; },
      'connect'     => function($timeout= 2) { $this->connected= true; },
      'close'       => function() { $this->connected= false; },
      'read'        => function($n= 8192) use(&$answers) { return array_shift($answers)."\r\n"; },
      'write'       => function($bytes) use(&$commands) { $commands[]= rtrim($bytes, "\r\n"); }
    ]));

    $conn->connect();
    $conn->send(with (new Message(), function($self) {
      $self->setFrom(new InternetAddress('sender@example.com'));
      $self->addRecipient(TO, new InternetAddress('recipient@example.com'));
      $self->setSubject('Test');
      $self->setBody('Test');
      return $self;
    }));
    $conn->close();

    $this->assertEquals(
      ['HELO tester', 'MAIL FROM: sender@example.com', 'RCPT TO: recipient@example.com', 'DATA', null, 'Test', '.', 'QUIT'],
      array_merge(array_slice($commands, 0, 4), [null], array_slice($commands, 5))
    );
  }
}