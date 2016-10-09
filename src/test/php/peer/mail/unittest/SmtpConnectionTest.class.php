<?php namespace peer\mail\unittest;

use lang\FormatException;
use lang\IllegalArgumentException;
use peer\mail\transport\SmtpConnection;
use peer\mail\transport\TransportException;
use peer\URL;
use peer\Socket;
use peer\SocketException;
use unittest\actions\VerifyThat;

class SmtpConnectionTest extends \unittest\TestCase {

  #[@test, @values([
  #  'smtp://smtp.example.com',
  #  'smtp://smtp.example.com:2525',
  #  'esmtp://user:pass@smtp.example.com:25/?auth=plain',
  #  'esmtp://user:pass@smtp.example.com:25/?auth=login',
  #  'esmtp://user:pass@smtp.example.com:25/?starttls=never',
  #  'esmtp://user:pass@smtp.example.com:25/?starttls=auto',
  #  'esmtp://user:pass@smtp.example.com:25/?starttls=always'
  #])]
  public function can_create_with($dsn) {
    new SmtpConnection($dsn);
  }

  #[@test]
  public function can_create_with_url() {
    new SmtpConnection(new URL('smtp://localhost'));
  }

  #[@test, @expect(FormatException::class), @values([
  #  '',
  #  'localhost:25',
  #  'smtp://'
  #])]
  public function malformed($dsn) {
    new SmtpConnection($dsn);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unsupported_scheme() {
    new SmtpConnection('http://localhost:25');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unsupported_authentication() {
    new SmtpConnection('smtp://user:pass@localhost:25/?auth=illegal');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unsupported_starttls() {
    new SmtpConnection('esmtp://localhost:25/?starttls=illegal');
  }

  #[@test, @values([
  #  'smtp://smtp.example.com',
  #  'smtp://smtp.example.com:25',
  #  'esmtp://user:pass@smtp.example.com/?auth=plain',
  #  'esmtp://user:pass@smtp.example.com:25/?auth=login'
  #])]
  public function server($dsn) {
    $this->assertEquals('smtp.example.com:25', (new SmtpConnection($dsn))->server());
  }

  #[@test]
  public function intially_not_connected() {
    $this->assertFalse((new SmtpConnection('smtp://test'))->connected());
  }

  #[@test]
  public function connected() {
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
      'write'       => function($bytes) { }
    ]));
    $conn->connect();
    $this->assertTrue($conn->connected());
  }

  #[@test, @expect(TransportException::class)]
  public function connection_failed() {
    $conn= new SmtpConnection('smtp://test?helo=tester', newinstance(Socket::class, ['test', 25], [
      'isConnected' => function() { return false; },
      'connect'     => function($timeout= 2) { throw new SocketException('Cannot connect'); },
      'close'       => function() { },
      'read'        => function($n= 8192) { },
      'write'       => function($bytes) { }
    ]));
    $conn->connect();
  }

  #[@test]
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

  #[@test]
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

  #[@test]
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

  #[@test]
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

  #[@test, @expect(TransportException::class)]
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

  #[@test, @expect(TransportException::class)]
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

  #[@test, @expect(TransportException::class), @action(new VerifyThat(function() {
  #  return function_exists('stream_socket_enable_crypto');
  #}))]
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

  #[@test, @expect(TransportException::class), @action(new VerifyThat(function() {
  #  return function_exists('stream_socket_enable_crypto');
  #}))]
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
}