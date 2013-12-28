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
    public function __construct()
    {
    }

    public function phppackagetodir($name)
    {
        $package_dir = str_replace(".", "/", $name);
        if (!$package_dir)  {
            $package_dir .= "/";
        }

        return $package_dir;
    }

    public function generate(\google\protobuf\FileDescriptorProto $file,
                             $paramter,
                             GeneratorContext $context,
                             &$error) {
        // google\protobuf\FileDescriptorProto
        //error_log(var_export($file, true));
        $file_list = array();

        $file_generator = new FileGenerator($context, $file);
        $package_name = $file_generator->phppackage();
        $package_dir = $this->phppackagetodir($package_name);

        $printer = new Printer($context->open($file->getName() . ".php"), "`");
        $file_generator->generate($printer);
        $file_generator->generateSiblings($package_dir, $context, $file_list);

        if (!$context->hasOpened("autoload.php")) {
            $printer = new Printer($context->open("autoload.php"), "`");
            $append_mode = false;
        } else {
            $printer = new Printer($context->openForInsert("autoload.php", "autoloader_scope:classmap"), "`");
            $append_mode = true;
        }

        $file_generator->generateAutoloader($printer, $file_list, $append_mode);
    }
}
