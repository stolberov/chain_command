<?php

namespace ChainCommandBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ChainCommand extends ContainerAwareCommand
{
    private $chains = array(
        'foo:hello' => array(
            'bar:hi'
        )
    );


    protected function configure()
    {
        $this
            ->setName('chain:description')
            ->setDescription('Here is a holder of commands')
            ->addArgument(
                'isOnChain',
                InputArgument::OPTIONAL
            )
        ;
    }

    public function run(InputInterface $input, OutputInterface $output)
    {

        /** @var $logger LoggerInterface */
        $logger = $this->getContainer()->get('logger');

        $commandName = $this->getName();
        $isOnChain = $input->hasArgument('isOnChain') ? $input->getArgument('isOnChain') : false;
        if ($isOnChain)
            return parent::run($input, $output);

        $chains = $this->getChains();
        if (empty($chains[$commandName])) {
            $holderName = $this->_searchChainHolder($commandName);
            if ($holderName)
                throw new Exception(sprintf(
                    "Error: %s command is a member of %s command chain and cannot be executed on its own.",
                    $commandName,
                    $holderName
                ));
            else
                return parent::run($input, $output);
        }
        else{
            $logger->info(sprintf("%s is a master command of a command chain that has registered member commands", $commandName));
            foreach ($chains[$commandName] as $memberCommandName) {
                $logger->info(sprintf("%s registered as a member of %s command chain", $memberCommandName, $commandName));
            }

            $logger->info(sprintf("Executing %s command itself first:", $commandName));
            $status = parent::run($input, $output);
            $logger->info(sprintf("Executing %s chain members:", $commandName));
            foreach ($chains[$commandName] as $memberCommandName) {
                $command = $this->getApplication()->find($memberCommandName);
                $input->setArgument('isOnChain', true);
                $command->run($input, $output);
            }
            $logger->info(sprintf("Execution of %s chain completed.", $commandName));
        }
        return $status;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getDescription());
    }

    private function _searchChainHolder($commandName)
    {
        $chains = $this->getChains();
        foreach ($chains as $holderName => $chainMembers) {
            if (in_array($commandName, $chainMembers))
                return $holderName;
        }
    }


    /**
     * Returns the command chains.
     *
     * @return array The command chains
     */
    public function getChains()
    {
        return $this->chains;
    }

    /**
     * Sets the chains of the command.
     *
     * This method can set both the namespace and the name if
     * you separate them by a colon (:)
     *
     *     $command->setChains(array(
     *              'foo:hello'=>array(
     *                      'bar:hi'
     *                  )
     * ));
     *
     * @param array $chains The command chains
     *
     * @return Command The current instance
     *
     */
    public function setChains(array $chains)
    {

        $this->chains = $chains;

        return $this;
    }


}
