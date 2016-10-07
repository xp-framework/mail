E-Mail APIs, POP3, IMAP, MailDir, SMTP support for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 7.1.3 / 2016-10-06

* Fixed exception handling in mail transports - @thekid

## 7.1.2 / 2016-10-06

* Fixed fetching mails from server (Error `Class 'MimeMessage' not found`)
  (@thekid)

## 7.1.1 / 2016-08-29

* Made compatible with xp-framework/networking v8.0.0 - @thekid

## 7.1.0 / 2016-08-28

* Added forward compatibility with XP 8.0.0 - @thekid

## 7.0.0 / 2016-02-21

* **Adopted semantic versioning. See xp-framework/rfc#300** - @thekid 
* Added version compatibility with XP 7 - @thekid

## 6.1.1 / 2016-01-23

* Fix code to use `nameof()` instead of the deprecated `getClassName()`
  method from lang.Generic. See xp-framework/core#120
  (@thekid)

## 6.1.0 / 2015-12-20

* **Heads up: Dropped PHP 5.4 support**. *Note: As the main source is not
  touched, unofficial PHP 5.4 support is still available though not tested
  with Travis-CI*.
  (@thekid)

## 6.0.2 / 2015-07-12

* Added forward compatibility with XP 6.4.0 - @thekid
* Added preliminary PHP 7 support (alpha2, beta1) - @thekid

## 6.0.1 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 6.0.0 / 2015-01-10

* Heads up: Converted classes to PHP 5.3 namespaces - (@thekid)
