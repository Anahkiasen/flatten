<?php
namespace Flatten;

use DOMElement;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\Client;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Crawler
{

  /**
   * The IoC Container
   *
   * @var Container
   */
  protected $app;

  /**
   * The HttpKernel client instance.
   *
   * @var Client
   */
  protected $client;

  /**
   * An array of the application's pages
   *
   * @var array
   */
  protected $pages = array(
    '/' => false,
  );

  /**
   * An array of the pages already crawled
   *
   * @var array
   */
  protected $crawled = array();

  /**
   * The root URL
   *
   * @var string
   */
  protected $root;

  /**
   * Build a new Crawler
   *
   * @param Container $app
   * @param string    $root A root url to use
   */
  public function __construct(Container $app, OutputInterface $output, $root = null)
  {
    $this->app    = $app;
    $this->output = $output;

    $this->client = new Client($app, array());
    $this->root   = $root ?: $this->app['request']->root();
  }

  //////////////////////////////////////////////////////////////////////
  ///////////////////////////// CRAWLER /////////////////////////////////
  //////////////////////////////////////////////////////////////////////

  /**
   * Crawl the pages in the queue
   */
  public function crawlPages()
  {
    $pages = array_keys($this->pages);
    $this->pages = array();

    foreach ($pages as $page) {
      if (in_array($page, $this->crawled)) continue;

      // Try to display the page
      // Cancel if not found
      $this->crawlPage($page);
    }

    // Recursive call
    if ($this->hasPagesToCrawl()) {
      $this->crawlPages();
    }
  }

  /**
   * Crawl an URL and extract its links
   *
   * @param string $page The page's URL
   */
  protected function crawlPage($page)
  {
    try {
      $crawler = $this->getPage($page);
      if (!$crawler) return false;
    }

    catch (NotFoundHttpException $e) {
      $this->error('Page at "' .$page. '" returned a 404');
      return false;
    }

    // Add the links to list of pages to crawl
    $this->crawled[] = $page;
    $this->extractLinks($crawler);
  }

  /**
   * Check if the Crawler still has pages to crawl
   *
   * @return boolean
   */
  protected function hasPagesToCrawl()
  {
    return !empty($this->pages);
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// LINKS /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Extract the various links from a page
   *
   * @param DomCrawler $crawler
   *
   * @return array An array of links
   */
  protected function extractLinks(DomCrawler $crawler)
  {
    $_links = $crawler->filter('a');
    $links  = array();

    foreach ($_links as $link) {
      if ($this->isExternal($link)) continue;
      else $this->addLink($link);
    }
  }

  /**
   * Add a Link to the list of pages to be crawled
   *
   * @param DOMElement $link
   */
  protected function addLink(DOMElement $link)
  {
    $link = $link->getAttribute('href');

    // If the page wasn't crawled yet, crawl it
    if (!in_array($link, $this->crawled)) {
      $this->pages[$link] = false;
    }
  }

  /**
   * Check if a Link is external
   *
   * @param DomElement $link
   *
   * @return boolean
   */
  protected function isExternal(DOMElement $link)
  {
    return !Str::startsWith($link->getAttribute('href'), $this->root);
  }

  /**
   * Call the given URI and return the Response.
   *
   * @param  string  $method
   * @param  string  $uri
   * @param  array   $parameters
   *
   * @return \Illuminate\Http\Response
   */
  protected function getPage($url)
  {
    $url = str_replace($this->root, null, $url);
    $this->info('Crawling : ' .$url);

    // Call page
    $this->client->request('GET', $url);
    $response = $this->client->getResponse();

    if (!$response->isOk()) {
      $this->error('Page at "' .$url. '" could not be reached');
      return false;
    }

    // Format content
    $content = $response->getContent();
    $content = preg_replace('#(href|src)=([\'"])/([^/])#', '$1=$2'.$this->root.'/$3', $content);
    $content = str_replace($this->app['url']->to('/'), $this->root, $content);
    $content = utf8_decode($content);

    // Cache page
    if ($this->app['flatten']->shouldCachePage()) {
      $this->comment('└─ Cached ✔');
      $this->app['flatten.cache']->storeCache($content);
    } else $this->comment('└─ Left uncached ✘');

    return new DomCrawler($content);
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////////// OUTPUT ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Write a string as information output.
   *
   * @param  string  $string
   * @return void
   */
  protected function info($string)
  {
    $this->output->writeln("<info>$string</info>");
  }

  /**
   * Write a string as comment output.
   *
   * @param  string  $string
   * @return void
   */
  public function comment($string)
  {
    $this->output->writeln("<comment>$string</comment>");
  }

  /**
   * Write a string as error output.
   *
   * @param  string  $string
   * @return void
   */
  protected function error($string)
  {
    $this->output->writeln("<error>$string</error>");
  }

}