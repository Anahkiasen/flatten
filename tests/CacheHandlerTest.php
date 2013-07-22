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
}