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

use google\protobuf\DescriptorProto;
use google\protobuf\EnumDescriptorProto;
use google\protobuf\FieldDescriptorProto;
use google\protobuf\FileDescriptorProto;
use protocolbuffers\generator\php\Generator;
use protocolbuffers\generator\php\Helper as MyHelper;

class Compiler
{
    /* @var FileDescriptorProto $file */
    protected $file;

    public function __construct()
    {
        fwrite(STDERR, "# protoc-gen-php\n");
    }

    protected function setupFullNameForEnum(EnumDescriptorProto $enum, $package_name = "")
    {
        $enum->full_name = $package_name . "." . $enum->getName();
        $enum->package_name = $package_name;
        MessagePool::register($enum->full_name, $enum);
    }

    protected function setupFullNameForMessage(DescriptorProto $message, $package_name = "")
    {
        $new_package_name = $package_name . "." . $message->getName();
        foreach ($message->getEnumType() as $enum) {
            $this->setupFullNameForEnum($enum, $new_package_name);
        }

        foreach ($message->getNestedType() as $m) {
            $this->setupFullNameForMessage($m, $new_package_name);
        }

        $message->full_name = $package_name . "." .$message->getName();
        $message->package_name = $package_name;
        MessagePool::register($message->full_name, $message);

        if (MyHelper::IsPackageNameOverriden($this->file)) {
            foreach ($message->getField() as $field) {
                /** @var $field FieldDescriptorProto */

                if ($field->getType() == \ProtocolBuffers::TYPE_MESSAGE ||
                    $field->getType() == \ProtocolBuffers::TYPE_ENUM) {

                    $name = $field->getTypeName();
                    $package = $this->file->getPackage();

                    if ($package) {
                        $name = str_replace($package, MyHelper::getPackageName($this->file), $name);
                    } else {
                        $name =  MyHelper::getPackageName($this->file) . $name;
                    }
                    $name = preg_replace("/^\.+/", ".", $name);
                    $field->setTypeName($name);
                }
            }
        }
    }

    public function setupFullName(\google\protobuf\compiler\CodeGeneratorRequest $req)
    {
        foreach ($req->getProtoFile() as $file_descriptor) {
            $this->file = $file_descriptor;

            if (MyHelper::IsPackageNameOverriden($file_descriptor)) {
                $package_name = MyHelper::getPackageName($file_descriptor);
            } else {
                $package_name = MyHelper::phppackage($file_descriptor);
            }

            /* @var $file_descriptor FileDescriptorProto */
            foreach ($file_descriptor->getEnumType() as $enum) {
                $this->setupFullNameForEnum($enum, $package_name);
            }
            foreach ($file_descriptor->getMessageType() as $message) {
                $this->setupFullNameForMessage($message, $package_name);
            }

            $file_descriptor->setPackage(MyHelper::getPackageName($file_descriptor));
        }
    }

    /**
     * @param $input raw protocol buffers message
     * @return \google\protobuf\compiler\CodeGeneratorResponse
     */
    public function compile($input)
    {
        $packages = array();

        $req = \ProtocolBuffers::decode('google\protobuf\compiler\CodeGeneratorRequest', $input);
        $this->setupFullName($req);
        /* @var $req \google\protobuf\compiler\CodeGeneratorRequest */

        $parameter = array();
        $resp = new \google\protobuf\compiler\CodeGeneratorResponse();
        $context = new GeneratorContext($resp);
        $gen = new Generator();
        $error = new StringStream();

        //error_log(var_export($req->getFileToGenerate(), true));
        foreach ($req->getProtoFile() as $file_descriptor) {
            if(!in_array($file_descriptor->getName(), $req->getFileToGenerate())) {
                error_log($file_descriptor->getName());
                continue;
            }

            $gen->generate($file_descriptor, $parameter, $context, $error);
        }

        $resp->setError($error);
        return $resp;
    }
}