<?php
namespace protocolbuffers;

class ZerocopyOutputStream
{
    protected $buffer = array();

    public function write($message)
    {
        $this->buffer[] = $message;
    }

    public function getContent()
    {
        return join("", $this->buffer);
    }
}