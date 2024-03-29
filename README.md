Mail for XP
===========

[![Build status on GitHub](https://github.com/xp-framework/mail/workflows/Tests/badge.svg)](https://github.com/xp-framework/mail/actions)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/mail/version.png)](https://packagist.org/packages/xp-framework/mail)

E-Mail APIs, POP3, IMAP, MailDir, SMTP support.

## Creating an email

```php
use peer\mail\{Message, InternetAddress};

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
use peer\mail\transport\{MailTransport, TransportException};

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
use peer\mail\transport\{SmtpConnection, TransportException};

$smtp= new SmtpConnection('esmtp://user:pass@mail.example.com:25/?auth=login');
try {
  $smtp->connect();
  $smtp->send($msg);
} catch (TransportException $e) {
  $e->printStackTrace();
}

$smtp->close();
```