<?php
class EventHandlerTest extends FlattenTests
{
	public function testReturnsNothingIfNoCache()
	{
		$empty = $this->events->onApplicationBoot();

		$this->assertNull($empty);
	}

	public function testCanStoreCache()
	{
		$response = Mockery::mock('Symfony\Component\HttpFoundation\Response');
		$response->shouldReceive('getContent')->once()->andReturn('foobar');

		// Pass the response
		$this->events->onApplicationDone($response);

		// Assert response
		$response = $this->flatten->getResponse();
		$this->assertEquals('foobar', $response->getContent());
	}
}