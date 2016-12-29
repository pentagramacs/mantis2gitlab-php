<?php

namespace M2G\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use M2G\Contracts\CommandAbstract;
use M2G\Configuration;
use M2G\Gitlab;

class TestGitlabCommand extends CommandAbstract
{
	protected function configure()
	{
		$this->setName('test:gitlab')
			 ->setDescription('Test the communication with Gitlab.')
			 ->setHelp('This commands allow you to test the communication with gitlab.');
		$this->addGitlabOptions();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		// clear options 
		$override = $this->sanitizeOptions($input->getOptions());
		$override = $this->splitIndexes($override);
		$configuration = new Configuration('./config', $override);
		$gitlab = new Gitlab($configuration->gitlab());

		$io->title('Testing connection to Gitlab');
		$io->section('Endpoint:');

		try {		
			$project = $gitlab->project($configuration->gitlab('project'));
			$io->success($configuration->gitlab('endpoint'));
		} catch(\Exception $e) {
			$io->error($configuration->gitlab('endpoint'));
		}

		$io->section('Project informations:');

		try {
			$io->success(sprintf("Project: '%s'", $project->raw('name_with_namespace')));
		} catch(\Exception $e) {
			$io->error('Failed to get project data.');
		}

		try {
			$gitlabIssues = $project->milestones();
			$io->success(sprintf("Milestones found: '%s'", count($gitlabIssues)));
		} catch(\Exception $e) {
			$io->error('Failed to get project milestones.');
		}

		try {
			$gitlabIssues = $project->issues();
			$io->success(sprintf("Issues found: '%s'", count($gitlabIssues)));
		} catch(\Exception $e) {
			$io->error('Failed to get project issues.');
		}
	}
}