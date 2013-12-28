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

use protocolbuffers\io\Printer;

class MessageGenerator
{
    protected $file;

    protected $descriptor;

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

    public function __construct(\google\protobuf\FileDescriptorProto $file, \google\protobuf\DescriptorProto $descriptor)
    {
        $this->file = $file;
        $this->descriptor = $descriptor;
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
        $printer->put(" * @return ProtocolBuffersDescriptor\n");
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
                    //error_log(var_export($name, true));
                } else {
                    $printer->put("\"message\" => \"`message`\",\n",
                        "message",
                        $descriptor->getName()
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
            "name", $this->descriptor->getName());

        $printer->put("\$descriptor = \$desc->build();\n");
        $printer->outdent();
        $printer->put("}\n");

        $printer->put("return \$descriptor;\n");
        $printer->outdent();
        $printer->put("}\n\n");
    }

    public function generate(Printer $printer)
    {
        $printer->put("<?php\n");
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
        $this->printProperties($printer);
        $this->printGetDescriptor($printer);

        $printer->outdent();
        $printer->put("}\n");
    }
}

