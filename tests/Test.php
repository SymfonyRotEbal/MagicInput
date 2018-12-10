<?php

require_once('ExampleCommand.php');

class Test extends \PHPUnit\Framework\TestCase {




    public function testStart() {
        $this->assertTrue(true);
    }



    public function testWebGetContext() {
        $name = 'test-get-name';
        $description = 'test-get-description';

        $_GET['name'] = $name;
        $_GET['description'] = $description;

        $Command = new ExampleCommand();

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testWebPostContext() {
        $name = 'test-post-name';
        $description = 'test-post-description';

        $_POST['name'] = $name;
        $_POST['description'] = $description;

        $Command = new ExampleCommand();

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testWebMixedContext() {
        $name = 'test-get-name';
        $description = 'test-post-description';

        $_GET['name'] = $name;
        $_POST['descr'] = $description;

        $Command = new ExampleCommand();

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testWebMixedConflictContext() {
        $name = 'test-get-name';
        $description_get = 'test-get-description';
        $description_post = 'test-post-description';

        $_GET['name'] = $name;
        $_GET['description'] = $description_get;
        $_POST['descr'] = $description_post;

        $Command = new ExampleCommand();

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description_post, $Command->description);
    }



    public function testCliContext() {
        $name = 'test-get-name';
        $description = 'test-post-description';

        $_SERVER['argv'][1] = $name;
        $_SERVER['argv'][2] = $description;

        $Command = new ExampleCommand();

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testConsoleContext() {
        $name = 'test-get-name';
        $description = 'test-post-description';

        $Input = new \Symfony\Component\Console\Input\StringInput('');
        $Input->setArgument('name', $name);
        $Input->setOption('description', $description);

        $Command = new ExampleCommand($Input);

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testRequestContext() {
        $name = 'test-get-name';
        $description = 'test-get-description';

        $Request = new \Symfony\Component\HttpFoundation\Request();

        $Command = new ExampleCommand($Request);

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testJsonObjectContext() {
        $name = 'test-json-name';
        $description = 'test-json-description';

        $json = "{
  \"data\": {
    \"name\": \"{$name}\",
    \"options\": {
      \"description\": \"{$description}\"
    }
  }
}";

        $Command = new ExampleCommand($json);

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testJsonArrayContext() {
        $name = 'test-json-name';
        $description = 'test-json-description';

        $json = "[\"{$name}\", \"{$description}\"]";

        $Command = new ExampleCommand($json);

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }




    public function testArrayContext() {
        $name = 'test-array-name';
        $description = 'test-array-description';

        $array = [$name, $description];

        $Command = new ExampleCommand($array);

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }



    public function testAssocArrayContext() {
        $name = 'test-array-name';
        $description = 'test-array-description';

        $array = ['name' => $name, 'description' => $description];

        $Command = new ExampleCommand($array);

        $this->assertEquals($name, $Command->name);
        $this->assertEquals($description, $Command->description);
    }




}
