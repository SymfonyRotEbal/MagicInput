<?php

class ExampleCommand {

    /**
     * @ManyInput\HttpGet('name')
     * @ManyInput\HttpPost('name')
     * @ManyInput\CliArg(1)
     * @ManyInput\ConsoleArgument('name')
     * @ManyInput\Array('name')
     * @ManyInput\Array(0)
     * @ManyInput\Json('$.data.name')
     * @ManyInput\Json('$.[0:1]')
     **/
    public $name;

    /**
     * @ManyInput\HttpGet('description')
     * @ManyInput\HttpPost('descr')
     * @ManyInput\CliArg(2)
     * @ManyInput\ConsoleOption('description')
     * @ManyInput\Array('description')
     * @ManyInput\Array(1)
     * @ManyInput\Json('$.data.options.description')
     * @ManyInput\Json('$.[1:2]')
     **/
    public $description;

}