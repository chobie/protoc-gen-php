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

class Generator
{
    protected $f = 0;

    public function __construct()
    {
    }

    public function generate(\google\protobuf\FileDescriptorProto $file,
                             $paramter,
                             GeneratorContext $context,
                             &$error) {
        // google\protobuf\FileDescriptorProto
        //error_log(var_export($file, true));
        $file_list = array();

        $file = new FileGenerator($file);
        $file->generate();
        $file->generateSiblings($context, $file_list);

        if (!$this->f) {
            $printer = new Printer($context->open("autoload.php"), "`");
            $append_mode = false;
            $this->f = 1;
        } else {
            $printer = new Printer($context->openForInsert("autoload.php", "autoloader_scope:classmap"), "`");
            $append_mode = true;
        }

        $file->generateAutoloader($printer, $file_list, $append_mode);
    }
}
