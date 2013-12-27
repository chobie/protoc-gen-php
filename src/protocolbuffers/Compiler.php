<?php
namespace protocolbuffers;

use protocolbuffers\generator\php\Generator;

class Compiler
{
    public function __construct()
    {
        fwrite(STDERR, "# protoc-gen-php (pure php)\n");
    }

    public function compile($input)
    {
        $packages = array();
        $req = \google\protobuf\compiler\CodeGeneratorRequest::parseFromString($input);
        /* @var $req \google\protobuf\compiler\CodeGeneratorRequest */

        $resp = new \google\protobuf\compiler\CodeGeneratorResponse();
        $context = new GeneratorContext();

        $gen = new Generator();
        $parameter = array();
        $error = "";
        foreach ($req->getProtoFile() as $file_descriptor) {
            $gen->generate($file_descriptor, $parameter, $context, $error);
        }

        $resp->setError($error);
        foreach ($context->getContexts() as $name => $c) {
            $file = new \google\protobuf\compiler\CodeGeneratorResponse\File();

            $file->setName($name);
            //$file->setInsertionPoint("hoge");
            $file->setContent($c->getContent());
            $resp->appendFile($file);
        }

        return $resp;
    }
}