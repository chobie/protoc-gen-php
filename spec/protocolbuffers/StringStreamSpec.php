<?php

namespace spec\protocolbuffers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class StringStreamSpec
 * @package spec\protocolbuffers
 * @mixin \prtocolbuffers\StringStream
 */
class StringStreamSpec extends ObjectBehavior
{
    function it_is_initializeable()
    {
        $this->shouldHaveType('protocolbuffers\StringStream');
    }

    function it_should_add_string()
    {
        $this->beConstructedWith("Hello ");
        $this->append("world");
        $this->__toString()->shouldBe("Hello world");
    }

    function it_should_assign_string()
    {
        $this->beConstructedWith("Hello ");
        $this->assign("world");
        $this->__toString()->shouldBe("world");
    }

    function it_should_constructed_with_no_args()
    {
        $this->__toString()->shouldBe("");
    }
}

