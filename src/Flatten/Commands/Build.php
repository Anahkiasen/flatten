<?php
namespace Flatten\Commands;

use Flatten\Crawler;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Build extends Command
{

  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'flatten:build';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Will crawl your application and cache all pages authorized';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return void
   */
  public function fire()
  {
    if ($this->option('clear')) {
      $this->laravel['cache']->flush();
    }

    $crawler = new Crawler($this->laravel, $this->option('root'));

    $crawler->crawlPages();
  }

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return array(
    );
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
      array('clear', 'c', InputOption::VALUE_NONE,     'Clear the cache before building'),
      array('root',  'r', InputOption::VALUE_REQUIRED, 'A root URL to be used when visiting'),
    );
  }

}