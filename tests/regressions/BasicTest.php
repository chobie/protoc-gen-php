<?php

class BasicTest extends PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_should_hoge()
    {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . "fixtures" . DIRECTORY_SEPARATOR . "simple.bin";
        $data = file_get_contents($path);

        $compiler = new \protocolbuffers\Compiler();
        $response = $compiler->compile($data);
        /** @var \google\protobuf\compiler\CodeGeneratorResponse $response */

        $file = $response->getFile(2);
        $this->assertTrue((bool)preg_match("/Person\.php/", $file->getName()));
        $file->getContent();

        $parser = new PhpParser\Parser(new PhpParser\Lexer);
        $stmts = $parser->parse((string)$file->getContent());

        $class = $stmts[0];
        /** @var PhpParser\Node\Stmt\Class_ $class */
        $this->assertInstanceof('PhpParser\Node\Stmt\Class_', $class);
        $this->assertEquals("Person", $class->name);

        $properties = array();
        foreach ($class->stmts as $stmt) {
            if ($stmt instanceof PhpParser\Node\Stmt\Property) {
                $properties[] = $stmt;
            }
        }

        foreach ($properties as $prop) {
            /** @var PhpParser\Node\Stmt\Property $prop */
            $this->assertEquals("name", $prop->props[0]->name);
            $this->assertTrue($prop->isProtected());
        }
    }
}