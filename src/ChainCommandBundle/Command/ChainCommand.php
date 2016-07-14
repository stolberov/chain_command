<?php

namespace ChainCommandBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChainCommand extends ContainerAwareCommand
{
    /**
     * @var array contains chains configuration
     */
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
            ->addOption(
                'isOnChain',
                false,
                InputOption::VALUE_OPTIONAL,
                "set true in case of execution in chain"
            );
    }

    /**
     * Redefined and modified functionality of Command's general method run
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int The command exit code
     *
     * @throws \Exception
     *
     */
    public function run(InputInterface $input, OutputInterface $output)
    {

        /** @var $logger LoggerInterface */
        $logger = $this->getContainer()->get('logger');

        $commandName = $this->getName();
        $isOnChain = $input->hasOption('isOnChain') ? $input->getOption('isOnChain') : false;
        /** in case of execution of chain member, execution is allowed */
        if ($isOnChain)
            return parent::run($input, $output);

        $chains = $this->getChains();
        if (empty($chains[$commandName])) {
            /** check and  disallow access if current command is part of chain and it's not a chain holder*/
            $holderName = $this->_searchChainHolder($commandName);
            if ($holderName)
                throw new \Exception(sprintf(
                    "Error: %s command is a member of %s command chain and cannot be executed on its own.",
                    $commandName,
                    $holderName
                ));
            else
                return parent::run($input, $output);
        } else {
            /** start of execution chain */

            /** logStart: this part of code needed only for correct detailed logging  */
            $logger->info(sprintf("%s is a master command of a command chain that has registered member commands", $commandName));
            foreach ($chains[$commandName] as $memberCommandName) {
                $logger->info(sprintf("%s registered as a member of %s command chain", $memberCommandName, $commandName));
            }
            /** logEnd; */

            $logger->info(sprintf("Executing %s command itself first:", $commandName));
            $status = parent::run($input, $output);
            $logger->info(sprintf("Executing %s chain members:", $commandName));

            /** execution of chain's members */
            /** @var string $memberCommandName member's commandName */

            foreach ($chains[$commandName] as $memberCommandName) {
                $command = $this->getApplication()->find($memberCommandName);
                $input->setOption('isOnChain', true);
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

    /**
     * Returns the command chain holder if exists
     * @param string $commandName name of executed command
     * @return string command name chain holder
     */
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
