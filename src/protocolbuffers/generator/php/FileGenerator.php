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
            $tmp = str_replace("/", "\\", $file);
            $key = substr($file, 0, strrpos($file, "."));

            $printer->put("'`key`' => '`path`',\n",
                    "key", $key,
                    "path", $tmp
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

    public function generateSiblings(GeneratorContext $context, &$file_list)
    {
        foreach ($this->file->getMessageType() as $message) {
            $path = $message->getName() . ".php";
            $output = $context->open($path);
            $file_list[] = $path;

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
