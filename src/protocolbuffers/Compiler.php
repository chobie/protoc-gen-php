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
        //$req->parseFromString($input);
        //error_log(var_export($req, 1));
        /* @var $req \google\protobuf\compiler\CodeGeneratorRequest */

        $resp = new \google\protobuf\compiler\CodeGeneratorResponse();
        $context = new GeneratorContext($resp);

        $gen = new Generator();
        $parameter = array();
        $error = "";
        foreach ($req->getProtoFile() as $file_descriptor) {
            //echo "# " . $file_descriptor->getName() . PHP_EOL;

            if ($file_descriptor->getName() == "proto/google/protobuf/descriptor.proto") {
                //error_log(var_export($file_descriptor, true));
                continue;
            }
            if ($file_descriptor->getName() == "php_options.proto") {
                continue;
            }
            //error_log("* " . $file_descriptor->getName());

            $gen->generate($file_descriptor, $parameter, $context, $error);
        }

        $resp->setError($error);
        return $resp;
    }
}