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
use protocolbuffers\MessagePool;

class MessageGenerator
{
    protected $file;

    protected $descriptor;

    protected $context;

    protected $file_list;

    protected $enclose_namespace_ = false;

    protected static $fields_map = array(
        "DUMMY",
        "\\ProtocolBuffers::TYPE_DOUBLE",
        "\\ProtocolBuffers::TYPE_FLOAT",
        "\\ProtocolBuffers::TYPE_INT64",
        "\\ProtocolBuffers::TYPE_UINT64",
        "\\ProtocolBuffers::TYPE_INT32",
        "\\ProtocolBuffers::TYPE_FIXED64",
        "\\ProtocolBuffers::TYPE_FIXED32",
        "\\ProtocolBuffers::TYPE_BOOL",
        "\\ProtocolBuffers::TYPE_STRING",
        "\\ProtocolBuffers::TYPE_GROUP",
        "\\ProtocolBuffers::TYPE_MESSAGE",
        "\\ProtocolBuffers::TYPE_BYTES",
        "\\ProtocolBuffers::TYPE_UINT32",
        "\\ProtocolBuffers::TYPE_ENUM",
        "\\ProtocolBuffers::TYPE_SFIXED32",
        "\\ProtocolBuffers::TYPE_SFIXED64",
        "\\ProtocolBuffers::TYPE_SINT32",
        "\\ProtocolBuffers::TYPE_SINT64",
    );

    public function __construct(GeneratorContext $context,
                                \google\protobuf\FileDescriptorProto $file,
                                \google\protobuf\DescriptorProto $descriptor,
                                &$file_list)
    {
        $this->file = $file;
        $this->descriptor = $descriptor;
        $this->context = $context;
        $this->file_list = &$file_list;

        if ($file->getOptions()->getExtension("php")->getMultipleFiles()) {
            $this->enclose_namespace_ = false;
        } else {
            $this->enclose_namespace_ = true;
        }
    }

    public function hasNameSpace()
    {
        if ($this->getNameSpace()) {
            return true;
        } else {
            return false;
        }
    }

    public function getNameSpace()
    {
        $args = explode(".", $this->descriptor->full_name);
        array_pop($args);
        if (count($args)) {
            $tmp = join("\\", $args);
            return ltrim($tmp, "\\");
        } else {
            return;
        }
    }

    public function printUseNameSpaceIfNeeded(Printer $printer)
    {
        if ($this->hasNameSpace()) {
            if ($this->enclose_namespace_) {
                $printer->put(
                    "namespace `namespace`\n{\n\n",
                    "namespace",
                    $this->getNameSpace());
            } else {
                $printer->put(
                    "namespace `namespace`;\n\n",
                    "namespace",
                    $this->getNameSpace());
            }
        } else {
            if ($this->enclose_namespace_) {
                $printer->put("namespace {\n\n");
            }
        }

//  NOTE: Printing use statement is troublesome in writing single file.
//  printer->Print("use \\ProtocolBuffers;\n");
//  printer->Print("use \\ProtocolBuffers\\Message;\n");
//  printer->Print("use \\ProtocolBuffers\\FieldDescriptor;\n");
//  printer->Print("use \\ProtocolBuffers\\DescriptorBuilder;\n");
//  printer->Print("use \\ProtocolBuffers\\ExtensionRegistry;\n");

        // TODO(chobie): add Message and Enum class here.

        $printer->put("// @@protoc_insertion_point(namespace:`name`)\n",
        "name", $this->descriptor->full_name);
        $printer->put("\n");
    }

    public function printProperties(Printer $printer)
    {
        if ($this->descriptor->getOptions()->getExtension("php_option")->getUseSingleProperty()) {
            $printer->put("/** @var array \$`var` */\n",
                "var", $this->descriptor->getOptions()->getExtension("php_option")->getSinglePropertyName()
            );
            $printer->put("protected \$`var` = array();\n",
                "var", $this->descriptor->getOptions()->getExtension("php_option")->getSinglePropertyName()
            );
        } else {
            foreach ($this->descriptor->getField() as $field) {
                /* @var $field \google\protobuf\FieldDescriptorProto */

                $printer->put("/** @var `type` $`var` tag:`tag` ",
                    "type", $this->getTypeName($field),
                    "var", $field->getName(),
                    "tag", $field->getNumber());
                $printer->put(" `required` */\n",
                    "required", ($field->getLabel() == FieldDescriptorProto\Label::LABEL_REQUIRED ? "required" : "optional"));
                $printer->put("protected \$`name`;\n",
                    "name",
                    $field->getName()
                );
            }
        }


        $printer->put("\n");
        $printer->put("// @@protoc_insertion_point(properties_scope:`name`)\n\n",
            "name", $this->descriptor->full_name
        );
    }

