<?php

namespace M2G\Command;

use M2G\Contracts\CommandAbstract;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class TestMantisCommand extends CommandAbstract
{
	protected function configure()
	{
		$this->setName('test:mantis')
			 ->setDescription('Test the communication with mantis.')
			 ->setHelp('This commands allow you to test the communication with mantis.');
		$this->addMantisOptions();
	}

	protected function addMantisOptions() {
		$this->addOption('mantis-endpoint',		'm', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Endpoint WSDL?')
			 ->addOption('mantis-username',		'u', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Username?')
			 ->addOption('mantis-password',		's', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Password?')
			 ->addOption('mantis-project',		'p', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Project (can be the name or the ID)?');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ...
	}
}