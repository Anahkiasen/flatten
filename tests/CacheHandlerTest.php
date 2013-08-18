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
		$this->cache->setHash('GET-maintainers/');
		$this->cache->storeCache('foobar');
		$this->cache->setHash('GET-maintainer/anahkiasen');
		$this->cache->storeCache('anahkiasen');
		$this->cache->setHash('GET-maintainer/jasonlewis');
		$this->cache->storeCache('jasonlewis');

		$this->cache->flushPattern('maintainer/.+');
		$this->assertFalse($this->app['cache']->has('GET-maintainer/anahkiasen'));
		$this->assertTrue($this->app['cache']->has('GET-maintainers/'));
	}
}
