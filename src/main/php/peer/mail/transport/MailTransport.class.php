<?php namespace peer\mail\transport;
 
use peer\mail\Message;
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
    $parameters= [];

  /**
   * Connect to this transport
   *
   * @param   string dsn default NULL additional parameters for sendmail
   * @return  self
   */
  public function connect($dsn= null) { 
    $this->parameters= $dsn;
    return $this;
  }
  
  /**
   * Send a message
   *
   * @param   peer.mail.Message message the Message object to send
   * @return  bool success
   */
  public function send(Message $message) { 
  
    // Sanity check: Do we have at least one recipient?
    $to= '';
    for ($i= 0, $s= sizeof($message->to); $i < $s; $i++) {
      if (!$message->to[$i] instanceof \peer\mail\InternetAddress) continue; // Ignore!
      $to.= $message->to[$i]->toString($message->getCharset()).', ';
    }
    if (empty($to)) {
      throw new TransportException('No recipients defined');
    }
    
    // Copy message and unset To / Subject. PHPs mail() function will add them
    // to the mail twice, otherwise
    $tmp= clone $message;
    unset($tmp->to);
    unset($tmp->subject);
    
    if (false === mail(
      substr($to, 0, -2),
      QuotedPrintable::encode($message->getSubject(), $message->getCharset()),
      strtr($message->getBody(), [
        "\r\n" => "\n",
        "\r"   => "\n"
      ]),
      rtrim($tmp->getHeaderString(), "\n"),
      $this->parameters
    )) {
      throw new TransportException(
        'Could not send mail to '.\xp::stringOf($message->to[0]), 
        new \io\IOException('Call to mail() failed')
      );
    }
    return true;
  }
}