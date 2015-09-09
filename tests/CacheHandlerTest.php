<?php

namespace Flatten;

use Flatten\TestCases\FlattenTestCase;

class CacheHandlerTest extends FlattenTestCase
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

        $this->assertContains('foobar', $this->cache->getCache());
    }

    public function testCanDisableTimestamp()
    {
        $this->config->set('flatten.timestamp', false);

        $this->cache->storeCache('foobar');

        $this->assertEquals('foobar', $this->app['cache']->get($this->cache->getHash()));
    }

    public function testCanFlushSpecificPatterns()
    {
        $this->mockRequest('/maintainers');
        $this->cache->storeCache('foobar');

        $this->mockRequest('/maintainer/anahkiasen');
        $this->cache->storeCache('anahkiasen');

        $this->mockRequest('/maintainer/jasonlewis');
        $this->cache->storeCache('jasonlewis');

        $this->cache->flushUrl('maintainer/.+');
        $this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
        $this->assertTrue($this->app['cache']->has('GET-/maintainers'));
    }

    public function testFlushingRemoveEntriesFromCachedPages()
    {
        $this->mockRequest('/maintainers');
        $this->cache->storeCache('foobar');

        $this->mockRequest('/maintainer/anahkiasen');
        $this->cache->storeCache('anahkiasen');

        $this->mockRequest('/maintainer/jasonlewis');
        $this->cache->storeCache('jasonlewis');

        $this->assertEquals([
            'GET-/maintainers',
            'GET-/maintainer/anahkiasen',
            'GET-/maintainer/jasonlewis',
        ], $this->cache->getCachedPages());
        $this->cache->flushUrl('maintainer/.+');
        $this->assertEquals(['GET-/maintainers'], $this->cache->getCachedPages());
    }

    public function testCanFlushEverythingIfNoPatternProvided()
    {
        $this->mockRequest('/maintainers');
        $this->cache->storeCache('foobar');

        $this->mockRequest('/maintainer/anahkiasen');
        $this->cache->storeCache('anahkiasen');

        $this->mockRequest('/maintainer/jasonlewis');
        $this->cache->storeCache('jasonlewis');

        $this->cache->flushAll();
        $this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
        $this->assertFalse($this->app['cache']->has('GET-/maintainer/jasonlewis'));
        $this->assertFalse($this->app['cache']->has('GET-/maintainers'));
    }

    public function testCanFlushUrl()
    {
        $this->mockRequest('/maintainer/anahkiasen');
        $this->cache->storeCache('foobar');

        $this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
        $this->cache->flushUrl('http://localhost/maintainer/.+');
        $this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
    }

    public function testCanFlushRoute()
    {
        $this->app['url']->shouldReceive('route')->with('maintainer', 'anahkiasen')->once()->andReturn('http://localhost/maintainer/anahkiasen');

        $this->mockRequest('/maintainer/anahkiasen');
        $this->cache->storeCache('foobar');

        $this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
        $this->cache->flushRoute('maintainer', 'anahkiasen');
        $this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
    }

    public function testCanFlushAction()
    {
        $this->app['url']->shouldReceive('action')->with('MaintainersController@maintainer', 'anahkiasen')->once()->andReturn('http://localhost/maintainer/anahkiasen');

        $this->mockRequest('/maintainer/anahkiasen');
        $this->cache->storeCache('foobar');

        $this->assertTrue($this->app['cache']->has('GET-/maintainer/anahkiasen'));
        $this->cache->flushAction('MaintainersController@maintainer', 'anahkiasen');
        $this->assertFalse($this->app['cache']->has('GET-/maintainer/anahkiasen'));
    }

    public function testCanMinifyHtmlContent()
    {
        $this->app['config']->set('flatten.minify', true);

        $content = <<<EOF
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <style>
        .foo {
            background: red;
        }
    </style>
</head>
<body>
    <main>
        <pre>
            var foo = '
                bar';
        </pre>
    </main>

    <script>
        var foo = 'foo';
        var bar = 'bar';
    </script>
</body>
</html>
EOF;

        $expected =
            '<!doctype html><html
lang="en"><head><meta
charset="UTF-8"><title>Document</title><style>.foo{background:red}</style></head><body>
<main><pre>
            var foo = \'
                bar\';
        </pre></main> <script>var foo=\'foo\',bar=\'bar\'</script> </body></html>';

        $this->cache->storeCache($content);
        $this->assertEquals($expected, $this->cache->getCache());
    }
}
