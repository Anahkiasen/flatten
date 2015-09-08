<?php

namespace Flatten\Crawler;

use DOMElement;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Crawler
{
    /**
     * The IoC Container.
     *
     * @var Container
     */
    protected $app;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * An array of the application's pages.
     *
     * @var array
     */
    protected $queue = [
        '/' => true,
    ];

    /**
     * The current page being crawled.
     *
     * @var int
     */
    protected $current;

    /**
     * An array of the pages already crawled.
     *
     * @var array
     */
    protected $crawled = [];

    /**
     * The root URL.
     *
     * @var string
     */
    protected $root;

    /**
     * Build a new Crawler.
     *
     * @param Container           $app
     * @param HttpKernelInterface $kernel
     * @param OutputInterface     $output
     * @param string|null         $root
     */
    public function __construct(Container $app, HttpKernelInterface $kernel, OutputInterface $output, $root = null)
    {
        $this->app = $app;

        // Prepare output
        $this->output = $output ?: new NullOutput();

        // Create Client
        $this->root = $root ?: $this->app['config']->get('app.url');
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// CRAWLER /////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Crawl the pages in the queue.
     *
     * @return int
     */
    public function crawlPages()
    {
        $pages = array_keys(array_filter($this->queue));

        foreach ($pages as $key => $page) {
            if (in_array($page, $this->crawled, true)) {
                continue;
            }

            // Try to display the page
            // Cancel if not found
            $this->current = $key;
            $this->crawlPage($page);
        }

        // Recursive call
        if ($this->hasPagesToCrawl()) {
            $this->crawlPages();
        }

        return count($this->queue);
    }

    /**
     * Crawl an URL and extract its links.
     *
     * @param string $page The page's URL
     *
     * @return false|null
     */
    protected function crawlPage($page)
    {
        // Mark page as crawled
        $this->crawled[] = $page;
        $this->queue[$page] = false;

        try {
            if (!$crawler = $this->getPage($page)) {
                return false;
            }
        } catch (Exception $e) {
            return $this->error('Page "'.$page.'" errored : '.$e->getMessage());
        }

        // Extract new links
        $this->extractLinks($crawler);
    }

    /**
     * Check if the Crawler still has pages to crawl.
     *
     * @return bool
     */
    protected function hasPagesToCrawl()
    {
        return !empty(array_filter($this->queue));
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////// LINKS //////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Extract the various links from a page.
     *
     * @param DomCrawler $crawler
     */
    protected function extractLinks(DomCrawler $crawler)
    {
        foreach ($crawler->filter('a') as $link) {
            if (!$this->isExternal($link)) {
                $this->queueLink($link);
            }
        }
    }

    /**
     * Add a Link to the list of pages to be crawled.
     *
     * @param DOMElement $link
     */
    protected function queueLink(DOMElement $link)
    {
        $link = $link->getAttribute('href');

        // If the page wasn't crawled yet, crawl it
        if (!in_array($link, $this->crawled, true)) {
            $this->queue[$link] = true;
        }
    }

    /**
     * Check if a Link is external.
     *
     * @param DOMElement $link
     *
     * @return bool
     */
    protected function isExternal(DOMElement $link)
    {
        return !Str::startsWith($link->getAttribute('href'), $this->root);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param string $url
     *
     * @return null|DomCrawler
     */
    protected function getPage($url)
    {
        $url = str_replace($this->root, null, $url);

        // Call page
        $request = Request::create($url, 'GET');
        $response = $this->app->make('Illuminate\Contracts\Http\Kernel')->handle($request);

        if (!$response->isOk()) {
            return $this->error('Page at "'.$url.'" could not be reached');
        }

        // Format content
        $content = $response->getContent();
        $content = preg_replace('#(href|src)=([\'"])/([^/])#', '$1=$2'.$this->root.'/$3', $content);
        $content = str_replace($this->app['url']->to('/'), $this->root, $content);
        $content = utf8_decode($content);

        // Display message
        $status = $this->app['flatten.context']->shouldCachePage() ? 'Cached' : 'Left uncached';
        $this->output->writeln($status.' <info>' .$url. '</info>');

        // Cache page
        if ($this->app['flatten.context']->shouldCachePage()) {
            $this->app['flatten.cache']->storeCache($content);
        }

        return new DomCrawler($content);
    }

    ////////////////////////////////////////////////////////////////////
    ////////////////////////////// OUTPUT //////////////////////////////
    ////////////////////////////////////////////////////////////////////

    /**
     * Write a string as error output.
     *
     * @param string $string
     */
    protected function error($string)
    {
        $this->output->writeln('<error>'.$string.'</error>');
    }
}
