<?php namespace peer\mail\transport;
 
use text\encode\QuotedPrintable;

 
/**
 * Mail transport via built-in mail() function
 *
 * Example:
 * <code>
 *   // [...build messages array...]
 *   $t= new MailTransport();
 *   $t->connect();       // use $t->connect('-odq'); for queuing
 *
 *   for ($i= 0, $size= sizeof($message); $i < $size; $i++) {
 *     $t->send($message);
 *   }
 *   $t->close();
 * </code>
 *
 * @see      php://mail
 * @purpose  Provide transport via mail()
 */
class MailTransport extends Transport {
  protected
    $parameters= array();

  /**
   * Connect to this transport
   *
   * @param   string dsn default NULL additional parameters for sendmail
   * @return  bool success
   */
  public function connect($dsn= null) { 
    $this->parameters= $dsn;
    return true;
  }
  
  /**
   * Close connection
   *
   * @return  bool success
   */
  public function close() { 
    return true;
  }

  /**
   * Send a message
   *
   * @param   peer.mail.Message message the Message object to send
   * @return  bool success
   */
  public function send($message) { 
  
    // Sanity check: Is this a message?
    if (!$message instanceof \peer\mail\Message) {
      throw new \TransportException(
        'Can only send messages (given: '.\xp::typeOf($message).')',
        new \lang\IllegalArgumentException('Parameter message is not a Message object')
      );
    }
    
    // Sanity check: Do we have at least one recipient?
    $to= '';
    for ($i= 0, $s= sizeof($message->to); $i < $s; $i++) {
      if (!$message->to[$i] instanceof \peer\mail\InternetAddress) continue; // Ignore!
      $to.= $message->to[$i]->toString($message->getCharset()).', ';
    }
    if (empty($to)) {
      throw new \TransportException(
        'No recipients defined (recipients[0]: '.\xp::typeOf($message->to[0]),
        new \lang\IllegalArgumentException('Recipient #0 is not an InternetAddress object')
      );
    }
    
    // Copy message and unset To / Subject. PHPs mail() function will add them
    // to the mail twice, otherwise
    $tmp= clone $message;
    unset($tmp->to);
    unset($tmp->subject);
    
    if (false === mail(
      substr($to, 0, -2),
      QuotedPrintable::encode($message->getSubject(), $message->getCharset()),
      strtr($message->getBody(), array(
        "\r\n" => "\n",
        "\r"   => "\n"
      )),
      rtrim($tmp->getHeaderString(), "\n"),
      $this->parameters
    )) {
      throw new \TransportException(
        'Could not send mail to '.\xp::stringOf($message->to[0]), 
        new \io\IOException('Call to mail() failed')
      );
    }
    return true;
  }
}
