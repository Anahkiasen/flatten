<?php

return [

    // Whether Flatten should be enabled in this environment
    'enabled' => true,

    // Cache checks
    //
    // Various checks that are used to see if Flatten should run
    ////////////////////////////////////////////////////////////////////

    // The different pages to be ignored when caching
    // They're all regexes so go crazy
    'ignore' => [],

    // List only specific pages to cache, useful if you have a lot of
    // pages you don't want to see cached
    // The ignored pages will still be substracted from this array
    'only' => [],

    // Here you can put variables that will be taken into account when
    // checking if Flatten should run. If the sum of the array is not
    // "true", then Flatten won't start
    'blockers' => [],

    // Cache variables
    ////////////////////////////////////////////////////////////////////

    // Whether to append a timestamp comment on cached pages
    // eg. <!-- Cached on 2015-09-09 01:01:01 -->
    'timestamp' => true,

    // The default period during which a cached page should be kept (in minutes)
    // 0 means the page never gets refreshed by itself
    'lifetime' => 0,

    // An array of string or variables to add to the salt being used
    // to differentiate pages
    'saltshaker' => [],
];
