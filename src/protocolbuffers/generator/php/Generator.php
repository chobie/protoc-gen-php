<?php
namespace protocolbuffers\generator\php;

use protocolbuffers\GeneratorContext;

class Generator
{
    public function __construct()
    {
    }

    public function generate(\google\protobuf\FileDescriptorProto $file,
                             $paramter,
                             GeneratorContext $context,
                             &$error) {
        // google\protobuf\FileDescriptorProto
        //error_log(var_export($file, true));

        $file = new FileGenerator($file);
        $file->generate();
        $file->generateSiblings($context);


    }
}
