<?php
spl_autoload_register(function($name){
    static $classmap;
    if (!$classmap) {
        $classmap = array(
            'google\protobuf\FileDescriptorSet' => 'com/google/protobuf/FileDescriptorSet.php',
            'google\protobuf\FileDescriptorProto' => 'com/google/protobuf/FileDescriptorProto.php',
            'google\protobuf\DescriptorProto' => 'com/google/protobuf/DescriptorProto.php',
            'google\protobuf\DescriptorProto\ExtensionRange' => 'google/protobuf/DescriptorProto/ExtensionRange.php',
            'google\protobuf\FieldDescriptorProto' => 'com/google/protobuf/FieldDescriptorProto.php',
            'google\protobuf\FieldDescriptorProto\Type' => 'google/protobuf/FieldDescriptorProto/Type.php',
            'google\protobuf\FieldDescriptorProto\Label' => 'google/protobuf/FieldDescriptorProto/Label.php',
            'google\protobuf\EnumDescriptorProto' => 'com/google/protobuf/EnumDescriptorProto.php',
            'google\protobuf\EnumValueDescriptorProto' => 'com/google/protobuf/EnumValueDescriptorProto.php',
            'google\protobuf\ServiceDescriptorProto' => 'com/google/protobuf/ServiceDescriptorProto.php',
            'google\protobuf\MethodDescriptorProto' => 'com/google/protobuf/MethodDescriptorProto.php',
            'google\protobuf\FileOptions' => 'com/google/protobuf/FileOptions.php',
            'google\protobuf\FileOptions\OptimizeMode' => 'google/protobuf/FileOptions/OptimizeMode.php',
            'google\protobuf\MessageOptions' => 'com/google/protobuf/MessageOptions.php',
            'google\protobuf\FieldOptions' => 'com/google/protobuf/FieldOptions.php',
            'google\protobuf\FieldOptions\CType' => 'google/protobuf/FieldOptions/CType.php',
            'google\protobuf\EnumOptions' => 'com/google/protobuf/EnumOptions.php',
            'google\protobuf\EnumValueOptions' => 'com/google/protobuf/EnumValueOptions.php',
            'google\protobuf\ServiceOptions' => 'com/google/protobuf/ServiceOptions.php',
            'google\protobuf\MethodOptions' => 'com/google/protobuf/MethodOptions.php',
            'google\protobuf\UninterpretedOption' => 'com/google/protobuf/UninterpretedOption.php',
            'google\protobuf\UninterpretedOption\NamePart' => 'google/protobuf/UninterpretedOption/NamePart.php',
            'google\protobuf\SourceCodeInfo' => 'com/google/protobuf/SourceCodeInfo.php',
            'google\protobuf\SourceCodeInfo\Location' => 'google/protobuf/SourceCodeInfo/Location.php',
            'google\protobuf\compiler\CodeGeneratorRequest' => 'com/google/protobuf/compiler/CodeGeneratorRequest.php',
            'google\protobuf\compiler\CodeGeneratorResponse' => 'com/google/protobuf/compiler/CodeGeneratorResponse.php',
            'google\protobuf\compiler\CodeGeneratorResponse\File' => 'google/protobuf/compiler/CodeGeneratorResponse/File.php',
            'PHPFileOptions' => 'PHPFileOptions.php',
            'PHPMessageOptions' => 'PHPMessageOptions.php',
        );
    }
    if (isset($classmap[$name])) {
        require __DIR__ . DIRECTORY_SEPARATOR . $classmap[$name];
    }
});