    public function printGetDescriptor(Printer $printer)
    {
        $php_options = $this->descriptor->getOptions();

        $printer->put("/**\n");
        $printer->put(" * get descriptor for protocol buffers\n");
        $printer->put(" * \n");
        $printer->put(" * @return \\ProtocolBuffersDescriptor\n");
        $printer->put(" */\n");
        $printer->put("public static function getDescriptor()\n");
        $printer->put("{\n");
        $printer->indent();
        $printer->put("static \$descriptor;\n");
        $printer->put("\n");
        $printer->put("if (!isset(\$descriptor)) {\n");
        $printer->indent();
        $printer->put("\$desc = new `class_name`();\n",
            "class_name",
            "\\ProtocolBuffers\\DescriptorBuilder");

        foreach ($this->descriptor->getField() as $offset => $field) {
            /* @var $field \google\protobuf\FieldDescriptorProto */
            $printer->put("\$desc->addField(`tag`, new `class_name`(array(\n",
                "tag",
                $field->getNumber(),
                "class_name",
                "\\ProtocolBuffers\\FieldDescriptor");
            $printer->indent();
            $printer->put("\"type\"     => `type`,\n",
                "type",
                self::$fields_map[$field->getType()]);
            $printer->put("\"name\"     => \"`name`\",\n",
                "name",
                $field->getName());
            $printer->put("\"required\" => `required`,\n",
                "required",
                ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_REQUIRED) ? "true" : "false");
            $printer->put("\"optional\" => `optional`,\n",
                "optional",
                ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_OPTIONAL) ? "true" : "false");
            $printer->put("\"repeated\" => `repeated`,\n",
                "repeated",
                ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_REPEATED) ? "true" : "false");

            $options = $field->getOptions();
            if ($options) {
                $printer->put("\"packable\" => `packable`,\n",
                    "packable",
                    ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_REPEATED &&
                        $field->getOptions()->getPacked()) ? "true" : "false");
            } else {
                $printer->put("\"packable\" => `packable`,\n",
                    "packable",
                    "false");
            }
            $printer->put("\"default\"  => `value`,\n",
                "value",
                $this->defaultValueAsString($field));

            if ($field->getType() == \google\protobuf\FieldDescriptorProto\Type::TYPE_MESSAGE) {
                $name = $field->getTypeName();

                $descriptor = MessagePool::get($name);
                $printer->put("\"message\" => \"`message`\",\n",
                    "message",
                    str_replace(".", "\\\\", $descriptor->full_name)
                );
            }

            $printer->outdent();
            $printer->put(")));\n");
        }

        if ($php_options instanceof \google\protobuf\MessageOptions) {
            $php_message_options = $php_options->getExtension("php_option");
            //error_log(var_export($php_options, true));
            //error_log(var_export($php_message_options, true));

            if ($php_message_options->getUseSingleProperty()) {
                $printer->put("\$phpoptions = \$desc->getOptions()->getExtension");
                $printer->put("(\\ProtocolBuffers::PHP_MESSAGE_OPTION);\n");
                $printer->put("\$phpoptions->setUseSingleProperty(true);\n");
                $printer->put("\$phpoptions->setSinglePropertyName(\"`name`\");\n",
                    "name",
                    $php_message_options->getSinglePropertyName());

                $printer->put("\n");
            }
        }


        $printer->put("// @@protoc_insertion_point(builder_scope:`name`)\n\n",
            "name", $this->descriptor->full_name);

        $printer->put("\$descriptor = \$desc->build();\n");
        $printer->outdent();
        $printer->put("}\n");

        $printer->put("return \$descriptor;\n");
        $printer->outdent();
        $printer->put("}\n\n");
    }

    public function fileName()
    {
        $name = $this->descriptor->full_name;
        return str_replace(".", DIRECTORY_SEPARATOR, $name) . ".php";
    }

    public function printTraitsInsertionPoint(Printer $printer)
    {
        $printer->put("// @@protoc_insertion_point(traits:`name`)\n",
            "name", $this->descriptor->full_name);
        $printer->put("\n");
    }

    public function getTypeName(FieldDescriptorProto $field)
    {
        if ($field->getLabel() == FieldDescriptorProto\Label::LABEL_REPEATED) {
            return "array";
        }

        $default_type = "string";
        switch ($field->getType()) {
            case \ProtocolBuffers::TYPE_DOUBLE:
            case \ProtocolBuffers::TYPE_FLOAT:
            case \ProtocolBuffers::TYPE_INT64:
            case \ProtocolBuffers::TYPE_UINT64:
            case \ProtocolBuffers::TYPE_INT32:
            case \ProtocolBuffers::TYPE_FIXED64:
            case \ProtocolBuffers::TYPE_FIXED32:
                return $default_type;
                break;
            case \ProtocolBuffers::TYPE_BOOL:
                return "bool";
            case \ProtocolBuffers::TYPE_STRING:
                return $default_type;
            case \ProtocolBuffers::TYPE_GROUP:
                return;
            case \ProtocolBuffers::TYPE_MESSAGE:
                return str_replace(".", "\\", $field->getTypeName());
                break;
            case \ProtocolBuffers::TYPE_BYTES:
            case \ProtocolBuffers::TYPE_UINT32:
                return $default_type;
                break;
            case \ProtocolBuffers::TYPE_ENUM:
                return str_replace(".", "\\", $field->getTypeName());
                break;
            case \ProtocolBuffers::TYPE_SFIXED32:
            case \ProtocolBuffers::TYPE_SFIXED64:
            case \ProtocolBuffers::TYPE_SINT32:
            case \ProtocolBuffers::TYPE_SINT64:
                return $default_type;
                break;
        }
    }

    public function defaultValueAsString(FieldDescriptorProto $field)
    {
        $default_value = null;
        switch ($field->getType()) {
            case \ProtocolBuffers::TYPE_DOUBLE:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_FLOAT:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_INT64:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_UINT64:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_INT32:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_FIXED64:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_FIXED32:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_BOOL:
                return ($field->getDefaultValue()) ? "true" : "false";
            case \ProtocolBuffers::TYPE_STRING:
                return "\"" . addcslashes($field->getDefaultValue(), "\"\n\r\$") . "\"";
            case \ProtocolBuffers::TYPE_GROUP:
                return;
            case \ProtocolBuffers::TYPE_MESSAGE:
                return "null";
                break;
            case \ProtocolBuffers::TYPE_BYTES:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_UINT32:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_ENUM:
                $value = $field->getTypeName();
                $descriptor = MessagePool::get($value);
                if ($field->getLabel() != FieldDescriptorProto\Label::LABEL_REPEATED) {
                    if ($field->hasDefaultValue()) {
                        $value = str_replace(".", "\\", $descriptor->full_name). "::" . $field->getDefaultValue();
                    } else {
                        $value = "null";
                    }
                    return $value;
                } else {
                    return "array()";
                }
            case \ProtocolBuffers::TYPE_SFIXED32:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_SFIXED64:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_SINT32:
                $default_value = $field->getDefaultValue();
                break;
            case \ProtocolBuffers::TYPE_SINT64:
                $default_value = $field->getDefaultValue();
                break;
        }

        if (!$default_value && $default_value !== 0) {
            return "null";
        } else {
            return $default_value;
        }

    }

    public function printMagicMethod(Printer $printer)
    {
        $printer->put(" * -*- magic methods -*-\n");
        $printer->put(" *\n");
        foreach ($this->descriptor->getField() as $field) {
            $printer->put(" * @method `type` get`variable`()\n",
                "type", $this->getTypeName($field),
                "variable", Helper::cameraize($field->getName())
            );

            if ($field->getLabel() == FieldDescriptorProto\Label::LABEL_REPEATED) {
                $printer->put(" * @method void append`variable`(`type2` \$value)\n",
                    "type", $this->getTypeName($field),
                    "variable", Helper::cameraize($field->getName()),
                    "type2", $this->getTypeName($field)
                );
            } else {
                $printer->put(" * @method void set`variable`(`type2` \$value)\n",
                    "type", $this->getTypeName($field),
                    "variable", Helper::cameraize($field->getName()),
                    "type2", $this->getTypeName($field)
                );
            }
        }

    }

    public function printExtension(Printer $printer, FieldDescriptorProto $field)
    {
        $printer->put("\$registry->add(`message`, `extension`, new \\ProtocolBuffers\\FieldDescriptor(array(\n",
            "message", $this->getTypeName($field),
            "extension", $field->getNumber()
        );
        $printer->indent();
        $printer->put("\"type\"     => `type`,\n",
            "type",
            self::$fields_map[$field->getType()]);
        $printer->put("\"name\"     => \"`name`\",\n",
            "name",
            $field->getName());
        $printer->put("\"required\" => `required`,\n",
            "required",
            ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_REQUIRED) ? "true" : "false");
        $printer->put("\"optional\" => `optional`,\n",
            "optional",
            ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_OPTIONAL) ? "true" : "false");
        $printer->put("\"repeated\" => `repeated`,\n",
            "repeated",
            ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_REPEATED) ? "true" : "false");

        $options = $field->getOptions();
        if ($options) {
            $printer->put("\"packable\" => `packable`,\n",
                "packable",
                ($field->getLabel() == \google\protobuf\FieldDescriptorProto\Label::LABEL_REPEATED &&
                    $field->getOptions()->getPacked()) ? "true" : "false");
        } else {
            $printer->put("\"packable\" => `packable`,\n",
                "packable",
                "false");
        }
        $printer->put("\"default\"  => `value`,\n",
            "value",
            $this->defaultValueAsString($field));

        if ($field->getType() == \google\protobuf\FieldDescriptorProto\Type::TYPE_MESSAGE) {
            $name = $field->getTypeName();

            $descriptor = MessagePool::get($name);
            $printer->put("\"message\" => \"`message`\",\n",
                "message",
                str_replace(".", "\\\\", $descriptor->full_name)
            );
        }

        $printer->outdent();
        $printer->put(")));\n");
    }

    public function printExtensions()
    {
        if ($this->file->getExtension()) {
            $printer = new Printer($this->context->openForInsert("autoload.php", "extension_scope:registry"), "`");
            foreach ($this->file->getExtension() as $ext) {
                $this->printExtension($printer, $ext);
            }
        }
    }


    public function generate(Printer $printer)
    {
        foreach ($this->descriptor->getEnumType() as $enum) {
            $generator = new EnumGenerator($this->context, $this->file, $enum, $this->file_list);

            if ($this->file->getOptions()->GetExtension("php")->getMultipleFiles()) {
                $child_name = $generator->fileName();
                $this->file_list[] = $child_name;
                $child_printer = new Printer($this->context->open($child_name), "`");
                $generator->generate($child_printer);
            }
        }

        foreach ($this->descriptor->getNestedType() as $message) {
            $generator = new MessageGenerator($this->context, $this->file, $message, $this->file_list);

            if ($this->file->getOptions()->GetExtension("php")->getMultipleFiles()) {
                $child_name = $generator->fileName();
                $this->file_list[] = $child_name;
                $child_printer = new Printer($this->context->open($child_name), "`");
                $generator->generate($child_printer);
            }

        }

        if ($this->file->getOptions()->getExtension("php")->getMultipleFiles()) {
            $printer->put("<?php\n");
        }

        $this->printUseNameSpaceIfNeeded($printer);

        $printer->put(
            "/**\n" .
            " * Generated by the protocol buffer compiler.  DO NOT EDIT!\n" .
            " * source: `filename`\n" .
            " *\n",
            "filename",
            $this->file->getName()
        );
        $this->printMagicMethod($printer);
        $printer->put(" */\n");

        $printer->put("class `name` extends \\ProtocolBuffers\\Message\n{\n",
            "name",
            $this->descriptor->getName()
        );
        $printer->indent();

        $this->printTraitsInsertionPoint($printer);
        $this->printProperties($printer);
        $printer->put("// @@protoc_insertion_point(class_scope:`name`)\n\n",
            "name", $this->descriptor->full_name);

        $this->printGetDescriptor($printer);

        $printer->outdent();
        $printer->put("}\n");

        if ($this->enclose_namespace_) {
            $printer->outdent();
            $printer->put("}\n\n");
        }
    }
}

