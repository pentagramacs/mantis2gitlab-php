<?php

namespace M2G\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use M2G\Contracts\CommandAbstract;
use M2G\Configuration;
use M2G\Mantis;

class TestMantisCommand extends CommandAbstract
{
	protected function configure()
	{
		$this->setName('test:mantis')
			 ->setDescription('Test the communication with mantis.')
			 ->setHelp('This commands allow you to test the communication with mantis.');
		$this->addMantisOptions();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		// clear options 
		$override = $this->sanitizeOptions($input->getOptions());
		$override = $this->splitIndexes($override);
		$configuration = new Configuration('./config', $override);
		$mantis = new Mantis($configuration->mantis());

		$io->title('Testing connection to Mantis');
		$io->section('Endpoint:');

		try {		
			$project = $mantis->project($configuration->mantis('project'));
			$io->success($configuration->mantis('wsdl'));
		} catch(\Exception $e) {
			$io->error($configuration->mantis('wsdl'));
		}

		$io->section('Project informations:');

		try {
			$io->success(sprintf("Project: '%s'", $configuration->mantis('project') . ' (' . $project->id() . ')'));
		} catch(\Exception $e) {
			$io->error('Failed to get project data.');
		}

		try {
			$mantisIssues = $project->versions();
			$io->success(sprintf("Versions found: '%s'", count($mantisIssues)));
		} catch(\Exception $e) {
			$io->error('Failed to get project versions.');
		}

		try {
			$mantisIssues = $project->issues();
			$io->success(sprintf("Issues found: '%s'", count($mantisIssues)));
		} catch(\Exception $e) {
			$io->error('Failed to get project issues.');
		}
	}
}