Mail for XP
===========

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/mail.svg)](http://travis-ci.org/xp-framework/mail)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_5plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Supports HHVM 3.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/hhvm-3_4plus.png)](http://hhvm.com/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/mail/version.png)](https://packagist.org/packages/xp-framework/mail)

E-Mail APIs, POP3, IMAP, MailDir, SMTP support.

## Creating an email

```php
use peer\mail\Message;
use peer\mail\InternetAddress;

$msg= new Message();
$msg->setFrom(new InternetAddress('friebe@example.com', 'Timm Friebe'));
$msg->addRecipient(TO, new InternetAddress('foo@bar.baz', 'Foo Bar'));
$msg->addRecipient(CC, new InternetAddress('timm@foo.bar', 'Timm Friebe'));
$msg->setHeader('X-Binford', '6100 (more power)');
$msg->setSubject('Hello world');
$msg->setBody('Testmail');
```

## Sending email

```php
use peer\mail\transport\MailTransport;
use peer\mail\transport\TransportException;

$smtp= new MailTransport();
try {
  $smtp->connect();
  $smtp->send($msg);
} catch (TransportException $e) {
  $e->printStackTrace();
}

$smtp->close();
```

## Using an SMTP server

```php
use peer\mail\transport\SmtpConnection;
use peer\mail\transport\TransportException;

$smtp= new SmtpConnection('esmtp://user:pass@mail.example.com:25/?auth=login');
try {
  $smtp->connect();
  $smtp->send($msg);
} catch (TransportException $e) {
  $e->printStackTrace();
}

$smtp->close();
```