<?php
class FlattenTest extends FlattenTests
{
	public function testCanComputeHash()
	{
		$this->assertEquals('GET-foobar', $this->flatten->computeHash('foobar'));
	}
}