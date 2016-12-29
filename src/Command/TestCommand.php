<?php

namespace M2G\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use M2G\Contracts\CommandAbstract;

class TestCommand extends CommandAbstract
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
		$argumentsGitlab = $this->getOptionsStartingWith('gitlab', $input->getOptions());
		$gitlabInput = new ArrayInput($argumentsGitlab);
		$command = $this->getApplication()->find('test:gitlab');
		$command->run($gitlabInput, $output);

		$argumentsMantis = $this->getOptionsStartingWith('mantis', $input->getOptions());
		$mantisInput = new ArrayInput($argumentsMantis);
		$command = $this->getApplication()->find('test:mantis');
		$command->run($mantisInput, $output);
	}

	protected function getOptionsStartingWith($startWith, $options)
	{
		$f = array_filter(array_keys($options), function($key) use ($startWith) {
			return substr($key, 0, strlen($startWith)) == $startWith;
		});
		$options = array_intersect_key($options, array_flip($f));
		foreach($options as $k => $v) { 
			if (!is_null($v)) {
				$options['--' . $k] = $v;
			}
			unset($options[$k]); 
		}
		return $options;
	}
}
