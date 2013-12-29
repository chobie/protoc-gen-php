<?php
spl_autoload_register(function($name){
  static $classmap;
  if (!$classmap) {
    $classmap = array(
      'google\protobuf\FileDescriptorSet' => '/google/protobuf/FileDescriptorSet.php',
      'google\protobuf\FileDescriptorProto' => '/google/protobuf/FileDescriptorProto.php',
      'google\protobuf\DescriptorProto' => '/google/protobuf/DescriptorProto.php',
      'google\protobuf\DescriptorProto\ExtensionRange' => '/google/protobuf/DescriptorProto/ExtensionRange.php',
      'google\protobuf\FieldDescriptorProto' => '/google/protobuf/FieldDescriptorProto.php',
      'google\protobuf\FieldDescriptorProto\Type' => '/google/protobuf/FieldDescriptorProto/Type.php',
      'google\protobuf\FieldDescriptorProto\Label' => '/google/protobuf/FieldDescriptorProto/Label.php',
      'google\protobuf\EnumDescriptorProto' => '/google/protobuf/EnumDescriptorProto.php',
      'google\protobuf\EnumValueDescriptorProto' => '/google/protobuf/EnumValueDescriptorProto.php',
      'google\protobuf\ServiceDescriptorProto' => '/google/protobuf/ServiceDescriptorProto.php',
      'google\protobuf\MethodDescriptorProto' => '/google/protobuf/MethodDescriptorProto.php',
      'google\protobuf\FileOptions' => '/google/protobuf/FileOptions.php',
      'google\protobuf\FileOptions\OptimizeMode' => '/google/protobuf/FileOptions/OptimizeMode.php',
      'google\protobuf\MessageOptions' => '/google/protobuf/MessageOptions.php',
      'google\protobuf\FieldOptions' => '/google/protobuf/FieldOptions.php',
      'google\protobuf\FieldOptions\CType' => '/google/protobuf/FieldOptions/CType.php',
      'google\protobuf\EnumOptions' => '/google/protobuf/EnumOptions.php',
      'google\protobuf\EnumValueOptions' => '/google/protobuf/EnumValueOptions.php',
      'google\protobuf\ServiceOptions' => '/google/protobuf/ServiceOptions.php',
      'google\protobuf\MethodOptions' => '/google/protobuf/MethodOptions.php',
      'google\protobuf\UninterpretedOption' => '/google/protobuf/UninterpretedOption.php',
      'google\protobuf\UninterpretedOption\NamePart' => '/google/protobuf/UninterpretedOption/NamePart.php',
      'google\protobuf\SourceCodeInfo' => '/google/protobuf/SourceCodeInfo.php',
      'google\protobuf\SourceCodeInfo\Location' => '/google/protobuf/SourceCodeInfo/Location.php',
      'google\protobuf\compiler\CodeGeneratorRequest' => '/google/protobuf/compiler/CodeGeneratorRequest.php',
      'google\protobuf\compiler\CodeGeneratorResponse' => '/google/protobuf/compiler/CodeGeneratorResponse.php',
      'google\protobuf\compiler\CodeGeneratorResponse\File' => '/google/protobuf/compiler/CodeGeneratorResponse/File.php',
      'PHPFileOptions' => '/PHPFileOptions.php',
      'PHPMessageOptions' => '/PHPMessageOptions.php',
      // @@protoc_insertion_point(autoloader_scope:classmap)
    );
  }
  if (isset($classmap[$name])) {
    require __DIR__ . DIRECTORY_SEPARATOR . $classmap[$name];
  }
});

call_user_func(function(){
    $registry = \ProtocolBuffers\ExtensionRegistry::getInstance();
    $registry->add('google\protobuf\FileOptions', 1004, new \ProtocolBuffers\FieldDescriptor(array(
        "type"     => \ProtocolBuffers::TYPE_MESSAGE,
        "name"     => "php",
        "required" => false,
        "optional" => true,
        "repeated" => false,
        "packable" => false,
        "default"  => null,
        "message"  => "\\PHPFileOptions",
    )));
    $registry->add('google\protobuf\MessageOptions', 1004, new \ProtocolBuffers\FieldDescriptor(array(
        "type"     => \ProtocolBuffers::TYPE_MESSAGE,
        "name"     => "php_option",
        "required" => false,
        "optional" => true,
        "repeated" => false,
        "packable" => false,
        "default"  => null,
        "message"  => "\\PHPMessageOptions",
    )));
    // @@protoc_insertion_point(extension_scope:registry)
});
