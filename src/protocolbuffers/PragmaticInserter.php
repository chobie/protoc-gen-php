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

    public static function execute($descriptor, GeneratorContext $context)
    {
        foreach (self::$data as $block) {
            $regex = sprintf("/%s/", $block['match']);
            if (preg_match($regex, $descriptor->getName(), $match)) {
                $file_name = $block['file'];
                $insertion_point = $block['insertion_point'];

                $count = count($match);
                for ($i = 0; $i < $count; $i++) {
                    $file_name = str_replace("\$$i", $match[0], $file_name);
                    $insertion_point = str_replace("\$$i", $match[0], $insertion_point);
                }

                if ($context->hasOpened($file_name)) {
                    $printer = new Printer($context->openForInsert($file_name, $insertion_point), "`");
                    $lines = preg_split("/\r?\n/", $block['insertion']);
                    foreach ($lines as $line) {
                        $printer->put($line . PHP_EOL);
                    }

                }
            }
        }

    }
}
