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
use protocolbuffers\ExtensionPool;
use protocolbuffers\GeneratorContext;
use protocolbuffers\io\Printer;
use protocolbuffers\MessagePool;
use protocolbuffers\PragmaticInserter;
use protocolbuffers\SourceInfoDictionary;
use Symfony\Component\Yaml\Yaml;
use protocolbuffers\generator\php\Helper;

class MessageGenerator
{
    /** @var \google\protobuf\DescriptorProto  */
    protected $descriptor;

    /** @var \protocolbuffers\GeneratorContext  */
    protected $context;

    /** @var  array $file_list */
    protected $file_list;

    protected $enclose_namespace_ = false;

    public function __construct(GeneratorContext $context,
                                \google\protobuf\DescriptorProto $descriptor,
                                &$file_list)
    {
        $this->descriptor = $descriptor;
        $this->context = $context;
        $this->file_list = &$file_list;

        if ($descriptor->file()->getOptions()->getExtension("php")->getMultipleFiles()) {
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
        if (Helper::isPearStyle($this->descriptor)) {
            // nothing to do.
        } else {
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
            //  NOTE: We don't use `use` statement here. it's troublesome.

            $printer->put("// @@protoc_insertion_point(namespace:`name`)\n",
                "name", $this->descriptor->full_name);
            $printer->put("\n");
        }
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

                $printer->put("/**\n");
                if ($dict = SourceInfoDictionary::get($this->descriptor->file()->getName(), $this->descriptor->getName(), $field->getName())) {
                    /* @var $dict \google\protobuf\SourceCodeInfo\Location */
                    if ($dict->getLeadingComments()) {
                        $lines = preg_split("/\r?\n/", trim($dict->getLeadingComments()));
                        foreach ($lines as $line) {
                            $line = Helper::cleanupComment($line);
                            $printer->put(" * `comment`\n", "comment", $line);
                        }
                        $printer->put(" *\n");
                    }
                }

                $printer->put(" * @var `type` $`var`\n",
                    "type", $this->getTypeName($field),
                    "var", $field->getName());
                $printer->put(" * @tag `tag`\n", "tag", $field->getNumber());
                $printer->put(" * @label `required`\n",
                    "required", (FieldDescriptorProto\Label::isRequired($field) ? "required" : "optional"));
                $printer->put(" * @type `type`\n",
                    "type",
                    Helper::getFieldTypeName($field));

                if (FieldDescriptorProto\Label::isRepeated($field) &&
                        FieldDescriptorProto\Type::isMessage($field) ||
                        FieldDescriptorProto\Type::isEnum($field)) {
                    $printer->put(" * @see `see`\n",
                        "see", Helper::getClassName($field, true));
                }

                if ($dict = SourceInfoDictionary::get($this->descriptor->file()->getName(), $this->descriptor->getName(), $field->getName())) {
                    /* @var $dict \google\protobuf\SourceCodeInfo\Location */
                    if ($dict->getTrailingComments()) {
                        $printer->put(" *\n");
                        $lines = preg_split("/\r?\n/", trim($dict->getTrailingComments()));
                        foreach ($lines as $line) {
                            $printer->put(" * `comment`\n", "comment", Helper::cleanupComment($line));
                        }
                        $printer->put(" *\n");
                    }
                }

                $printer->put(" **/\n");
                $printer->put("protected \$`name`;\n",
                    "name",
                    $field->getName()
                );
                $printer->put("\n");
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
            Helper::getDescriptorBuilderClassName($this->descriptor));

        foreach ($this->descriptor->getField() as $offset => $field) {
            /* @var $field \google\protobuf\FieldDescriptorProto */
            $printer->put("\$desc->addField(`tag`, new `class_name`(array(\n",
                "tag",
                $field->getNumber(),
                "class_name",
                Helper::getFieldDescriptorClassName($this->descriptor));
            $printer->indent();
            $printer->put("\"type\"     => `type`,\n",
                "type",
                Helper::getFieldTypeName($field));
            $printer->put("\"name\"     => \"`name`\",\n",
                "name",
                $field->getName());
            $printer->put("\"required\" => `required`,\n",
                "required",
                (FieldDescriptorProto\Label::isRequired($field)) ? "true" : "false");
            $printer->put("\"optional\" => `optional`,\n",
                "optional",
                (FieldDescriptorProto\Label::isOptional($field)) ? "true" : "false");
            $printer->put("\"repeated\" => `repeated`,\n",
                "repeated",
                (FieldDescriptorProto\Label::isRepeated($field)) ? "true" : "false");

            $options = $field->getOptions();
            if ($options) {
                $printer->put("\"packable\" => `packable`,\n",
                    "packable",
                    (FieldDescriptorProto\Label::isPacked($field)) ? "true" : "false");
            } else {
                $printer->put("\"packable\" => `packable`,\n",
                    "packable",
                    "false");
            }
            $printer->put("\"default\"  => `value`,\n",
                "value",
                $this->defaultValueAsString($field));

            if (FieldDescriptorProto\Type::isMessage($field)) {
                $name = $field->getTypeName();

                $descriptor = MessagePool::get($name);
                $printer->put("\"message\" => '`message`',\n",
                    "message",
                    Helper::getClassName($descriptor, true)
                );
            }

            $printer->outdent();
            $printer->put(")));\n");
        }

        if ($php_options instanceof \google\protobuf\MessageOptions) {
            $php_message_options = $php_options->getExtension("php_option");

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

    public function getTypeName2(FieldDescriptorProto $field, $repeated = false)
    {
        $type = $this->getTypeName($field, $repeated);
        if ($type == "string") {
            // add backslashes for scalar type hints https://github.com/chobie/protoc-gen-php/issues/2
            $type = "\\" . $type;
        }

        return $type;
    }

    public function getTypeName(FieldDescriptorProto $field, $repeated = false)
    {
        if (!$repeated && FieldDescriptorProto\Label::isRepeated($field)) {
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
                return Helper::getClassName($field);
                break;
            case \ProtocolBuffers::TYPE_BYTES:
            case \ProtocolBuffers::TYPE_UINT32:
                return $default_type;
                break;
            case \ProtocolBuffers::TYPE_ENUM:
                return Helper::getClassName($field);
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
                return ($field->getDefaultValue() == "true") ? "true" : "false";
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

                if (!FieldDescriptorProto\Label::isRepeated($field)) {
                    $def = $field->getDefaultValue();
                    if (!empty($def)) {
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

            if (FieldDescriptorProto\Label::isRepeated($field)) {
                $printer->put(" * @method void append`variable`(`type2` \$value)\n",
                    "type", $this->getTypeName($field),
                    "variable", Helper::cameraize($field->getName()),
                    "type2", $this->getTypeName2($field, true)
                );
            } else {
                $printer->put(" * @method void set`variable`(`type2` \$value)\n",
                    "type", $this->getTypeName($field),
                    "variable", Helper::cameraize($field->getName()),
                    "type2", $this->getTypeName2($field)
                );
            }
        }

    }

    public function printExtension(Printer $printer, FieldDescriptorProto $field)
    {
        if (ExtensionPool::has($field->getExtendee(), $field->getNumber())) {
            // NOTE: already registered.
            return;
        }

        ExtensionPool::register($field->getExtendee(), $field->getNumber(), $field);
        $printer->put("\$registry->add('`message`', `extension`, new \\ProtocolBuffers\\FieldDescriptor(array(\n",
            "message", Helper::getExtendeeClassName($field),
            "extension", $field->getNumber()
        );
        $printer->indent();
        $printer->put("\"type\"     => `type`,\n",
            "type",
            Helper::getFieldTypeName($field));
        $printer->put("\"name\"     => \"`name`\",\n",
            "name",
            $field->getName());
        $printer->put("\"required\" => `required`,\n",
            "required",
            (FieldDescriptorProto\Label::isRequired($field)) ? "true" : "false");
        $printer->put("\"optional\" => `optional`,\n",
            "optional",
            (FieldDescriptorProto\Label::isOptional($field)) ? "true" : "false");
        $printer->put("\"repeated\" => `repeated`,\n",
            "repeated",
            (FieldDescriptorProto\Label::isRepeated($field)) ? "true" : "false");
        $printer->put("\"packable\" => `packable`,\n",
            "packable",
            (FieldDescriptorProto\Label::isPacked($field)) ? "true" : "false");
        $printer->put("\"default\"  => `value`,\n",
            "value",
            $this->defaultValueAsString($field));

        if (FieldDescriptorProto\Type::isMessage($field)) {
            $name = $field->getTypeName();

            $descriptor = MessagePool::get($name);
            $printer->put("\"message\" => '`message`',\n",
                "message",
                Helper::getClassName($descriptor, true)
            );
        }

        $printer->outdent();
        $printer->put(")));\n");
    }

    public function printExtensions()
    {
        if ($this->descriptor->file()->get("extension")) {
            $printer = new Printer($this->context->openForInsert("autoload.php", "extension_scope:registry"), "`");
            foreach ($this->descriptor->file()->get("extension") as $ext) {
                $this->printExtension($printer, $ext);
            }
        }
    }

    public function generate(Printer $printer)
    {
        foreach ($this->descriptor->getEnumType() as $enum) {
            $generator = new EnumGenerator($this->context, $enum, $this->file_list);

            if ($this->descriptor->file()->getOptions()->GetExtension("php")->getMultipleFiles()) {
                $child_name = $generator->fileName();
                $this->file_list[] = $child_name;
                $child_printer = new Printer($this->context->open($child_name), "`");
                $generator->generate($child_printer);
            }
        }

        foreach ($this->descriptor->getNestedType() as $message) {
            $generator = new MessageGenerator($this->context, $message, $this->file_list);

            if ($this->descriptor->file()->getOptions()->GetExtension("php")->getMultipleFiles()) {
                $child_name = $generator->fileName();
                $this->file_list[] = $child_name;
                $child_printer = new Printer($this->context->open($child_name), "`");
                $generator->generate($child_printer);
            }
        }

        if ($this->descriptor->file()->getOptions()->getExtension("php")->getMultipleFiles()) {
            $printer->put("<?php\n");
        }

        $this->printUseNameSpaceIfNeeded($printer);

        $printer->put(
            "/**\n" .
            " * Generated by the protocol buffer compiler.  DO NOT EDIT!\n" .
            " * source: `filename`\n" .
            " *\n",
            "filename",
            $this->descriptor->file()->getName()
        );

        if ($dict = SourceInfoDictionary::get($this->descriptor->file()->getName(), $this->descriptor->getName(), "message")) {
            /* @var $dict \google\protobuf\SourceCodeInfo\Location */
            if ($dict->getLeadingComments()) {
                $lines = preg_split("/\r?\n/", trim($dict->getLeadingComments()));
                foreach ($lines as $line) {
                    if ($line[0] == " ") {
                        $line = substr($line, 1);
                    }
                    $printer->put(" * `comment`\n", "comment", $line);
                }
                $printer->put(" *\n");
            }
        }

        $this->printMagicMethod($printer);
        $printer->put(" */\n");

        $printer->put("class `name` extends `base`\n{\n",
            "name",
             Helper::getClassName($this->descriptor, false),
            "base",
            Helper::getBaseClassName($this->descriptor)
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

        $this->printExtensions();

        PragmaticInserter::execute($this->descriptor, $this->context);
    }
}

