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
use google\protobuf\FileDescriptorProto;
use protocolbuffers\io\Printer;
use Symfony\Component\Yaml\Yaml;

class PragmaticInserter
{
    protected static $data = array();

    public static function loadYaml($path)
    {
        if (file_exists($path)) {
            self::$data = Yaml::parse(file_get_contents($path));
        }
    }

    public static function execute(DescriptorProto $descriptor, GeneratorContext $context)
    {
        foreach (self::$data as $block) {
            if ($descriptor->getName() == $block['match']) {
                if ($context->hasOpened($block['file'])) {
                    $printer = new Printer($context->openForInsert($block['file'], $block['insertion_point']), "`");

                    $lines = preg_split("/\r?\n/", $block['insertion']);
                    foreach ($lines as $line) {
                        $printer->put($line . PHP_EOL);
                    }

                }
            }
        }

    }
}
