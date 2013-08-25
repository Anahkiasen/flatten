<?php
use Flatten\Facades\Flatten;

class FlattenTest extends FlattenTests
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
}
