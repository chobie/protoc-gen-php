<?php
/*
 * This file is part of the protoc-gen-php package.
 *
 * (c) Shuhei Tanuma <shuhei.tanuma@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace protocolbuffers;

use protocolbuffers\generator\php\Generator;

class Compiler
{
    public function __construct()
    {
        fwrite(STDERR, "# protoc-gen-php\n");
    }

    /**
     * @param $input raw protocol buffers message
     * @return \google\protobuf\compiler\CodeGeneratorResponse
     */
    public function compile($input)
    {
        $packages = array();

        $req = \ProtocolBuffers::decode('google\protobuf\compiler\CodeGeneratorRequest', $input);
        /* @var $req \google\protobuf\compiler\CodeGeneratorRequest */

        $resp = new \google\protobuf\compiler\CodeGeneratorResponse();
        $context = new GeneratorContext($resp);

        $gen = new Generator();
        $parameter = array();
        $error = "";

        foreach ($req->getProtoFile() as $file_descriptor) {
            if ($file_descriptor->getName() == "proto/google/protobuf/descriptor.proto") {
                continue;
            }
            if ($file_descriptor->getName() == "php_options.proto") {
                continue;
            }

            $gen->generate($file_descriptor, $parameter, $context, $error);
        }

        $resp->setError($error);
        return $resp;
    }
}