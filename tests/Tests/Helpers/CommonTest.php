<?php


namespace Tests\Helpers;


use O2System\Kernel\Http\Message\Request;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    public function testIsPhp()
    {
        $result = is_php('7.4');
        $this->assertTrue($result);
    }

    public function testIsTrue()
    {
        $result = is_true(true);
        $this->assertTrue($result);
    }

    public function testIsAjax()
    {
        $request = new Request();
        $this->assertFalse($request->isAjax());
    }

}
