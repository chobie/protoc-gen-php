<?php
namespace protocolbuffers;

class StringStream
{
    protected $buffer = "";

    public function __construct($string = "")
    {
        $this->buffer = $string;
    }

    public function append($string)
    {
        $this->buffer .= $string;
    }

    public function assign($message)
    {
        $this->buffer = $message;
    }

    public function __toString()
    {
        return $this->buffer;
    }
}