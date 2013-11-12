<?php namespace peer\mail;

/**
 * Interface for messaging addresses
 *
 * @see      xp://peer.mail.InternetAddress
 */
interface MessagingAddress {
  
  /**
   * Retrieve address
   *
   * @return  string
   */
  public function getAddress();
}
