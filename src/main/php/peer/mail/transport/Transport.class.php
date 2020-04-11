<?php namespace peer\mail\transport;
 
use lang\IllegalArgumentException;
use peer\mail\Message;
use util\log\{LogCategory, Traceable};

/**
 * Abstract base class for mail transport
 */
abstract class Transport implements Traceable, \lang\Closeable {
  public $cat= null;

  /**
   * Connect to this transport
   *
   * @return self
   */
  public function connect() { return $this; }
  
  /**
   * Close connection
   *
   * @return void
   */
  public function close() { }

  /**
   * Send a message
   *
   * @param  peer.mail.Message $message
   * @return bool success
   * @throws peer.mail.TransportException
   */
  public abstract function send(Message $message);
  
  /**
   * Set a LogCategory for tracing communication
   *
   * @param  util.log.LogCategory $cat pass NULL to stop tracing
   * @return void
   * @throws lang.IllegalArgumentException in case a of a type mismatch
   */
  public function setTrace($cat) {
    if (null !== $cat && !$cat instanceof LogCategory) {
      throw new IllegalArgumentException('Expected a LogCategory, have '.typeof($cat)->getName());
    }
    
    $this->cat= $cat;
  }
  
  /**
   * Trace function
   *
   * @param  var... arguments
   * @return void
   */
  protected function trace() {
    if (null === $this->cat) return;
    $args= func_get_args();
    call_user_func_array([$this->cat, 'debug'], $args);
  }
}