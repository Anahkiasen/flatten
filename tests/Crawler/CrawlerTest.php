<?php
namespace Flatten\Crawler;

use Flatten\FlattenTests;
use Flatten\TestCases\FlattenTestCase;
use Mockery;

class CrawlerTest extends FlattenTestCase
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
