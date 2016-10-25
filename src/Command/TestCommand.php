<?php

namespace M2G\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class TestCommand extends TestGitlabCommand
{
	protected function configure()
	{
		$this->setName('test')
			 ->setDescription('Test the communication with the APIs.')
			 ->setHelp('This commands allow you to test the communication with gitlab and mantis.');

		$this->addMantisOptions();
		$this->addGitlabOptions();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		// ...
	}
}