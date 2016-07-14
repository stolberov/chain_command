<?php

namespace FooBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use ChainCommandBundle\Command\ChainCommand;
use Symfony\Component\Console\Input\InputOption;
use Psr\Log\LoggerInterface;

class FooCommand extends ChainCommand
{
    protected function configure()
    {
        $this
            ->setName('foo:hello')
            ->setDescription('Say hello from Foo')
            ->setChains((array)parent::getChains())
            ->addOption(
                'isOnChain',
                false,
                InputOption::VALUE_OPTIONAL,
                "set true in case of execution in chain"
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = 'Hello from Foo';
        /** @var $logger LoggerInterface */
        $logger = $this->getContainer()->get('logger');
        $logger->info($message);
        $output->writeln($message);
    }

}
