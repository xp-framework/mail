<?php namespace peer\mail\transport;
 
use util\log\Traceable;


/**
 * Abstract base class for mail transport
 *
 * @purpose  Provide an interface
 */
abstract class Transport extends \lang\Object implements Traceable {
  public
    $cat    = null;

  /**
   * Connect to this transport
   *
   * @param   string dsn default NULL
   */
  public function connect($dsn= null) { }
  
  /**
   * Close connection
   *
   */
  public function close() { }

  /**
   * Send a message
   *
   * @param   peer.mail.Message message the Message object to send
   * @throws  peer.mail.transport.TransportException to indicate an error occured
   */
  public abstract function send($message);
  
  /**
   * Set a LogCategory for tracing communication
   *
   * @param   util.log.LogCategory cat a LogCategory object to which communication
   *          information will be passed to or NULL to stop tracing
   * @return  util.log.LogCategory
   * @throws  lang.IllegalArgumentException in case a of a type mismatch
   */
  public function setTrace($cat) {
    if (null !== $cat && !$cat instanceof \util\log\LogCategory) {
      throw new \lang\IllegalArgumentException('Argument passed is not a LogCategory');
    }
    
    $this->cat= $cat;
  }
  
  /**
   * Trace function
   *
   * @param   var* arguments
   */
  protected function trace() {
    if (null == $this->cat) return;
    $args= func_get_args();
    call_user_func_array(array($this->cat, 'debug'), $args);
  }

} 
