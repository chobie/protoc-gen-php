<?php
namespace protocolbuffers;

use google\protobuf\compiler\CodeGeneratorResponse;

class GeneratorContext
{
    protected $response;

    public function __construct(CodeGeneratorResponse $response)
    {
        $this->response = $response;
    }

    public function open($name)
    {
        $file = new \google\protobuf\compiler\CodeGeneratorResponse\File();
        $file->setName($name);
        $this->response->appendFile($file);

        $stream = new ZerocopyOutputStream($file->getContentRef());

        return $stream;
    }

    public function openForInsert($name, $insertion_point)
    {
        $file = new \google\protobuf\compiler\CodeGeneratorResponse\File();
        $file->setName($name);
        $file->setInsertionPoint($insertion_point);
        $this->response->appendFile($file);

        $stream = new ZerocopyOutputStream($file->getContentRef());

        return $stream;

    }
}
