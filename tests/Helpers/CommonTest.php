<?php


namespace Tests\Helpers;


use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    public function testIsPhp()
    {
        $result = is_php(phpversion());
        $this->assertTrue($result, 'the php version is'. phpversion());
    }

    public function testIsTrue()
    {
        $result = is_true(true);
        $this->assertFalse(false);
        $this->assertTrue($result);
    }

}
