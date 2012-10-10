<?php return array(

  // The folder inside storage/cache where the pages will be stored
  'folder' => 'pages',

  // The Laravel Flatten will start caching from
  'hook' => 'laravel.started: application',

  // The different pages to ignored when caching
  // They're all regexes so go crazy
  'ignore' => array(
  ),

);