<?php return array(

  // The folder inside storage/cache where the pages will be stored
  'folder' => 'pages',

  // The Laravel Flatten will start caching from
  'hook' => 'laravel.started: application',

  // The different pages to ignored when caching
  // They're all regexes so go crazy
  'ignore' => array(
  ),

  // List only specific pages to cache, useful if you have a lot of
  // pages you don't want to see cached
  // The ignored pages will still be substracted from this array
  'only' => array(
  ),

  // The environments in which Flatten should not run
  'environments' => array(
    'test',
  ),

);