<?php
namespace protocolbuffers;

class ZerocopyOutputStream
{
    protected $buffer;

    public function __construct(&$ref)
    {
        $this->buffer = &$ref;
    }

    public function write($message)
    {
        $this->buffer .= $message;
    }
}