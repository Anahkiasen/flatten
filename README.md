# Flatten

Flatten is a powerful cache system for the Laravel framework. What it does is quite simple : you tell him which page are to be cached, when the cache is to be flushed, and from there Flatten handles it all. It will quietly flatten your pages to plain HTML and store them. That whay if an user visit a page that has already been flattened, all the Laravel-system and PHP is highjacked to instead display a simple HTML page.
This will provide an essential boost to your application's speed, as your page's cache only gets refreshed when a change is made to the data it displays.

<a name='installation'></a>
## Installation

Flatten installs just like any other Laravel bundle

```bash
php artisan bundle:install flatten
```

Add the following to your `bundles.php` file :

```php
'flatten' => array('auto' => true),
```

And in **bundles.php**

```php
'Flatten' => 'Flatten\Flatten',
```

<a name='configuration'></a>
## Configuration

All the options are explained in the **flatten.php** configuration file found in `bundles/flatten/config/flatten.php`. If you don't want to touch anything in there, just create your own **flatten.php** file in `application/config/flatten.php` and any default setting set by Flatten will be overwritten by your custom configuration.

Here is a preview of the configuration options available in said file :

```php
// The environments in which Flatten should not run
'environments' => array('local'),

// The folder inside storage/cache where the pages will be stored
'folder'       => 'pages',

// The Laravel event from which Flatten will start caching from
'hook'         => 'laravel.started: application',

// The different pages to be ignored when caching
// They're all regexes so go crazy
'ignore'       => array(),

// List only specific pages to cache, useful if you have a lot of
// pages you don't want to see cached
// The ignored pages will still be substracted from this array
'only'         => array(),

// Strings or variables to prepend/append to the caching salt
// Like 'prepend' => Auth::user()->level.Session::get('something').Config::get('application.language')
// OR 'prepend' => array(Auth::user()->level, Session::get('something'), ...)
'prepend' => null,
'apprend' => null,
```

<a name='flushing'></a>
## Flushing

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