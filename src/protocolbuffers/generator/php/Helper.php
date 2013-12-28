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

use google\protobuf\DescriptorProto;
use google\protobuf\FieldDescriptorProto;
use google\protobuf\FileDescriptorProto;

class Helper
{
    public static function cameraize($string)
    {
        return str_replace(' ','',ucwords(preg_replace('/[^A-Z^a-z^0-9]+/',' ',$string)));
    }

    public static function getFullQualifiedTypeName(FieldDescriptorProto $field, DescriptorProto $descriptor, FileDescriptorProto $file)
    {
        if ($field->getType() == \ProtocolBuffers::TYPE_MESSAGE || $field->getType() == \ProtocolBuffers::TYPE_ENUM) {
            $name = $field->getTypeName();
            if (strlen($name) < 1) {
                return null;
            }

            if ($name[0] == ".") {
                $name = Helper::getPackageName($file) . $name;
            } else {
                $name = $descriptor->package_name . $name;
            }
        } else {
            $name = $field->getTypeName();
        }

        return $name;
    }


    public static function phppackage(FileDescriptorProto $file)
    {
        $package = getEnv("PACKAGE");
        if ($package) {
            $result = $package;
        } else if ($file->getOptions()->getJavaPackage()) {
            $result = $file->getOptions()->getJavaPackage();
        } else {
            $result = "";
            if ($file->getPackage()) {
                if (!$result) {
                    $result .= ".";
                }
                $result .= $file->getPackage();

            }
        }

        return $result;
    }

    public static function getPackageName(FileDescriptorProto $file)
    {
        $package = getEnv("PACKAGE");
        if ($package) {
            $result = $package;
        } else if ($file->getOptions()->getJavaPackage()) {
            $result = $file->getOptions()->getJavaPackage();
        } else {
            $result = "";
            if ($file->getPackage()) {
                if (!$result) {
                    $result .= ".";
                }
                $result .= $file->getPackage();
            }
        }

        return $result;
    }

}
