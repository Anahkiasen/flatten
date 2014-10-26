<?php
namespace Flatten;

use Flatten\Facades\Flatten;
use Flatten\TestCases\FlattenTestCase;

class FlattenTest extends FlattenTestCase
{
	public function testCanComputeHash()
	{
		$this->assertEquals('GET-foobar', $this->flatten->computeHash('foobar'));
	}

	public function testCanComputeHashWithAdditionalSalts()
	{
		$this->app['config'] = $this->mockConfig(array(
			'flatten::saltshaker' => array('fr'),
		));

		$this->assertEquals('fr-GET-foobar', $this->flatten->computeHash('foobar'));
	}

	public function testCanComputeHashWithQueryStrings()
	{
		$this->mockRequest('foobar?foo=bar');
		$this->assertEquals('GET-/foobar?foo=bar', $this->flatten->computeHash());
	}

	public function testCanRenderResponses()
	{
		$response = $this->flatten->getResponse('foobar');
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('foobar', $response->getContent());

		$response = $this->flatten->getResponse();
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('', $response->getContent());
	}

	public function testFacadeCanDelegateCallsToFlush()
	{
		$this->mockRequest('/maintainer/anahkiasen');
		$this->cache->storeCache('anahkiasen');

		Flatten::setFacadeApplication($this->app);

		$this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
		Flatten::flushPattern('#maintainer/.+#');
		$this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
	}

	public function testCanGetPathForKickstarter()
	{
		$this->mockRequest('foobar');
		$this->cache->storeCache('foobar');

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI']    = '/foobar';
		$filename                  = \Flatten\Flatten::getKickstartPath();

		$this->assertContains('cache/69/cc/69ccdba817c2fb3cdade9450a36b273e', $filename);
	}

	public function testCanGetPathForKickstarterWithQueryStrings()
	{
		$this->mockRequest('foobar?foo=bar');
		$this->cache->storeCache('foobar');

		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI']    = '/foobar';
		$_SERVER['QUERY_STRING']   = 'foo=bar';
		$filename                  = \Flatten\Flatten::getKickstartPath();

		$this->assertContains('cache/13/1c/131cc1c3ea11da7e1643b2aaac262a6b', $filename);
	}
}
