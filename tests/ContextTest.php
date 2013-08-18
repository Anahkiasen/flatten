<?php
class ContextTest extends FlattenTests
{
	public function testCanGetCurrentUrl()
	{
		$this->app['request'] = $this->mockRequest('/');
		$this->assertEquals('/', $this->context->getCurrentUrl());
	}

	public function testCanCheckIfPageMatchesPattern()
	{
		$this->app['request'] = $this->mockRequest('/');
		$this->assertTrue($this->context->matches(array('^/$')));

		$this->app['request'] = $this->mockRequest('/maintainer/foobar');
		$this->assertFalse($this->context->matches(array('^/$')));
		$this->assertTrue($this->context->matches(array('maintainer/.+')));
	}

	public function testCanCheckIfPageShouldBeCached()
	{
		$this->app['config']  = $this->mockConfig(array(
			'flatten::ignore' => array('^/maintainer/anahkiasen', 'admin/.+'),
			'flatten::only'   => array('^/maintainers/.+', 'package/.+'),
		));

		$this->app['request'] = $this->mockRequest('/');
		$this->assertTrue($this->context->shouldCachePage());

		$this->app['request'] = $this->mockRequest('/maintainer/jasonlewis');
		$this->assertTrue($this->context->shouldCachePage());

		$this->app['request'] = $this->mockRequest('/maintainer/anahkiasen');
		$this->assertFalse($this->context->shouldCachePage());

		$this->app['request'] = $this->mockRequest('/admin/maintainers/5/edit');
		$this->assertFalse($this->context->shouldCachePage());

		$this->app['config'] = $this->mockConfig()->shouldReceive('get')->andReturn(null)->mock();
		$this->assertTrue($this->context->shouldCachePage());
	}

	public function testCanCheckIfInAllowedEnvironment()
	{
		$this->app['config'] = $this->mockConfig();
		$this->app['config']->shouldReceive('get')->with('flatten::environments')->andReturn(array('local'));

		$this->assertTrue($this->context->isInAllowedEnvironment());

		$this->app['env'] = 'production';
		$this->assertTrue($this->context->isInAllowedEnvironment());

		$this->app['env'] = 'local';
		$this->assertFalse($this->context->isInAllowedEnvironment());
	}

	public function testCanCheckIfShouldRun()
	{
		$this->app['config']  = $this->mockConfig(array(
			'flatten::environments' => array('local'),
			'flatten::ignore'       => array('^/maintainer/anahkiasen', 'admin/.+'),
			'flatten::only'         => array('^/maintainers/.+', 'package/.+'),
		));

		$this->app['env'] = 'local';
		$this->app['request'] = $this->mockRequest('/maintainer/jasonlewis');
		$this->assertFalse($this->context->shouldRun());

		$this->app['env'] = 'production';
		$this->app['request'] = $this->mockRequest('/maintainer/jasonlewis');
		$this->assertTrue($this->context->shouldRun());
	}
}
