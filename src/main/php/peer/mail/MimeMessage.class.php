<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses(
    'peer.mail.Message',
    'peer.mail.MimePart',
    'peer.mail.MultiPart'
  );
  
  /**
   * Mime message
   *
   * To generate a mime-message containing a body with
   * text/plain, text/html and an image used within the html part
   * one may use a construct as follows:
   *
   * <code>
   *   uses(
   *     'peer.mail.MultiPart',
   *     'peer.mail.MimeMessage',
   *     'peer.mail.InternetAddress',
   *     'io.FileUtil',
   *     'io.File',
   *     'util.MimeType'
   *   );
   * 
   *   $message= new MimeMessage();
   *   $message->setFrom(new InternetAddress('alex.kiesel@xp-framework.net'));
   *   $message->addRecipient(TO, new InternetAddress('timm.friebe@xp-framework.net'));
   * 
   *   $message->setSubject('HTML Mail');
   * 
   *   // MultiPart containing the text/plain and text/html parts
   *   $mime= new MultiPart();
   * 
   *   // Create the image
   *   $imageUrl= 'attention.gif';
   *   $image= new MimePart();
   *   $image->setDisposition(MIME_DISPOSITION_INLINE);
   *   $image->setEncoding(MIME_ENC_BASE64);
   *   $image->setFilename(NULL);
   *   $image->setName(NULL);
   *   $image->setBody(
   *     chunk_split(base64_encode(FileUtil::getContents(new File($imageUrl)))), 
   *     TRUE);
   *   $image->setContentType(MimeType::getByFilename($imageUrl));
   *   $image->charset= '';
   *   $image->generateContentId();
   * 
   *   // Create text/plain part
   *   $text= new MimePart(
   *     'Your mail client is not able to display html messages.', 
   *     'text/plain'
   *   );
   *   $text->setDisposition(MIME_DISPOSITION_INLINE);
   * 
   *   // Create text/html part (images must be referred to by their content-id)
   *   $html= new MimePart(sprintf(
   *       '<html><body><h1>You see html.</h1><img src="%s" border="0"/></body></html>',
   *       'cid:'.$image->getContentId()
   *     ),
   *     'text/html'
   *   );
   *   $html->setDisposition(MIME_DISPOSITION_INLINE);
   * 
   *   // Add to the multipart
   *   $mime->addPart($text);
   *   $mime->addPart($html);
   * 
   *   $message->addPart($mime);
   *   $message->addPart($image);
   * 
   *   // This is very important to not see the image as an attachment
   *   $message->setContentType('multipart/related; type="multipart/alternative"');
   * </code>
   *
   * @test     xp://net.xp_framework.unittest.peer.mail.MimeMessageTest
   * @purpose  MimeMessage class
   */
  class MimeMessage extends Message {
    public
      $parts     = array(),
      $encoding  = '',
      $boundary  = '';
      
    public
      $_ofs      = 0;

    /**
     * Constructor. Also generates a boundary of the form
     * <pre>
     * ----=_Part_10424693873e22d20b43b490.00112051
     * </pre>
     *
     */
    public function __construct($uid= -1) {
      $this->setBoundary('----=_Part_'.uniqid(time(), TRUE));
      $this->headers[HEADER_MIMEVER]= $this->mimever;
      $this->contenttype= 'multipart/mixed';
      parent::__construct($uid);
    }
    
    /**
     * Checks whether the mime message is simple (contains only one
     * inline mime part) or complex (contains multiple or not inline
     * mime parts)
     *
     * @return bool
     */
    protected function isSimpleMimePart() {
      return (1 === ($size= sizeof($this->parts)) && $this->parts[0]->isInline());
    }

    /**
     * Add a Mime Part
     *
     * @param   peer.mail.MimePart part
     * @return  peer.mail.MimePart the part added
     * @throws  lang.IllegalArgumentException if part argument is not a peer.mail.MimePart
     */
    public function addPart(MimePart $part) {
      $this->parts[]= $part;
      return $part;
    }
    
    /**
     * Set boundary and updates Content-Type header. Note: A boundary is generated 
     * upon instanciation, so this is usually not needed!
     *
     * @param   string b the new boundary
     */
    public function setBoundary($b) {
      $this->boundary= $b;
    }

    /**
     * Get boundary
     *
     * @return  string
     */
    public function getBoundary() {
      return $this->boundary;
    }
    
    /**
     * Get content type
     *
     * @return  string
     */
    public function getContentType() {
      return $this->isSimpleMimePart()
        ? $this->parts[0]->getContenttype() :
        parent::getContentType();
    }
      
    /**
     * Return headers as string
     *
     * @return  string headers
     */
    public function getHeaderString() {
      if ($this->isSimpleMimePart()) {
        $this->setContenttype($this->parts[0]->getContenttype());
        if ($this->parts[0] instanceof MultiPart)
          $this->setBoundary($this->parts[0]->getBoundary());

        $this->charset= $this->parts[0]->charset;
      }
      
      return parent::getHeaderString();
    }
    
    /**
     * Private helper method
     *
     * @param   array parameters
     * @param   string val
     * @return  var value or FALSE if not found
     */
    protected function _lookupattr($parameters, $val) {
      if (!is_array($parameters)) return FALSE;
      
      for ($i= 0, $s= sizeof($parameters); $i < $s; $i++) {
        if (0 == strcasecmp($parameters[$i]->attribute, $val)) {
          return $parameters[$i]->value;
        }
      }
      
      return FALSE;
    }
    
    /**
     * Private helper method
     *
     * @param   &peer.mail.MimePart[] parts
     * @param   array p structure parts as retrieved from cclient lib
     * @param   string id default '' part id
     */
    protected function _recurseparts(&$parts, $p, $id= '') {
      static $types= array(
        'text',
        'multipart',
        'message',
        'application',
        'audio',
        'image',
        'video',
        'unknown'
      );
      static $encodings= array(
        '7bit',
        '8bit',
        'binary',
        'base64',
        'quoted-printable',
        NULL
      );
      
      for ($i= 0, $s= sizeof($p); $i < $s; $i++) {
        $pid= sprintf('%s%d', $id, $i+ 1);

        if (empty($p[$i]->parts)) {
          $part= new MimePart();
        } else {
          $part= new MultiPart();
        }
        $part->setName($this->_lookupattr(@$p[$i]->dparameters, 'NAME'));
        $part->setEncoding($encodings[$p[$i]->encoding]);
        $part->setContentType($types[$p[$i]->type].'/'.strtolower($p[$i]->subtype));
        $part->setCharset($this->_lookupattr(@$p[$i]->parameters, 'CHARSET') ?: '');
        $part->setDisposition($p[$i]->ifdisposition ? MIME_DISPOSITION_ATTACHMENT : MIME_DISPOSITION_INLINE);
        if (FALSE !== ($f= $this->_lookupattr(@$p[$i]->dparameters, 'FILENAME'))) {
          $part->setFilename($f);
        }

        $part->id= $pid;
        
        // We can retrieve the body here since the message has been read anyway
        if (!empty($p[$i]->parts)) {
          if ($p[$i]->ifsubtype) switch ($p[$i]->subtype) {
            case 'MIXED': 
              $pid= substr($pid, 0, -2); 
              break;
              
            default: // Nothing
          }

          // Recurse through parts
          $this->_recurseparts($part->parts, $p[$i]->parts, $pid.'.');
          
          // Multipart -> part.0 are the headers
          $part->parts[0]->setHeaderString($this->folder->getMessagePart($this->uid, $pid.'.0'));
        } else {
          $part->body= $this->folder->getMessagePart($this->uid, $pid);
        }
        
        $part->folder= $this->folder;
        $parts[]= $part;
      }
    }
    
    /**
     * Get a part
     *
     * @param   int id default -1
     * @return  peer.mail.MimePart part
     */
    public function getPart($id= -1) {
      $this->_parts();
      
      // Iterative use
      if (-1 == $id) $id= $this->_ofs++;
      
      // EOL
      if (!isset($this->parts[$id])) {
        $this->_ofs= 0;
        return NULL;
      }
      
      return $this->parts[$id];
    }

    /**
     * Get all parts
     *
     * @return  peer.mail.MimePart[]
     */
    public function getParts() {
      $this->_parts();
      return $this->parts;
    }

    /**
     * Get structure from folder
     *
     * @return  bool got parts
     */    
    protected function _parts() {
      if ((NULL === $this->folder) || (!empty($this->parts))) return FALSE;
      
      $struct= $this->folder->getMessageStruct($this->uid);
      if (!$struct->parts) {
        return FALSE;
      }
      
      $this->_recurseparts($this->parts, $struct->parts);
    }
    
    /**
     * Returns the content-type header. This includes a
     * boundary information if one is set and a charset
     * information if one is set.
     *
     * @return  string header
     */
    protected function _getContenttypeHeaderString() {
      return $this->getContentType().(
        $this->isSimpleMimePart() ? '' : '; boundary="'.$this->getBoundary().'"'
      ).(empty($this->charset) 
        ? '' 
        : ";\n\tcharset=\"".$this->charset.'"'
      );
    }    

    /**
     * Get message body.
     *
     * @param   decode default FALSE
     * @return  string
     */
    public function getBody($decode= FALSE) {
      $this->_parts();

      if ($this->isSimpleMimePart()) return $this->parts[0]->getBody($decode);
      
      $size= sizeof($this->parts);
      $body= "This is a multi-part message in MIME format.\n\n";
      for ($i= 0; $i < $size; $i++) {
        $body.= (
          '--'.$this->boundary."\n".
          $this->parts[$i]->getHeaderString().
          "\n".
          rtrim($this->parts[$i]->getBody(), "\n").
          "\n\n"
        );
      }
      
      // End boundary
      return $body.'--'.$this->boundary."--\n";
    }

    /**
     * Sets message body. Adds a text/plain mimepart.
     *
     * @param   string str Message Text
     * @return  peer.mail.MimePart
     */
    public function setBody($str) {
      $this->parts= array(new MimePart($str, 'text/plain', $this->encoding));
      return $this->parts[0];
    }
  }
?>
