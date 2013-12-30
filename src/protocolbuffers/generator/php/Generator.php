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
use protocolbuffers\StringStream;
use Symfony\Component\Yaml\Yaml;

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
                             $paramter = array(),
                             GeneratorContext $context,
                             StringStream $error) {
        $file_list = array();

        try {
            $file_generator = new FileGenerator($context, $file);

            if (!$context->hasOpened("autoload.php")) {
                // NOTE: generate autoloader first. it's easier to reuse for extension
                $printer = new Printer($context->open("autoload.php"), "`");
                $append_mode = false;
                $file_generator->generateAutoloader($printer, array(), $append_mode);
            }

            if (Helper::IsPackageNameOverriden($file)) {
                $package_name = Helper::getPackageName($file);
            } else {
                $package_name = Helper::phppackage($file);
            }

            $package_dir = $this->phppackagetodir($package_name);
            $printer = new Printer($context->open($file->getName() . ".php"), "`");
            $file_generator->generate($printer);
            $file_generator->generateSiblings($package_dir, $context, $file_list);

            $printer = new Printer($context->openForInsert("autoload.php", "autoloader_scope:classmap"), "`");
            $file_generator->generateAutoloader($printer, $file_list, true);
        } catch (\Exception $e) {
            $error->assign($e->getMessage() . "\n" . $e->getTraceAsString());
        }

    }
}
