<?php
use Illuminate\View\ViewServiceProvider;

class TemplatingTest extends FlattenTests
{
	public function testCanCacheSections()
	{
		$section = $this->templating->section('foobar', function() {
			?><h1>Header</h1><?php
		});
		$this->assertEquals('<h1>Header</h1>', $section);

		$section = $this->templating->section('foobar', function() {
			?><h1>NewHeader</h1><?php
		});
		$this->assertEquals('<h1>Header</h1>', $section);

		$this->app['cache']->flush();
		$section = $this->templating->section('foobar', function() {
			?><h1>NewHeader</h1><?php
		});
		$this->assertEquals('<h1>NewHeader</h1>', $section);
	}

	public function testCanCompileBladeTags()
	{
		// Bind ViewProvider
		$this->app['events'] = Mockery::mock('Illuminate\Events\Dispatcher');
		$this->app['config']->set('view.paths', array('views'));
		$viewProvider = new ViewServiceProvider($this->app);
		$viewProvider->registerEngineResolver();
		$viewProvider->registerViewFinder();
		$viewProvider->registerEnvironment();

		// Register tags
		$this->app['flatten.templating']->registerTags();

		$blade = $this->app['view']->getEngineResolver()->resolve('blade')->getCompiler();
		$this->assertEquals("<?php echo Flatten\Facades\Template::section('section', 512, function() { ?>", $blade->compileString("@cache('section', 512)"));
		$this->assertEquals("<?php echo Flatten\Facades\Template::section('section', function() { ?>", $blade->compileString("@cache('section')"));
		$this->assertEquals("<?php }); ?>", $blade->compileString("@endcache"));
	}
}
