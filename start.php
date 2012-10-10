<?php
use \Flatten\Flatten;
use \Flatten\Config;

// Loading Former -------------------------------------------------- /

Autoloader::namespaces(array(
  'Flatten' => Bundle::path('flatten') . 'libraries'
));

// Loading Flatten configuration ----------------------------------- /

new Config;

// Hook Flatten to Laravel ----------------------------------------- /

Flatten::hook();