# Flatten

Flatten is a powerful cache system for the Laravel framework. What it does is quite simple : you tell him which page are to be cached, when the cache is to be flushed, and from there Flatten handles it all. It will quietly flatten your pages to plain HTML and store them. That whay if an user visit a page that has already been flattened, all the Laravel-system and PHP is highjacked to instead display a simple HTML page.
This will provide an essential boost to your application's speed, as your page's cache only gets refreshed when a change is made to the data it displays.

<a name='installation'></a>
## Installation

Flatten installs just like any other package, via Composer

```json
"anahkiasen/flatten": "dev-develop",
```

Then add Flatten's Service Provider to you `config/app.php` file :

```php
'Flatten\FlattenServiceProvider',
```

<a name='configuration'></a>
## Configuration

All the options are explained in the **config.php** configuration file. You can publish it via `artisan config:publish anahkiasen/flatten`.

Here is a preview of the configuration options available in said file :

```php
// The environments in which Flatten should not run
'environments' => array(),

// The default period during which a cached page should be kept (in minutes)
// 0 means the page never gets refreshed by itself
'lifetime' => 0,

// The different pages to be ignored when caching
// They're all regexes so go crazy
'ignore'       => array(),

// List only specific pages to cache, useful if you have a lot of
// pages you don't want to see cached
// The ignored pages will still be substracted from this array
'only'         => array(),

// An array of string or variables to add to the salt being used
// to differentiate pages
'saltshaker' => array(),
```

<a name='building'></a>
## Building

Flatten can cache all authorized pages in your application via the `artisan flatten:build` command. It will crawl your application and go from page to page, caching all the pages you allowed him to.

<a name='flushing'></a>
## Flushing

-- This part is not yet up to date for Laravel 4

Flatten gracefully ties-in Laravel's system by providing both a public toolkit and a flushing filter.
The filter will flush the whole cache, and can easily be binded to any method or rest request. You can per example do this :

```php
class Users_Controller
{
  public function __construct()
  {
    parent::__construct();

    // This will flush on all POST methods of
    // the Users controller, excepted the post_login one
    $this->filter('after', 'flush')->on('post')->except(array('login'));
  }
}
```

Flatten also provides several public methods to use directly in your code.

```php
// Flushes the whole cache
Flatten::flush()

// Flushes a certain pattern
Flatten::flush('(users|documents)/(read|edit|.+)')

// Flush an action or a custom route
Flatten::flush_action('users@read', array($user->id))
Flatten::flush_route('myroute',     array($user->id))
```

For any questions, bug or suggestions on Flatten I redirect you to [the Github's issues](https://github.com/Anahkiasen/flatten/issues) !