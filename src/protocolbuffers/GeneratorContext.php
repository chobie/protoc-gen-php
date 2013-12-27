<?php
namespace protocolbuffers;

class GeneratorContext
{
    protected $context = array();

    public function open($name)
    {
        $stream = new ZerocopyOutputStream();
        $this->context[$name] = $stream;

        return $stream;
    }

    public function getContexts()
    {
        return $this->context;
    }
}
