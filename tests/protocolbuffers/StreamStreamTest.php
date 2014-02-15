<?php

class StringStreamTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_append_string()
    {
        $stream = new protocolbuffers\StringStream("Hello ");
        $stream->append("world");

        $this->assertEquals("Hello world", $stream->__toString());
    }

    /** @test */
    public function it_should_assign_string()
    {
        $stream = new protocolbuffers\StringStream("Hello ");
        $stream->assign("world");

        $this->assertEquals("world", $stream->__toString());
    }

    /** @test */
    public function it_should_constructive_with_no_args()
    {
        $stream = new protocolbuffers\StringStream();

        $this->assertEquals("", $stream->__toString());
    }

}