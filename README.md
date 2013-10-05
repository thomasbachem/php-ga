Server-Side Google Analytics PHP Client
===========================================================================================

- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
**Oct 5, 2013:** php-ga was ported from [Google Code](https://code.google.com/p/php-ga/)
where it has 8,000+ downloads and 160+ stares.

*NOTE: php-ga is no longer maintained as Google finally released an official server-side
tracking API: Measurement Protocol! I couldn't find any well-implemented client library for
PHP yet, so feel free to help make php-ga 2.0 a Measurement Protocol PHP client library.*
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

php-ga basically is ga.js in PHP: An implementation of a generic server-side Google
Analytics client in PHP that implements nearly every parameter and tracking feature of the
original GA Javascript client. That means you can send data directly from your servers to
to Google Analytics – bypassing the user's browser.

I love Google Analytics and want to contribute to its community with this PHP client
implementation. It is intended to be used stand-alone or in addition to an existing
Javascript library implementation.

It's PHP, but porting it to other languages (see below for a Python port) should be easy.
Building this library involved weeks of documentation reading, googling and testing -
therefore its source code is thorougly well-documented.

The PHP client has nothing todo with the
[Data Export](http://code.google.com/apis/analytics/docs/gdata/gdataDeveloperGuide.html)
or [Management](http://code.google.com/apis/analytics/docs/mgmt/home.html) APIs, although
you can of course use them in combination.


Requirements
-------------------------------------------------------------------------------------------

Requires PHP 5.3+ as namespaces and closures are used. Has no other dependencies and can be
used independantly from any framework or whatsoever environment.


Supported Features
-------------------------------------------------------------------------------------------

The current release is based on version 5.2.5 of the official Javascript client library,
see `CHANGELOG` file for details.

- Pageview Tracking
- Event Tracking
- Custom Variable Tracking
- Ecommerce Tracking
- Campaign Tracking
- Social Interaction Tracking
- Site Speed Tracking


Gotchas
-------------------------------------------------------------------------------------------

- **100% namespaced OOP**

  As a matter of course.

- **Completely abstracted from any enviroment**

  Doesn't rely on any globals like $_SERVER, PHP sessions or whatsoever - implement it the
  way you want.

- **High-Performance Tracking**

  Can be configured to enqueue requests via register_shutdown_function() and to use
  non-blocking requests.

- **Probably the most comprehensive technical documentation of GA**

  More than 50% of all source code lines are PHPDoc and inline comments!


Caveats
-------------------------------------------------------------------------------------------

- **Google Analytics' geo location functionalities won't work**

  Native geo location features like the worldmap view won't work anymore as they rely
  solely on the IP address of the GA client - which will always be the one of your
  server(s) when using this library.


Usage Example
-------------------------------------------------------------------------------------------

A very basic page view tracking example:

```php
use UnitedPrototype\GoogleAnalytics;

// Initilize GA Tracker
$tracker = new GoogleAnalytics\Tracker('UA-12345678-9', 'example.com');

// Assemble Visitor information
// (could also get unserialized from database)
$visitor = new GoogleAnalytics\Visitor();
$visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
$visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
$visitor->setScreenResolution('1024x768');

// Assemble Session information
// (could also get unserialized from PHP session)
$session = new GoogleAnalytics\Session();

// Assemble Page information
$page = new GoogleAnalytics\Page('/page.html');
$page->setTitle('My Page');

// Track page view
$tracker->trackPageview($page, $session, $visitor);
```


Links
-------------------------------------------------------------------------------------------

- [Python port: PYGA](https://github.com/kra3/py-ga-mob)
- [php-ga questions on Stack Overflow](http://stackoverflow.com/search?q=%22php-ga%22)

Thanks to Matt Clarke for two great articles/tutorials:

- [TechPad: Developing a cookie-less Google Analytics implementation](http://techpad.co.uk/content.php?sid=205)
- [TechPad: Turn Google Analytics into an inventory profiling system](http://techpad.co.uk/content.php?sid=209)


Disclaimer
-------------------------------------------------------------------------------------------

Google Analytics is a registered trademark of Google Inc.