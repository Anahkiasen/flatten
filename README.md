# Flatten

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

### Configuration

All the options are explained in the **config.php** configuration file. You can publish it via `artisan config:publish anahkiasen/flatten`.

Here is a preview of the configuration options available in said file :

```php
// The environments in which Flatten should not run
'environments' => array(),

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

### Building

Flatten can cache all authorized pages in your application via the `artisan flatten:build` command. It will crawl your application and go from page to page, caching all the pages you allowed him to.

### Flushing

Sometimes you may want to flush a specific page or pattern. If per example you cache your users's profiles, you may want to flush those when the user edit its informations.
You can do so via the following methods :

```php
Flatten::flushPattern('users/.+');
Flatten::flushUrl('http://localhost/users/taylorotwell');
Flatten::flushRoute('user', 'taylorotwell');
Flatten::flushAction('UsersController@user', 'taylorotwell');
```