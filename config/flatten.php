<?php return array(

  // The environments in which Flatten should not run
  'environments' => array('local'),

  // The folder inside storage/cache where the pages will be stored
  'folder'       => 'pages',

  // The Laravel Flatten will start caching from
  'hook'         => 'laravel.started: application',

  // The different pages to ignored when caching
  // They're all regexes so go crazy
  'ignore'       => array(),

  // List only specific pages to cache, useful if you have a lot of
  // pages you don't want to see cached
  // The ignored pages will still be substracted from this array
  'only'         => array(),

  // Strings or variables to append/prepend the cache hash
  // Like 'prepend' => Auth::user()->level.Session::get('something').Config::get('application.language')
  // OR 'prepend' => array(Auth::user()->level, Session::get('something'), ...)
  'prepend' => null,
  'apprend' => null,
);