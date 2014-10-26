<?php
namespace Flatten;

use Flatten\TestCases\FlattenTestCase;
use Illuminate\View\ViewServiceProvider;
use Mockery;

class TemplatingTest extends FlattenTestCase
{
	public function testCanCacheSections()
	{
		$section = $this->templating->section('foobar', function () {
			?><h1>Header</h1><?php
		});
		$this->assertEquals('<h1>Header</h1>', $section);

		$section = $this->templating->section('foobar', function () {
			?><h1>NewHeader</h1><?php
		});
		$this->assertEquals('<h1>Header</h1>', $section);

		$this->app['cache']->flush();
		$section = $this->templating->section('foobar', function () {
			?><h1>NewHeader</h1><?php
		});
		$this->assertEquals('<h1>NewHeader</h1>', $section);
		$this->assertTrue($this->app['cache']->has('flatten-section-foobar'));
	}

	public function testCanCacheSectionsWithVariables()
	{
		$title   = 'Header';
		$section = $this->templating->section('foobar', function () use ($title) {
			?><h1><?= $title ?></h1><?php
		});
		$this->assertEquals('<h1>Header</h1>', $section);

		$title   = 'NewHeader';
		$section = $this->templating->section('foobar', function () use ($title) {
			?><h1><?= $title ?></h1><?php
		});
		$this->assertEquals('<h1>Header</h1>', $section);

		$this->app['cache']->flush();
		$section = $this->templating->section('foobar', function () use ($title) {
			?><h1><?= $title ?></h1><?php
		});
		$this->assertEquals('<h1>NewHeader</h1>', $section);
		$this->assertTrue($this->app['cache']->has('flatten-section-foobar'));
	}

	public function testCanFlushSection()
	{
		$section = $this->templating->section('foo', function () {
			?><h1>foo</h1><?php
		});

		$section = $this->templating->section('bar', function () {
			?><h1>bar</h1><?php
		});

		$this->assertTrue($this->app['cache']->has('flatten-section-foo'));
		$this->assertTrue($this->app['cache']->has('flatten-section-bar'));
		$this->templating->flushSection('foo');
		$this->assertFalse($this->app['cache']->has('flatten-section-foo'));
		$this->assertTrue($this->app['cache']->has('flatten-section-bar'));
	}

	public function testCanCompileBladeTags()
	{
		// Bind ViewProvider
		$this->app['events'] = Mockery::mock('Illuminate\Events\Dispatcher');
		$this->app['config']->set('view.paths', array('views'));
		$viewProvider = new ViewServiceProvider($this->app);
		$viewProvider->registerEngineResolver();
		$viewProvider->registerViewFinder();
		if (method_exists($viewProvider, 'registerEnvironment')) {
			$viewProvider->registerEnvironment();
		} else {
			$viewProvider->registerFactory();
		}

		// Register tags
		$this->app['flatten.templating']->registerTags();

		$blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();
		$this->assertEquals("<?php echo Flatten\Facades\Flatten::section('section', 512, function() { ?>", $blade->compileString("@cache('section', 512)"));
		$this->assertEquals("<?php echo Flatten\Facades\Flatten::section('section', function() { ?>", $blade->compileString("@cache('section')"));
		$this->assertEquals("<?php }); ?>", $blade->compileString("@endcache"));
	}
}
