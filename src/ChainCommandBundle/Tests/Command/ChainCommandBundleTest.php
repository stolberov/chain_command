<?php
use ChainCommandBundle\Command\ChainCommand;
use FooBundle\Command\FooCommand;
use BarBundle\Command\BarCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChainCommandBundle extends KernelTestCase
{
    public $application;

    public function setUp()
    {

        $kernel = $this->createKernel();
        $kernel->boot();
        $this->application = new Application($kernel);
        $this->application->add(new ChainCommand());
        $this->application->add(new FooCommand());
        $this->application->add(new BarCommand());
    }

    public function testExecute()
    {

        $command = $this->application->find('foo:hello');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('isOnChain' => true));
        echo $commandTester->getDisplay();
    }
}