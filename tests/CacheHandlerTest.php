<?php
class CacheHandlerTest extends FlattenTests
{
	public function testCanGetHash()
	{
		$this->assertEquals('GET-/', $this->cache->getHash());
	}

	public function testCanStoreCache()
	{
		$this->cache->storeCache('foobar');

		$this->assertTrue($this->cache->hasCache());
		$this->assertFalse($this->cache->storeCache(''));
	}

	public function testCanFetchContent()
	{
		$this->cache->storeCache('foobar');

		$this->assertEquals('foobar', $this->cache->getCache());
	}

	public function testCanFlushSpecificPatterns()
	{
		$this->app['request'] = $this->mockRequest('/maintainers');
		$this->cache->storeCache('foobar');

		$this->app['request'] = $this->mockRequest('/maintainer/anahkiasen');
		$this->cache->storeCache('anahkiasen');

		$this->app['request'] = $this->mockRequest('/maintainer/jasonlewis');
		$this->cache->storeCache('jasonlewis');

		$this->cache->flushUrl('maintainer/.+');
		$this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
		$this->assertTrue($this->app['cache']->has('GET-/maintainers'));
	}

	public function testCanFlushUrl()
	{
		$this->app['request'] = $this->mockRequest('/maintainer/anahkiasen');
		$this->cache->storeCache('foobar');

		$this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
		$this->cache->flushUrl('http://localhost/maintainer/.+');
		$this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
	}

	public function testCanFlushRoute()
	{
		$this->app['url']->shouldReceive('route')->with('maintainer', 'anahkiasen')->once()->andReturn('http://localhost/maintainer/anahkiasen');

		$this->app['request'] = $this->mockRequest('/maintainer/anahkiasen');
		$this->cache->storeCache('foobar');

		$this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
		$this->cache->flushRoute('maintainer', 'anahkiasen');
		$this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
	}

	public function testCanFlushAction()
	{
		$this->app['url']->shouldReceive('action')->with('MaintainersController@maintainer', 'anahkiasen')->once()->andReturn('http://localhost/maintainer/anahkiasen');

		$this->app['request'] = $this->mockRequest('/maintainer/anahkiasen');
		$this->cache->storeCache('foobar');

		$this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
		$this->cache->flushAction('MaintainersController@maintainer', 'anahkiasen');
		$this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
	}
}
