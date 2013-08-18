<?php
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
}
