<?php
namespace Flatten\Crawler;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class BuildCommand extends Command
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
      $this->comment('Clearing the cache');
      $this->laravel['cache']->flush();
    }

    // Crawl pages
    $crawler = new Crawler($this->laravel, $this->output, $this->option('root'));
    $crawled = $crawler->crawlPages();

    $this->command->info('Successfully built '.$crawled. ' pages');
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
