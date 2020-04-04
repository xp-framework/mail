E-Mail APIs, POP3, IMAP, MailDir, SMTP support for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 8.0.2 / 2020-04-04

* Made compatible with XP 10 - @thekid

## 8.0.1 / 2018-08-25

* Made compatible with `xp-framework/logging` version 9.0.0 - @thekid

## 8.0.0 / 2017-06-19

* **Heads up:** Drop PHP 5.5 support - @thekid
* Added forward compatibility with XP 9.0.0 - @thekid

## 7.3.3 / 2017-05-20

* Refactored code to use `typeof()` instead of `xp::typeOf()`, see
  https://github.com/xp-framework/rfc/issues/323
  (@thekid)

## 7.3.2 / 2016-10-20

* Fixed 8bit encoding constant `MIME_ENC_8BIT` - @thekid
* Made header parsing for mime parts consistent with message header
  parsing; added a bunch of tests to verify various situations.
  (@thekid)

## 7.3.1 / 2016-10-20

* Fixed `lang.IndexOutOfBoundsException (Undefined offset: 1)` error
  when header line did not contain any value after the colon
  (@thekid)

## 7.3.0 / 2016-10-16

* Added `newFolder()` and `removeFolder()` methods to `CclientStore`
  (@thekid)

## 7.2.0 / 2016-10-09

* Merged pull request #1: SMTP connection class
  - Heads up: Deprecated SmtpTransport in favor of SmtpConnection
  - Added support for STARTTLS
  - Added support for SMTPS
  (@thekid)

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
