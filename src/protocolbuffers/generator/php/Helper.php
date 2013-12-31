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
use google\protobuf\EnumDescriptorProto;
use google\protobuf\FieldDescriptorProto;
use google\protobuf\FileDescriptorProto;
use JsonSchema\Constraints\Type;

class Helper
{
    public static function cameraize($string)
    {
        return str_replace(' ','',ucwords(preg_replace('/[^A-Z^a-z^0-9]+/',' ',$string)));
    }

    public static function getFullQualifiedTypeName(FieldDescriptorProto $field, DescriptorProto $descriptor, FileDescriptorProto $file)
    {
        if (FieldDescriptorProto\Type::isMessage($field) || FieldDescriptorProto\Type::isEnum($field)) {
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

    public static function phpPackageToDir($name)
    {
        $package_dir = str_replace(".", DIRECTORY_SEPARATOR, $name);
        if (!$package_dir)  {
            $package_dir .= DIRECTORY_SEPARATOR;
        }

        return $package_dir;
    }

    public static function cleanupComment($line)
    {
        if (strlen($line) > 0 && $line[0] == " ") {
            $line = substr($line, 1);
        }
        $line = preg_replace("!\*/!", "", $line);
        $line = preg_replace("!/\*!", "//", $line);
        $line = preg_replace("! \*!", "//", $line);

        return $line;
    }

    public static function getClassName($descriptor, $full_qualified = false)
    {
        if ($descriptor instanceof DescriptorProto || $descriptor instanceof EnumDescriptorProto) {
            if ($full_qualified) {
                return ltrim(str_replace(".", "\\\\", $descriptor->full_name), "\\");
            } else {
                return $descriptor->getName();
            }
        } else if ($descriptor instanceof FieldDescriptorProto) {
            $name = str_replace(".", "\\", $descriptor->getTypeName());
            return $name;
        }
    }

    public static function getExtendeeClassName(FieldDescriptorProto $field)
    {
        return str_replace(".", "\\", $field->getExtendee());
    }

    public static function IsPackageNameOverriden(FileDescriptorProto $file)
    {
        $package = getEnv("PACKAGE");
        $result = null;
        if ($package) {
            $result = $package;
        } else if ($file->getOptions()->getJavaPackage()) {
            $result = $file->getOptions()->getJavaPackage();
        }

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function getPackageName(FileDescriptorProto $file)
    {
        $package = getEnv("PACKAGE");
        if ($package) {
            $result = $package;
//        } else if ($file->getOptions()->getJavaPackage()) {
//            $result = $file->getOptions()->getJavaPackage();
        } else {
            $result = "";
            if ($file->getPackage()) {
                if (!$result) {
                    $result .= ".";
                }
                $result .= $file->getPackage();
            }
        }
        $result = preg_replace("/^\.+/", ".", $result);

        return $result;
    }

}
