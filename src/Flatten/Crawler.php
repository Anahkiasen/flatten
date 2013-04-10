<?php
namespace Flatten;

use DOMElement;
use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\Client;
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
  public function __construct(Container $app, $root = null)
  {
    $this->app    = $app;
    $this->client = new Client($app, array());
    $this->root   = $root ?: $this->app['request']->path();
  }

  /**
   * Change the Crawler's root URL
   *
   * @param string $root
   */
  public function setRoot($root)
  {
    $this->root = $root;
  }

  //////////////////////////////////////////////////////////////////////
  ///////////////////////////// CRAWLER /////////////////////////////////
  //////////////////////////////////////////////////////////////////////

  /**
   * Crawl the pages in the queue
   */
  public function crawlPages()
  {
    $pages = $this->pages;
    $this->pages = array();

    foreach ($pages as $page => $nope) {
      if (in_array($page, $this->crawled)) continue;

      // Try to display the page
      // Cancel if not found
      try {
        $this->crawled[] = $page;
        $crawler = $this->getPage($page);
      }
      catch (NotFoundHttpException $e) {
        continue;
      }

      // Add the links to list of pages to crawl
      $this->extractLinks($crawler);
    }

    if ($this->hasPagesToCrawl()) {
      $this->crawlPages();
    }
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
    print 'Crawling page ' .$url.PHP_EOL;

    // Call page
    $this->client->request('GET', $url);
    $response = $this->client->getResponse();

    // Format content
    $content = $response->getContent();
    $content = preg_replace('#(href|src)=([\'"])/([^/])#', '$1=$2'.$this->root.'/$3', $content);
    $content = str_replace($this->app['url']->to('/'), $this->root, $content);
    $content = utf8_decode($content);

    // Cache page
    $this->app['flatten.cache']->storeCache($content);

    return new DomCrawler($content);
  }

}