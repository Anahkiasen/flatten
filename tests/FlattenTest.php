<?php
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

	public function testCanRenderResponses()
	{
		$response = $this->flatten->getResponse('foobar');
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('foobar', $response->getContent());

		$response = $this->flatten->getResponse();
		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('', $response->getContent());
	}
}
