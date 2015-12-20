<?php namespace peer\mail\store;

use io\Folder;
use io\File;


/**
 * Mail store
 *
 * @see     http://cr.yp.to/proto/maildir.html
 * @see     http://www.courier-mta.org/maildir.html
 * @purpose Incarnation of abstract class MailStore for Maildir
 * @experimental
 */
class MaildirStore extends MailStore {
  public 
    $cache= null,
    $currentFolder= null;
  
  public
    $_folder= null,
    $_root=   null;
    
  /**
   * Constructs a MaildirStore object. 
   *
   */
  public function __construct($cache= null) {
    parent::__construct($cache);
  }
  
  /**
   * Opens a Maildir store. If no parameter is given, this opens
   * the users default mailbox located in $HOME/Maildir/.
   *
   * @param   string folder default NULL
   * @return  bool success
   */    
  public function open($folder= null) {
    if (null === $folder)
      $folder= getenv ('HOME').DIRECTORY_SEPARATOR.'Maildir';
    
    try {
      $this->_folder= new Folder ($folder);
      $this->_folder->open();
    } catch (\io\IOException $e) {
      $this->_folder= null;
      return $e;
    }
    
    $this->currentfolder= '';
    $this->_root= realpath ($folder);
    
    return true;
  }
  
  /**
   * Returns the non-global foldername.
   *
   * @param   string folder
   * @return  string realfolder
   */
  protected function _getFolderName($folder) {
    return str_replace (
      $this->_root,
      '',
      realpath ($folder)
    );
  }    
  
  /**
   * Closes currently open mailstore.
   *
   * @return  bool
   */
  public function close() {
    return $this->_folder->close();
  }

  /**
   * Check whether a folder exists
   *
   * @param   string name
   * @return  bool
   */
  public function hasFolder($name) {
    $f= new Folder ($this->_folder->getURI().DIRECTORY_SEPARATOR.$name);
    return $f->exists();
  }

  /**
   * Opens a subfolder of the current folder and returns
   * an object of that mailbox.
   *
   * @param   string foldername
   * @return  peer.mail.MailFolder folder;
   */    
  public function getFolder($name) {
    $f= new Folder ($this->_folder->getURI().DIRECTORY_SEPARATOR.$name);
    if (!$f->exists())
      throw (new \peer\mail\MessagingException (
        'Maildir does not exist: '.$f->getURI()
      ));
    
    $mf= new \peer\mail\MailFolder ($this, $name);
    return $mf;
  }
  
  /**
   * Returns a list of all subfolders in current folder.
   *
   * @return  array* folders array of peer.mail.MailFolder objects
   */
  public function getFolders() {
    $f= [];
    while ($entry= $this->_folder->getEntry()) {
      if (is_dir ($this->_folder->getURI().DIRECTORY_SEPARATOR.$entry)) {
        if ('.' != $entry{0} || '.' == $entry || '..' == $entry) {
          $f[]= new \peer\mail\MailFolder (
            $this,
            $this->_getFolderName ($entry)
          );
        }
      }
    }
    
    return $f;
  }
  
  /**
   * Opens a folder.
   *
   * @param   peer.mail.MailFolder folder
   * @param   bool readonly default FALSE
   * @return  bool success
   * @throws  lang.IllegalAccessException if another folder is still open
   * @throws  io.IOException if folder cannot be opened
   */
  public function openFolder($f, $readonly= false) {
    // Is it already open?
    if ($this->currentfolder === $f->name)
      return true;

    // Only one open folder at a time
    if (null !== $this->currentfolder) {
      trigger_error('Currently open Folder: '.$this->currentfolder, E_USER_NOTICE);
      throw new \lang\IllegalAccessException(
        'There can only be one open folder at a time. Close the currently open folder first.',
        $f->name
      );      
    }
    
    $nf= new Folder ($this->_root.DIRECTORY_SEPARATOR.$f->name);
    $nf->open();
    
    $this->_folder= $nf;
    
    $this->currentfolder= $f->name;
    return true;
  }

