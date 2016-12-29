<?php 

namespace M2G\Traits;

use Symfony\Component\Console\Input\InputOption;

trait DefaultOptions
{

	protected function addGitlabOptions()
	{
		$this->addOption('gitlab.endpoint', 	'G', InputOption::VALUE_REQUIRED, 'What\'s your Gitlab Endpoint?')
			 ->addOption('gitlab.access-token', 'A', InputOption::VALUE_REQUIRED, 'What\'s your Gitlab Access Token?')
			 ->addOption('gitlab.project', 		'P', InputOption::VALUE_REQUIRED, 'Which project at Gitlab want to be migrated at?');
	}

	protected function addMantisOptions() {
		$this->addOption('mantis.wsdl',		'm', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Endpoint WSDL?')
			 ->addOption('mantis.username',	'u', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Username?')
			 ->addOption('mantis.password',	's', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Password?')
			 ->addOption('mantis.project',	'p', InputOption::VALUE_REQUIRED, 'What\'s your Mantis Project (can be the name or the ID)?');
	}

}