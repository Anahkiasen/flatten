<?php
class FlattenTest extends FlattenTests
{
	public function testCanComputeHash()
	{
		$this->assertEquals('GET-foobar', $this->flatten->computeHash('foobar'));
	}

	public function testCanRenderResponses()
	{
		$response = $this->flatten->getResponse('foobar');

		$this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
		$this->assertEquals('foobar', $response->getContent());
	}

	public function testCanCheckIfPageMatchesPattern()
	{
		$this->app['request'] = $this->mockRequest('/');
		$this->assertTrue($this->flatten->matches(array('^/$')));

		$this->app['request'] = $this->mockRequest('maintainer/foobar');
		$this->assertFalse($this->flatten->matches(array('^/$')));
		$this->assertTrue($this->flatten->matches(array('maintainer/.+')));
	}

	public function testCanCheckIfPageShouldBeCached()
	{
		$this->app['config']  = $this->mockConfig(array(
			'flatten::ignore' => array('^maintainer/anahkiasen', 'admin/.+'),
			'flatten::only'   => array('^maintainers/.+', 'package/.+'),
		));

		$this->app['request'] = $this->mockRequest('/');
		$this->assertTrue($this->flatten->shouldCachePage());

		$this->app['request'] = $this->mockRequest('maintainer/jasonlewis');
		$this->assertTrue($this->flatten->shouldCachePage());

		$this->app['request'] = $this->mockRequest('maintainer/anahkiasen');
		$this->assertFalse($this->flatten->shouldCachePage());

		$this->app['request'] = $this->mockRequest('admin/maintainers/5/edit');
		$this->assertFalse($this->flatten->shouldCachePage());

		$this->app['config'] = $this->mockConfig()->shouldReceive('get')->andReturn(null)->mock();
		$this->assertTrue($this->flatten->shouldCachePage());
	}

	public function testCanCheckIfInAllowedEnvironment()
	{
		$this->app['config'] = $this->mockConfig();
		$this->app['config']->shouldReceive('get')->with('flatten::environments')->andReturn(array('local'));

		$this->assertTrue($this->flatten->isInAllowedEnvironment());

		$this->app['env'] = 'production';
		$this->assertTrue($this->flatten->isInAllowedEnvironment());

		$this->app['env'] = 'local';
		$this->assertFalse($this->flatten->isInAllowedEnvironment());
	}

	public function testCanCheckIfShouldRun()
	{
		$this->app['config']  = $this->mockConfig(array(
			'flatten::environments' => array('local'),
			'flatten::ignore'       => array('^maintainer/anahkiasen', 'admin/.+'),
			'flatten::only'         => array('^maintainers/.+', 'package/.+'),
		));

		$this->app['env'] = 'local';
		$this->app['request'] = $this->mockRequest('maintainer/jasonlewis');
		$this->assertFalse($this->flatten->shouldRun());

		$this->app['env'] = 'production';
		$this->app['request'] = $this->mockRequest('maintainer/jasonlewis');
		$this->assertTrue($this->flatten->shouldRun());
	}
}