# Flatten

[![Build Status](http://img.shields.io/travis/Anahkiasen/flatten.svg?style=flat)](https://travis-ci.org/Anahkiasen/flatten)
[![Latest Stable Version](http://img.shields.io/packagist/v/anahkiasen/flatten.svg?style=flat)](https://packagist.org/packages/anahkiasen/flatten)
[![Total Downloads](http://img.shields.io/packagist/dt/anahkiasen/flatten.svg?style=flat)](https://packagist.org/packages/anahkiasen/flatten)
[![Scrutinizer Quality Score](http://img.shields.io/scrutinizer/g/anahkiasen/flatten.svg?style=flat)](https://scrutinizer-ci.com/g/anahkiasen/flatten/)
[![Code Coverage](http://img.shields.io/scrutinizer/coverage/g/anahkiasen/flatten.svg?style=flat)](https://scrutinizer-ci.com/g/anahkiasen/flatten/)
[![Dependency Status](https://www.versioneye.com/user/projects/544d46789fc4d555ff000071/badge.svg?style=flat)](https://www.versioneye.com/user/projects/544d46789fc4d555ff000071)
[![Support via Gittip](http://img.shields.io/gittip/Anahkiasen.svg?style=flat)](https://www.gittip.com/Anahkiasen/)

Flatten is a powerful cache system for caching pages at runtime.
What it does is quite simple : you tell him which page are to be cached, when the cache is to be flushed, and from there Flatten handles it all. It will quietly flatten your pages to plain HTML and store them. That whay if an user visit a page that has already been flattened, all the PHP is highjacked to instead display a simple HTML page.
This will provide an essential boost to your application's speed, as your page's cache only gets refreshed when a change is made to the data it displays.

## Setup

### Installation

Flatten installs just like any other package, via Composer : `composer require anahkiasen/flatten`.

Then if you're using Laravel, add Flatten's Service Provider to you `config/app.php` file :

```php
'Flatten\FlattenServiceProvider',
```

And its facade :

```php
'Flatten' => 'Flatten\Facades\Flatten',
```

### Configuration

All the options are explained in the **config.php** configuration file. You can publish it via `artisan config:publish anahkiasen/flatten`.

Here is a preview of the configuration options available in said file :

```php
// The default period during which a cached page should be kept (in minutes)
// 0 means the page never gets refreshed by itself
'lifetime'     => 0,

// The different pages to be ignored when caching
// They're all regexes so go crazy
'ignore'       => array(),

// List only specific pages to cache, useful if you have a lot of
// pages you don't want to see cached
// The ignored pages will still be substracted from this array
'only'         => array(),

// An array of string or variables to add to the salt being used
// to differentiate pages
'saltshaker'   => array(),
```

## Usage

The pages are cached according to two parameters : their path and their method. Only GET requests get cached as all the other methods are dynamic by nature.
All of the calls you'll make, will be to the `Flatten\Facades\Flatten` facade.
Query strings are taken into account in the cache and pages will different query strings will have different caches.

### Building

Flatten can cache all authorized pages in your application via the `artisan flatten:build` command. It will crawl your application and go from page to page, caching all the pages you allowed him to.

### Flushing

Sometimes you may want to flush a specific page or pattern. If per example you cache your users's profiles, you may want to flush those when the user edit its informations.
You can do so via the following methods :

```php
// Manual flushing
Flatten::flushAll();
Flatten::flushPattern('users/.+');
Flatten::flushUrl('http://localhost/users/taylorotwell');

// Flushing via an UrlGenerator
Flatten::flushRoute('user', 'taylorotwell');
Flatten::flushAction('UsersController@user', 'taylorotwell');

// Flushing template sections (see below)
Flatten::flushSection('articles');
```

### Runtime caching

You don't have to cache all of a page, you can fine-tune your cache in smaller cached sections.

In PHP you'd do it like this :

```php
<h1>This will always be dynamic</h1>
<?php foreach ($articles as $article): ?>
	<?= $article->name ?>
<?php endforeach; ?>

<h1>This will not</h1>
<?php Flatten::section('articles', function () use ($articles) { ?>
	<?php foreach ($articles as $article): ?>
		<?= $article->name ?>
	<?php endforeach; ?>
<?php }); ?>
```

You can also specify for how long you want that section to be cached by adding an argument to `section` :

```php
<!-- This section will be cached for 10 minutes -->
<?php Flatten::section('articles', 10, function () use ($articles) { ?>
	<?php foreach ($articles as $article): ?>
		<?= $article->name ?>
	<?php endforeach; ?>
<?php }); ?>
```

Flatten also hooks into the Blade templating engine for a leaner syntax. Let's rewrite our above example :

```html
<h1>This will always be dynamic</h1>
@foreach($articles as $articles)
	{{ $article->name }}
@endforeach

<h1>This will not</h1>
@cache('articles', 10)
	@foreach($articles as $article)
		{{ $article->name }}
	@endforeach
@endcache
```

### Kickstarting

You can speed up Flatten even more by using the `Flatten::kickstart` method. It requires a little more boilerplate code but can enhance performances dramatically.
Basically you want to call that before _everything else_ (ie. before even loading Composer). On Laravel you'd put that code at the top of `bootstrap/autoload.php`.

You use it like that :

```php
require __DIR__.'/../vendor/anahkiasen/flatten/src/Flatten/Flatten.php';

Flatten\Flatten::kickstart();
```

If you have things in your saltshaker, you'll need to find faster raw methods to get these and pass the salts as arguments :

```php
require __DIR__.'/../vendor/anahkiasen/flatten/src/Flatten/Flatten.php';

$salt = mysql_query('SOMETHING');
Flatten\Flatten::kickstart($salt);
```

## Using outside of Laravel

Flatten can easily be used outside of Laravel, for this you'll basically only ever use two methods. What you basically want to do is call `Flatten::start` at the top of the page, and `Flatten::end` at the bottom.

```php
<?php
require 'vendor/autoload.php';

use Flatten\Facades\Flatten;
?>

<?php Flatten::start() ?>
<!DOCTYPE html>
	<head></head>
	<body></body>
</html>
<?php Flatten::end() ?>
```
