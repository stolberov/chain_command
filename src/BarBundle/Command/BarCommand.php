<?php

namespace BarBundle\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ChainCommandBundle\Command\ChainCommand;
use Psr\Log\LoggerInterface;

class BarCommand extends ChainCommand
{
    protected function configure()
    {
        $this
            ->setName('bar:hi')
            ->setDescription('Say hello from Bar')
            ->setChains((array)parent::getChains())
            ->addArgument(
                'isOnChain',
                InputArgument::OPTIONAL
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $message = 'Hi from Bar';
        /** @var $logger LoggerInterface */
        $logger = $this->getContainer()->get('logger');
        $logger->info($message);
        $output->writeln($message);
    }
}
