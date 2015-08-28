<?php
use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;
use Symfony\CS\FixerInterface;

$finder = DefaultFinder::create()->in(['src', 'tests']);

return Config::create()
             ->level(FixerInterface::SYMFONY_LEVEL)
             ->fixers([
                 'ereg_to_preg',
                 'multiline_spaces_before_semicolon',
                 'ordered_use',
                 'php4_constructor',
                 'phpdoc_order',
                 'short_array_syntax',
                 'short_echo_tag',
                 'strict',
                 'strict_param',
             ])
             ->setUsingCache(true)
             ->finder($finder);
