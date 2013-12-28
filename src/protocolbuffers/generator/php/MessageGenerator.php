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

    public function __construct(GeneratorContext $context, \google\protobuf\FileDescriptorProto $file, \google\protobuf\DescriptorProto $descriptor, &$file_list)
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
            return join("\\", $args);
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
        foreach ($this->descriptor->getField() as $field) {
            /* @var $field \google\protobuf\FieldDescriptorProto */
            $printer->put("protected \$`name`;\n",
                "name",
                $field->getName()
            );
        }

        $printer->put("// @@protoc_insertion_point(properties_scope:`name`)\n\n",
            "name", $this->descriptor->getName()
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
            $printer->put("\"default\"  => \"`value`\",\n",
                "value",
                $field->getDefaultValue());

            if ($field->getType() == \google\protobuf\FieldDescriptorProto\Type::TYPE_MESSAGE) {
                $name = array_pop(explode(".", $field->getTypeName()));

                $descriptor = null;
                foreach ($this->file->getMessageType() as $m) {
                    if ($m->getName() == $name){
                        $descriptor = $m;
                        break;

                    }
                }
                if (!$descriptor) {
                    foreach ($this->descriptor->getNestedType() as $m) {
                        if ($m->getName() == $name){
                            $descriptor = $m;
                            break;

                        }
                    }

                    $printer->put("\"message\" => \"`message`\",\n",
                        "message",
                        str_replace(".", "\\\\", $descriptor->full_name)
                    );
                } else {
                    $printer->put("\"message\" => \"`message`\",\n",
                        "message",
                        str_replace(".", "\\\\", $descriptor->full_name)
                    );
                }
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
        return str_replace(".", "/", $name) . ".php";

    }

    public function printTraitsInsertionPoint(Printer $printer)
    {
        $printer->put("// @@protoc_insertion_point(traits:`name`)\n",
            "name", $this->descriptor->full_name);
        $printer->put("\n");
    }

    public function generate(Printer $printer)
    {
        foreach ($this->descriptor->getEnumType() as $enum) {
            $enum->full_name = $this->file->getPackage() . "." . $this->descriptor->getName() . "." . $enum->getName();
            $generator = new EnumGenerator($this->context, $this->file, $enum, $this->file_list);

            if ($this->file->getOptions()->GetExtension("php")->getMultipleFiles()) {
                $child_name = $generator->fileName();
                $this->file_list[] = $child_name;
                $child_printer = new Printer($this->context->open($child_name), "`");
                $generator->Generate($child_printer);
            }
        }

        foreach ($this->descriptor->getNestedType() as $message) {
            $message->full_name = $this->file->getPackage() . "." . $this->descriptor->getName() . "." . $message->getName();

            $generator = new MessageGenerator($this->context, $this->file, $message, $this->file_list);
            if ($this->file->getOptions()->GetExtension("php")->getMultipleFiles()) {
                $child_name = $generator->fileName();
                $this->file_list[] = $child_name;
                $child_printer = new Printer($this->context->open($child_name), "`");
                $generator->Generate($child_printer);
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