  /**
   * Closes an open folder.
   *
   * @param   peer.mail.MailFolder folder
   * @return  bool success
   * @throws  lang.IllegalArgumentException if folder is not opened folder
   */    
  public function closeFolder($f) {
    // Is it already open?
    if ($this->currentfolder !== $f->name)
      throw (new \lang\IllegalArgumentException (
        'Cannot close non-opened folder!',
        $f->name
      ));
    
    $this->_folder->close();
    $this->currentfolder= null;
    return true;
  }
  
  /**
   * Gets the count of messages with speciefied attribute
   * or all messages when no attribute was specified
   *
   * @param   peer.mail.Mailfolder f
   * @param   int attr default 0xFFFF
   * @return  int count
   */    
  public function getMessageCount($f, $attr= 0xFFFF) {
    $this->openFolder ($f);
    $f= new Folder ($f->name.DIRECTORY_SEPARATOR.'cur');
    if (!$f->exists())
      return 0;
    
    $cnt= 0;
    $f->open();
    while ($e= $f->getEntry()) {
      if ($attr & $this->_getMailFlags ($e)) $cnt++;
    }
    $f->close();
    return $cnt;
  }
  
  /**
   * Returns the URI to a specific message in a Maildir. This is the
   * absolute path to that file.
   *
   * @param   peer.mail.MailFolder folder
   * @param   int number
   * @return  string uri
   */    
  protected function _getMessageURI($f, $nr) {
    $this->_folder->rewind();

    while (false !== ($entry= $this->_folder->getEntry()) && $nr <= $i++) {
      if ($nr == $i) 
        return $f->getURI().DIRECTORY_SEPARATOR.$entry;
    }
    return false;
  }
  
  /**
   * Returns the flags of the specified message given in the filename.
   *
   * @param string filename
   * @return int flags
   */    
  protected function _getMailFlags($filename) {
    static
      $maildirFlagMatrix= array (
        'R' => MAIL_FLAG_ANSWERED,
        'S' => MAIL_FLAG_SEEN,
        'T' => MAIL_FLAG_DELETED,
        'D' => MAIL_FLAG_DRAFT,
        'F' => MAIL_FLAG_TAGGED
      );
  
    $flagString= substr ($e, strpos ('2,'));
    for ($i= 0; $i < count ($flagString); $i++)
      $flags|= $maildirFlagMatrix[$flagString{$i}];

    return $flags;
  }
  
  /**
   * Reads the whole message, applies the header information,
   * sets the body as a plain text (thus does not parse any
   * MIME-Information and returns the created Message object.
   *
   * @param   string filename
   * @return  peer.mail.Message
   * @throws  io.IOException if file cannot be read
   */    
  protected function _readMessageRaw($filename) {
    $header= '';
    $body= '';
    $f= new File ($filename);
    $f->open ();
    $d= $f->read ($f->size());
    $f->close();
  
    if (false === ($hdrEnd= strpos ($d, "\n\r\n\r")))
      $hdrEnd= 0;
      
    $h= substr ($c, 0, $hdrEnd);
    $b= substr ($c, $hdrEnd);
    
    $msg= new \peer\mail\Message();
    $msg->setHdrString ($h);
    $msg->setBody ($b);
    
    return $msg;
  }
  
  /**
   * Returns an array of messages specified by the numbers in the
   * argument
   *
   * @param   peer.mail.MailFolder folder
   * @param   var* msgnums
   * @return  array messages
   */    
  public function getMessages($f) {
    $this->openFolder ($f);
    if (1 == func_num_args()) {
      $count= $this->getMessageCount ();
      $msgnums= range (1, $count);
    } else {
      $msgnums= [];
      for ($i= 1, $s= func_num_args(); $i < $s; $i++) {
        $arg= func_get_arg($i);
        $msgnums= array_merge($msgnums, (array)$arg);
      }
    }
    
    $messages= [];
    foreach ($msgnums as $msg) {
      $filename= $this->_getMessageURI($f, $msg);
      $flags= $this->_getMailFlags($filename);
      
      try {
        $msg= $this->_readMessageRaw($filename);
      } catch (\io\IOException $e) {
      
        // Ignore any errors
        continue;
      }
      
      $messages[]= $msg;
    }
    
    return $messages;
  }
  
}
