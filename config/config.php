<?php return array(

  // The environments in which Flatten should not run
  'environments' => array(),

  // The folder inside storage/cache where the pages will be stored
  'folder'       => 'pages',

  // The default period during which a cached page should be kept (in minutes)
  // 0 means the page never gets refreshed by itself
  'cachetime' => 0,

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
);