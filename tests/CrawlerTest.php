<?php
use Flatten\Crawler\Crawler;

class CrawlerTest extends FlattenTests
{
	public function testCanCreateCrawler()
	{
	}

	////////////////////////////////////////////////////////////////////
	/////////////////////////////// HELPERS ////////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Get a new Crawler instance
	 *
	 * @return Crawler
	 */
	protected function getCrawler()
	{
	$client = Mockery::mock('Illuminate\Foundation\Testing\Application');
	$output = Mockery::mock('Symfony\Component\Console\Output\OutputInterface');
		$kernel = Mockery::mock('Symfony\Component\HttpKernel\HttpKernelInterface');

		return new Crawler($this->app, $kernel, $output);
	}
}
