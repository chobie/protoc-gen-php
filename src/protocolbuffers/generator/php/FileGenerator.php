<?php
/*
 * This file is part of the protoc-gen-php package.
 *
 * (c) Shuhei Tanuma <shuhei.tanuma@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace protocolbuffers\generator\php;

use google\protobuf\FieldDescriptorProto;
use protocolbuffers\GeneratorContext;
use protocolbuffers\io\Printer;

class FileGenerator
{
    protected $file;
    protected $context;

    public function __construct(GeneratorContext $context,
                                \google\protobuf\FileDescriptorProto $file)
    {
        $this->context = $context;
        $this->file = $file;
    }

    public function generate(Printer $printer)
    {
        $printer->put("<?php\n");

        if (!$this->file->getOptions()->getExtension("php")->getMultipleFiles()) {
            $file_list = array();

            foreach ($this->file->getEnumType() as $enum) {
                $gen = new EnumGenerator($this->context, $enum, $file_list);
                $gen->generate($printer);
            }

            foreach ($this->file->getMessageType() as $message) {
                $gen = new MessageGenerator($this->context, $message, $file_list);
                $gen->generate($printer);
            }
        } else {
            $printer->put("require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';\n");
        }
    }

    public function phppackage()
    {
        $package = getEnv("PACKAGE");
        if ($package) {
            $result = $package;
        } else if ($this->file->getOptions()->getJavaPackage()) {
            $result = $this->file->getOptions()->getJavaPackage();
        } else {
            $result = "";
            if ($this->file->getPackage()) {
                if (!$result) {
                    $result .= ".";
                }
                $result .= $this->file->getPackage();
            }
        }

        return $result;
    }

    public function getNameSpace()
    {
        $package = $this->file->getPackage();
        if (!$package) {
            $args = explode(".", $package);
            array_pop($args);
            $output = join("\\", $args);
        } else {
            $output = "";
        }

        return $output;
    }

    public function generateAutoloader(Printer $printer, $file_list, $append_mode = false)
    {
        if (!$append_mode) {
            $printer->put("<?php\n");
            $printer->put("spl_autoload_register(function(\$name){\n");
            $printer->indent();
            $printer->put("static \$classmap;\n");
            $printer->put("if (!\$classmap) {\n");
            $printer->indent();
            $printer->put("\$classmap = array(\n");
            $printer->indent();
        }

        foreach ($file_list as $file) {
            $tmp = str_replace("\\", "/", $file);
            $key = str_replace("/", "\\", substr($file, 0, strrpos($file, ".")));

            $printer->put("'`key`' => '`path`',\n",
                    "key", ltrim($key, "\\"),
                    "path", "/" . ltrim($tmp, "/")
            );
        }

        if (!$append_mode) {
            $printer->put("// @@protoc_insertion_point(autoloader_scope:classmap)\n");
            $printer->outdent();
            $printer->put(");\n");
            $printer->outdent();
            $printer->put("}\n");
            $printer->put("if (isset(\$classmap[\$name])) {\n");
            $printer->indent();
            $printer->put("require __DIR__ . DIRECTORY_SEPARATOR . \$classmap[\$name];\n");
            $printer->outdent();
            $printer->put("}\n");
            $printer->outdent();
            $printer->put("});\n");
            $printer->put("\n");
            $printer->put("call_user_func(function(){\n");
            $printer->indent();
            $printer->put("\$registry = \\ProtocolBuffers\\ExtensionRegistry::getInstance();\n");
            $printer->put("// @@protoc_insertion_point(extension_scope:registry)\n");
            $printer->outdent();
            $printer->put("});\n");
        }
    }

    public function generateSiblings($package_name, GeneratorContext $context, &$file_list)
    {
        if ($this->file->getOptions()->getExtension("php")->getMultipleFiles()) {

            foreach ($this->file->getEnumType() as $enum) {
                $enum->full_name = Helper::getPackageName($this->file) . "." . $enum->getName();

                $path = $package_name . DIRECTORY_SEPARATOR . $enum->getName() . ".php";
                $output = $context->open($path);
                $file_list[] = $path;

                $printer = new Printer($output, "`");
                $gen = new EnumGenerator($context, $enum, $file_list);
                $gen->generate($printer);
            }

            foreach ($this->file->getMessageType() as $message) {
                $message->full_name = Helper::getPackageName($this->file) . "." . $message->getName();

                $path = $package_name . DIRECTORY_SEPARATOR . $message->getName() . ".php";
                $output = $context->open($path);
                $file_list[] = $path;

                $printer = new Printer($output, "`");
                $gen = new MessageGenerator($context, $message, $file_list);
                $gen->generate($printer);
            }

            /* TODO(chobie): add service here */
        }
    }
}
