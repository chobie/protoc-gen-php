<?php
namespace protocolbuffers\generator\php;

use protocolbuffers\GeneratorContext;
use protocolbuffers\io\Printer;


class FileGenerator
{
    protected $file;

    public function __construct(\google\protobuf\FileDescriptorProto $file)
    {
        $this->file = $file;
    }

    public function generate()
    {
    }

    public function generateSiblings(GeneratorContext $context)
    {
        foreach ($this->file->getMessageType() as $message) {
            $output = $context->open($message->getName() . ".php");
            $printer = new Printer($output, "`");
            $gen = new MessageGenerator($this->file, $message);
            $gen->generate($printer);
        }

//            GenerateSibling<MessageGenerator>(package_dir, "",
//      file_->message_type(i),
//      context, file_list, "",
//      &MessageGenerator::Generate);
    }
}
