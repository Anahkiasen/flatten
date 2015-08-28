<?php

namespace Flatten;

use Flatten\TestCases\FlattenTestCase;

class ContextTest extends FlattenTestCase
{
    public function testCanGetCurrentUrl()
    {
        $this->mockRequest('/');
        $this->assertEquals('/', $this->context->getCurrentUrl());
    }

    public function testCanGetCurrentUrlWithQueryString()
    {
        $this->mockRequest('/?q=foo');
        $this->assertEquals('/?q=foo', $this->context->getCurrentUrl());
    }

    public function testCanCheckIfPageMatchesPattern()
    {
        $this->mockRequest('/');
        $this->assertTrue($this->context->matches(['^/$']));

        $this->mockRequest('/maintainer/foobar');
        $this->assertFalse($this->context->matches(['^/$']));
        $this->assertTrue($this->context->matches(['maintainer/.+']));
    }

    public function testCanCheckIfPageShouldBeCached()
    {
        $this->app['config'] = $this->mockConfig([
            'flatten.ignore' => ['^/maintainer/anahkiasen', 'admin/.+'],
            'flatten.only' => ['^/maintainers/.+', 'package/.+'],
        ]);

        $this->mockRequest('/');
        $this->assertTrue($this->context->shouldCachePage());

        $this->mockRequest('/maintainer/jasonlewis');
        $this->assertTrue($this->context->shouldCachePage());

        $this->mockRequest('/maintainer/anahkiasen');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/admin/maintainers/5/edit');
        $this->assertFalse($this->context->shouldCachePage());

        $this->app['config'] = $this->mockConfig()->shouldReceive('get')->andReturn(null)->mock();
        $this->assertTrue($this->context->shouldCachePage());
    }

    public function testDoesntCacheAjaxRequests()
    {
        $this->mockRequest('/', true);
        $this->assertFalse($this->context->shouldCachePage());
    }

    public function testDoesntCacheNonGetRequests()
    {
        $this->mockRequest('/', false, 'POST');
        $this->assertFalse($this->context->shouldCachePage());
    }

    public function testCanUncacheAllPagesWithOnly()
    {
        $this->app['config'] = $this->mockConfig([
            'flatten.only' => ['foobar'],
            'flatten.ignore' => [],
        ]);

        $this->mockRequest('/');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/maintainer/jasonlewis');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/maintainer/anahkiasen');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/admin/maintainers/5/edit');
        $this->assertFalse($this->context->shouldCachePage());
    }

    public function testCanUncacheAllPagesWithIgnore()
    {
        $this->app['config'] = $this->mockConfig([
            'flatten.only' => [],
            'flatten.ignore' => ['.+'],
        ]);

        $this->mockRequest('/');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/maintainer/jasonlewis');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/maintainer/anahkiasen');
        $this->assertFalse($this->context->shouldCachePage());

        $this->mockRequest('/admin/maintainers/5/edit');
        $this->assertFalse($this->context->shouldCachePage());
    }

    public function testCanCheckIfInAllowedEnvironment()
    {
        $this->app['config'] = $this->mockConfig();
        $this->app['config']->shouldReceive('get')->with('flatten.enabled')->andReturn(true);
        $this->assertTrue($this->context->isInAllowedEnvironment());

        $this->app['config'] = $this->mockConfig();
        $this->app['config']->shouldReceive('get')->with('flatten.enabled')->andReturn(false);
        $this->assertFalse($this->context->isInAllowedEnvironment());
    }

    public function testCanCheckIfShouldRun()
    {
        $this->app['config'] = $this->mockConfig([
            'flatten.enabled' => false,
            'flatten.ignore' => ['^/maintainer/anahkiasen', 'admin/.+'],
            'flatten.only' => ['^/maintainers/.+', 'package/.+'],
        ]);

        $this->app['env'] = 'local';
        $this->mockRequest('/maintainer/jasonlewis');
        $this->assertFalse($this->context->shouldRun());

        $this->app['config'] = $this->mockConfig([
            'flatten.enabled' => true,
            'flatten.ignore' => ['^/maintainer/anahkiasen', 'admin/.+'],
            'flatten.only' => ['^/maintainers/.+', 'package/.+'],
        ]);

        $this->app['env'] = 'production';
        $this->mockRequest('/maintainer/jasonlewis');
        $this->assertTrue($this->context->shouldRun());
    }

    public function testCanUseBlockers()
    {
        $_GET['foo'] = 'bar';
        $this->app['config'] = $this->mockConfig([
            'flatten.enabled' => true,
            'flatten.blockers' => [$_GET['foo'] === 'bar'],
        ]);
        $this->assertTrue($this->context->shouldRun());

        $_GET['foo'] = 'baz';
        $this->app['config'] = $this->mockConfig([
            'flatten.enabled' => true,
            'flatten.blockers' => [$_GET['foo'] === 'bar'],
        ]);
        $this->assertFalse($this->context->shouldRun());
    }
}
