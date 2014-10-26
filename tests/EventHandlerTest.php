<?php
namespace Flatten;

use Flatten\TestCases\FlattenTestCase;
use Mockery;

class EventHandlerTest extends FlattenTestCase
{
	public function testReturnsNothingIfNoCache()
	{
		$empty = $this->events->onApplicationBoot();
		ob_end_clean();

		$this->assertNull($empty);
	}

	public function testCanStoreCache()
	{
		$response = $this->mockResponse();

		// Pass the response
		$this->events->onApplicationDone($response);

		// Assert response
		$response = $this->flatten->getResponse();
		$this->assertContains('foobar', $response->getContent());
	}

	public function testCancelIfRedirect()
	{
		$response = $this->mockResponse(false, true);

		$this->assertFalse($this->events->onApplicationDone($response));
	}

	public function testCancelIfNotFound()
	{
		$response = $this->mockResponse(true);

		$this->assertFalse($this->events->onApplicationDone($response));
	}

	public function testCancelIfServerError()
	{
		$response = $this->mockResponse(false, false, true);

		$this->assertFalse($this->events->onApplicationDone($response));
	}

	public function testCancelIfForbidden()
	{
		$response = $this->mockResponse(false, false, false, true);

		$this->assertFalse($this->events->onApplicationDone($response));
	}

	/**
	 * @param boolean $found
	 * @param boolean $redirection
	 * @param boolean $error
	 * @param boolean $fobidden
	 *
	 * @return Mockery\MockInterface
	 */
	protected function mockResponse($found = false, $redirection = false, $error = false, $fobidden = false)
	{
		$response = Mockery::mock('Symfony\Component\HttpFoundation\Response');
		$response->shouldReceive('isNotFound')->once()->andReturn($found);
		$response->shouldReceive('isRedirection')->once()->andReturn($redirection);
		$response->shouldReceive('isServerError')->once()->andReturn($error);
		$response->shouldReceive('isForbidden')->once()->andReturn($fobidden);
		$response->shouldReceive('getContent')->once()->andReturn('foobar');

		return $response;
	}
}
