<?php
use ChainCommandBundle\Command\ChainCommand;
use FooBundle\Command\FooCommand;
use BarBundle\Command\BarCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ChainCommandBundleTest extends KernelTestCase
{
    /** @var  $application Application */
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

    public function testForChainingStartedFromFoo()
    {

        $command = $this->application->find('foo:hello');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
        $this->assertRegExp('/Hello from Foo\sHi from Bar/', $commandTester->getDisplay());
    }

    public function testForChainingStartedFromBar()
    {

        $command = $this->application->find('bar:hi');
        $commandTester = new CommandTester($command);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("bar:hi command is a member of foo:hello command chain and cannot be executed on its own.");
        $commandTester->execute(array());
    }

    public function testShouldSuccessfullyExecuteBarOnChainsDisabled()
    {
        $command = $this->application->find('bar:hi');
        $command->setChains(array());
        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
        $this->assertRegExp('/Hi from Bar/', $commandTester->getDisplay());
    }

    public function testShouldExecuteOnlyFooOnChainsDisabled()
    {
        $command = $this->application->find('foo:hello');
        $command->setChains(array());
        $commandTester = new CommandTester($command);
        $commandTester->execute(array());
        $this->assertRegExp('/Hello from Foo/', $commandTester->getDisplay());
    }
}