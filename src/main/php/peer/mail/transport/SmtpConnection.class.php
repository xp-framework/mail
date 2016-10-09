<?php namespace peer\mail\transport;
 
use peer\URL;
use peer\Socket;
use peer\ProtocolException;
use lang\Throwable;
use lang\IllegalArgumentException;
use peer\mail\Message;

/**
 * Mail transport via SMTP
 *
 * ```php
 * $smtp= new SmtpConnection('smtp://localhost:25');
 * $smtp->setTrace((new LogCategory())->withAppender(new ConsoleAppender()));
 *
 * $smtp->connect();
 * $smtp->send($msg);
 * $smtp->close();
 * ```
 *
 * @see   rfc://2822
 * @see   rfc://2554
 * @see   rfc://1891 SMTP Service Extension for Delivery Status Notifications
 * @see   http://www.sendmail.org/~ca/email/authrealms.html
 * @test  xp://peer.mail.unittest.SmtpConnectionTest
 */
class SmtpConnection extends Transport {
  const AUTH_PLAIN = 'plain';
  const AUTH_LOGIN = 'login';

  private $init, $host, $port, $helo, $socket;
  private $user, $pass, $auth= null;
  private $banner= null, $capabilities= [];

  /**
   * Creates a new SMTP connection
   *
   * DSN parameter examples:
   * 
   * - smtp://localhost
   * - smtp://localhost:2525
   * - smtp://localhost:25/?helo=hostname.used.in.ehlo
   * - esmtp://user:pass@smtp.example.com:25/?auth=plain
   * - esmtp://user:pass@smtp.example.com:25/?auth=login
   *
   * @param  string|peer.URL $dsn
   * @throws lang.IllegalArgumentException
   */
  public function __construct($dsn, $socket= null) {
    $u= $dsn instanceof URL ? $dsn : new URL($dsn);

    $this->init= $this->init($u->getScheme());
    $this->host= $u->getHost();
    $this->port= $u->getPort(25);
    $this->helo= $u->getParam('helo', gethostname());

    if ($this->user= $u->getUser()) {
      $this->pass= $u->getPassword();
      $this->auth= $this->auth($u->getParam('auth', self::AUTH_PLAIN));
    }

    $this->socket= $socket ?: new Socket($this->host, $this->port);
  }

  /** @return string */
  public function server() { return $this->host.':'.$this->port; }

  /** @return bool */
  public function connected() { return $this->socket->isConnected(); }

  /** @return string */
  public function banner() { return $this->banner; }

  /** @return string[] */
  public function capabilities() { return $this->capabilities; }

  /**
   * Setup initialization method
   *
   * @param  string $scheme
   * @return function(): void
   */
  private function init($scheme) {
    switch (strtolower($scheme)) {
      case 'esmtp':
        return function() {
          $answer= $this->command('EHLO %s', $this->helo, 250);
          $this->capabilities= [];
          while ($answer && $buf= $this->socket->read()) {
            sscanf($buf, "%d%[^\r]", $code, $capability);
            $this->trace('+++', $code, $capability);
            $this->capabilities[]= substr($capability, 1);
            if ('-' !== $capability{0}) break;
          }
        };

      case 'smtp':
        return function() {
          $this->command('HELO %s', [$this->helo], 250);
        };

      default: throw new IllegalArgumentException('Scheme "'.$scheme.'" not supported');
    }    
  }

  /**
   * Setup authentication method
   *
   * @param  string $method
   * @return function(): void
   */
  private function auth($method) {
    switch (strtolower($method)) {
      case self::AUTH_LOGIN:
        return function() {
          $this->command('AUTH LOGIN', [], 334); 
          $this->command('%s', [base64_encode($this->user)], 334);
          $this->command('%s', [base64_encode($this->pass)], 235);
        };
        
      case self::AUTH_PLAIN:
        return function() {
          $this->command('AUTH PLAIN %s', [base64_encode("\0".$this->user."\0".$this->pass)], 235);
        };
        
      default: throw new IllegalArgumentException('Authentication method '.$method.' not supported');
    }
  }

  /**
   * Private helper method
   *
   * @param  string $fmt or NULL to indicate not to write any data
   * @param  string[] $args arguments for sprintf-string fmt
   * @param  int|int[] $expect int possible returncodes or NULL to indicate not to read any data
   * @return string buf
   */
  protected function command($fmt, $args, $expect) {
    $expect= (array)$expect;

    // Send command
    if (null !== $fmt) {
      $cmd= vsprintf($fmt, $args);
      $this->trace('>>>', $cmd);
      if (false === $this->socket->write($cmd."\r\n")) return false;

      // Expecting data?
      if (null === $expect[0]) return '';
    }
    
    // Read
    if (false === ($buf= substr($this->socket->read(), 0, -2))) return false;
    $this->trace('<<<', $buf);
    
    // Got expected data?
    $code= substr($buf, 0, 3);
    if (!in_array($code, $expect)) {
      throw new ProtocolException(
        'Expected '.implode(' or ', $expect).', have '.$code.' ["'.$buf.'"]'
      );
    }
    
    return $buf;
  }

  /**
   * Connect to this transport
   *
   * @return self
   */
  public function connect() { 
    if ($this->socket->isConnected()) return;

    try {
      $this->socket->connect();
      $this->banner= substr($this->command(null, [], 220), 4);
      if ($f= $this->init) $f();
      if ($f= $this->auth) $f();
    } catch (Throwable $e) {
      $this->socket->close();
      throw new TransportException('Connect failed', $e);
    }

    return $this;
  }

  /**
   * Close connection
   *
   * @return void
   */
  public function close() {
    if (!$this->socket->isConnected()) return;

    try {
      $this->socket->write("QUIT\r\n"); 
      $this->socket->close();
    } catch (Throwable $e) {
      // Ignore
    }
  }

  /**
   * Send a message
   *
   * @param  peer.mail.Message $message
   * @return bool success
   * @throws peer.mail.TransportException
   */
  public function send(Message $message) {
    try {
      $this->command('MAIL FROM: %s', $message->from->getAddress(), 250);
      foreach ([TO, CC, BCC] as $type) {
        foreach ($message->getRecipients($type) as $r) {
          $this->command('RCPT TO: %s', $r->getAddress(), [250, 251]);
        }
      }

      // Content: Headers and body. Make sure lines containing a dot by itself are
      // properly escaped.
      $this->command('DATA', [], 354);
      $this->command('%s', [$message->getHeaderString()], null);
      $this->command('%s', [preg_replace('/(^|[\r\n])([\.]+)([\r\n]|$)/', '$1.$2$3', $message->getBody())], null);
      $this->command('.', [], 250);
    } catch (Throwable $e) {
      throw new TransportException('Sending message failed', $e);
    }
    
    return true;
  }
}
