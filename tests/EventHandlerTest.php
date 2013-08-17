<?php
class EventHandlerTest extends FlattenTests
{
	public function testReturnsNothingIfNoCache()
	{
		$empty = $this->app['flatten.events']->onApplicationBoot();

		$this->assertNull($empty);
	}

	public function testCanStoreCache()
	{
		$response = Mockery::mock('Symfony\Component\HttpFoundation\Response');
		$response->shouldReceive('getContent')->once()->andReturn('foobar');

		// Pass the response
		$this->app['flatten.events']->onApplicationDone($response);

		// Assert response
		$response = $this->app['flatten.events']->onApplicationBoot();
		$this->assertEquals('foobar', $response->getContent());
	}
}