<?php

namespace M2G\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\ProgressBar;

use M2G\Contracts\CommandAbstract;
use M2G\Configuration;
use M2G\Gitlab;
use M2G\Mantis;
use M2G\Gitlab\Upload;

class MigrateCommand extends CommandAbstract
{
	protected $io = null;
	protected $configuration = null;
	protected $gitlab = null;
	protected $mantis = null;
	protected $dryRun = false;
	protected $skipVerification = false;

	protected $milestones = array();

	protected $handlerPath = array();
	protected $handlers = array();

	protected function configure()
	{
		$this->setName('migrate')
			 ->setDescription('Migrate all the data from a specific mantis project to the mapped gitlab project.')
			 ->setHelp('@TODO');

		$this->addMantisOptions();
		$this->addGitlabOptions();

		$this->addOption('dry-run', 	'N', InputOption::VALUE_NONE, 'Fake run and output what would be done');
		$this->addOption('skip-verification', 	'S', InputOption::VALUE_NONE, 'Skip the initial loading for verification purposes');
	}

	protected function io($input = null, $output = null) {
		if (empty($this->io))
		{
			if (empty($input) || empty($output))
			{
				throw new \Exception('First call to io() must have a InputInterface and OutputInterface classes.');
			}

			$this->io = new SymfonyStyle($input, $output);
		}

		return $this->io;
	}

	protected function addHandlerPath($path)
	{
		$this->handlerPath[] = $path;
		return $this;
	}

	protected function handler($handler, $data)
	{
		if (empty($this->handlers[strtolower($handler)])) {
			foreach($this->handlerPath as $classPath)
			{
				$fullClassName = sprintf("%s\%s", $classPath, $handler);
				if (class_exists($fullClassName)) {
					$this->handlers[strtolower($handler)] = new $fullClassName($this->config());
				}
			}
		}

		return !empty($this->handlers[strtolower($handler)]) ? $this->handlers[strtolower($handler)]->handle($data) : null;
	}

	protected function init($input, $output)
	{
		ProgressBar::setFormatDefinition(
			'm2g-mantis',
			"<bg=green;fg=black>Loading: %message:-29s%</>\n%current%/%max% [%bar%] %percent%%\n"
		);
		ProgressBar::setFormatDefinition(
			'm2g-gitlab',
			"<bg=yellow;fg=black>Loading: %message:-29s%</>\n%current%/%max% [%bar%] %percent%%\n"
		);
		ProgressBar::setFormatDefinition(
			'm2g',
			"%message:-38s%\n%current%/%max% [%bar%] %percent%%\n"
		);

		$this->output = $output;

		$this->io($input, $output);
		$this->config($input);

		$this->dryRun = $input->getOption('dry-run');
		$this->skipVerification = $input->getOption('skip-verification');

		$this->addHandlerPath('M2G\Handler');
		// $this->handler('Label');
		// $this->handler('Title');
		// $this->handler('Description');
	}

	protected function preload()
	{
		$progress = new ProgressBar($this->output, 6);
		$progress->setFormat('m2g-mantis');
		$progress->setBarCharacter('<bg=green;fg=green> </>');
		$progress->setProgressCharacter('<bg=green;fg=white>|</>');
		$progress->setEmptyBarCharacter(' ');
		$progress->start();

		// pre-load mantis
		$progress->setMessage('mantis project...');
		$progress->advance();
		$this->mantisProject();
		$progress->setMessage('mantis issues...');
		$this->mantisIssues();
		$progress->setMessage('mantis versions...');
		$progress->advance();
		$this->mantisVersions();

		// pre-load gitlab		
		$progress->setFormat('m2g-gitlab');
		$progress->setMessage('gitlab project...');
		$progress->advance();
		$this->project();
		$progress->setMessage('gitlab issues...');
		$progress->advance();
		$this->issues();
		$progress->setMessage('gitlab milestones...');
		$progress->advance();
		$this->milestones();
		$progress->setMessage('Loaded.');
		$progress->finish();
		$progress->clear();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->init($input, $output);

		$this->io()->title('Migrate Information');

		if (!$this->skipVerification) {
			$this->preload();

			$this->io()->table(
				array('', 'Mantis', '=>', 'GitLab'),
				array(
					array('Project Name', $this->mantisProject()->raw('name'), '=>', $this->project()->raw('name_with_namespace')),
					array('Issues Found', $this->mantisIssues()->count(), '=>', $this->issues()->count()),
					array('Versions / Milestones', $this->mantisVersions()->count(), '=>', $this->milestones()->count()),
				)
			);

			// @TODO implement some information even if we skip verification, otherwise user can do mass
			// to skip the confirmation user can use -n or --no-interaction
		}

		if ($this->io()->confirm('Should we continue?', true)) {
			$this->processMilestones();
			$this->processIssues();

			if ($this->dryRun) {
				$this->io()->warning('This was ran in dry-run mode.');
			}
		} else {
			$this->io->text('Ok! No hard feelings about this..');
			$this->io->newLine(3);
		}
	}

	protected function processIssues()
	{
		$this->io()->newLine(1);

		$progress = new ProgressBar($this->output, 3);
		$progress->setFormat('m2g');
		$progress->setMessage('Loading milestones...');
		$progress->start();
		$progress->advance();
		$milestones = $this->milestones(true);

		$progress->setMessage('Loading GitLab users...');
		$progress->advance();
		$users = $this->gitlab()->user()->all();

		$progress->setMessage('Loading GitLab issues...');
		$progress->advance();
		$this->issues(); // should be fast if without --skip-verification
		$progress->clear();
		$progress->finish();

		$this->io()->newLine(1);

		$progress = new ProgressBar($this->output, $this->mantisIssues()->count());
		$progress->setFormat('m2g');
		$progress->setMessage('Importing issues...');
		foreach($this->mantisIssues() as $issue)
		{
			$issueAsArray = $issue->toArray();

			$labels = $this->handler('Label', $issue);
			$title = $this->handler('Title', $issue);
			$description = $this->handler('Description', $issue);

			$existingIssue = $this->issues()->filter(function($item) use ($title) {
				return $item->raw('title') == $title;
			});

			if ($existingIssue) {
				if ($this->output->isVerbose()) {
					$progress->clear();
					$this->io()->writeln(sprintf("Already mapped issue found '<info>%s</info>'\n", $title));
					$progress->display();
				}
				$progress->advance();
				continue;
			}

			$handler = $issue->handler() && property_exists($issue->handler(), 'name') ? $issue->handler()->name : null;
			$assignee_id = $handler && isset($users[$handler]) ? $users[$handler]->raw('id') : null;

			$milestone_id = (
				$milestones && 
				$issue->target_version() &&
				!empty($milestones[$issue->target_version()])
			)
			? $milestones[$issue->target_version()]->raw('id')
			: null;

			$description .= $this->__handleAttachments($description, $issue, $progress);

			$newIssueData = array(
				'title' => $title,
				'description' => $description,
				'confidential' => $issue->visibility() !== 'public' && $issue->visibility() !== null,
				'assignee_id' => $assignee_id,
				'milestone_id' => $milestone_id,
				'labels' => $labels,
				'created_at' => $issue->dateSubmitted()->format('Y-m-d'),
				'due_date' => null
			);

			$newIssue = false;
			if (!$this->dryRun) {
				$newIssue = $this->project()->newIssue($newIssueData);
			}

			if ($this->output->isVerbose()) {
				$progress->clear();
				$this->io()->writeln(sprintf("New issue imported '<info>%s</info>'\n", $title));
				$progress->display();
			}

			$this->__handleNotes($issue, $newIssue, $progress);

			if ($issueAsArray['status'] == 'closed') {
				if (!$this->dryRun) {
					$newIssue->close();
				}

				if ($this->output->isVerbose()) {
					$progress->clear();
					$this->io()->writeln(sprintf("Closed issue '<info>%s</info>'\n", $title));
					$progress->display();
				}
			}

			$progress->advance();
		}

		$progress->finish();
		$this->io()->newLine(2);
	}

	protected function __handleNotes($issue, $newIssue, $progress)
	{
		$mantisNotes = $issue->notes();
		$notesProgress = new ProgressBar($this->output, count($mantisNotes));
		$notesProgress->setFormat('m2g');
		$notesProgress->setMessage('Importing notes for #' .  $issue->id() . ' issue...');
		$notesProgress->start();
		foreach($mantisNotes as $i => $mNote) {
			$reporter = $mNote->reporter() && isset($mNote->reporter()->name) ? $mNote->reporter()->name : $mNote->reporter()->id;

			$body = '### Originalmente escrito por: *' . $reporter . '*' . PHP_EOL . PHP_EOL;
			$body .= '-----';
			$body .= $mNote->text();

			$rawNote = array(
				'body' => $body,
				'created_at' => $mNote->date_submitted()
			);

			if (!$this->dryRun) {
				$newIssue->newNote($rawNote);
			}

			if ($this->output->isVerbose()) {
				$notesProgress->clear();
				$progress->clear();
				$this->io()->writeln(sprintf("New note #%s imported for issue '<info>%s</info>'\n", ($i+1), $title));
				$progress->display();
				$notesProgress->display();
			}

			$notesProgress->advance();
		}
		$notesProgress->clear();
	}

	protected function __handleAttachments($description, $issue, $progress)
	{
		if (($attachments = $issue->attachments()) && count($attachments)) {
			$attachProgress = new ProgressBar($this->output, count($attachments));
			$attachProgress->setFormat('m2g');
			$attachProgress->setMessage('Importing attachments for #' .  $issue->id() . ' issue...');
			$attachProgress->start();

			$description .= "\n\n# Arquivos Anexados\n\n";
			foreach($issue->attachments() as $attachment) {
				$upload = new Upload($this->project(), array(
					'file' => $attachment->filename(),
					'data' => $attachment->download(),
					'content_type' => $attachment->content_type()
				));

				try {
					if (!$this->dryRun) {
						$upload = $upload->save();
					}

					$description .= "* " . $upload->raw('markdown') . "\n";

					if ($this->output->isVerbose()) {
						$attachProgress->clear();
						$progress->clear();
						$this->io()->writeln(sprintf("New file uploaded '<info>%s</info>'\n", $attachment->filename()));
						$progress->display();
						$attachProgress->display();
					}

					$attachProgress->advance();
				} catch(\Exception $e) {
						//@TODO Handle errors
				}
			}

			$attachProgress->clear();
		}

		return $description;
	}

	protected function processMilestones()
	{
		$progress = new ProgressBar($this->output, $this->mantisVersions()->count());
		$progress->setFormat('m2g');
		$progress->start();
		$progress->setMessage('Importing milestones...');

		foreach($this->mantisVersions() as $version)
		{
			$keys[] = $version->name();

			$existingMilestone = $this->milestones()->filter(function($item) use ($version) {
				return $item->raw('title') == $version->name();
			});
			if ($existingMilestone) {
				if ($this->output->isVerbose()) {
					$progress->clear();
					$this->io()->writeln(sprintf("Already mapped milestone found '<info>%s</info>'\n", $version->name()));
					$progress->display();
				}
				$progress->advance();
				continue;
			}

			if (!$this->dryRun) {
				$milestone = $this->project()->newMilestone(array(
					'title' => $version->name(),
					'description' => $version->description(),
					'due_date' => $version->dateOrder()->format('Y-m-d')
				));

				if ($version->released()) {
					$milestone->raw('state', 'closed');
					$milestone->save();
				}

				$this->milestones[] = $milestone;
			}

			if ($this->output->isVerbose()) {
				$progress->clear();
				$this->io()->writeln(sprintf("New milestone imported '<info>%s</info>'\n", $version->name()));
				$progress->display();
			}

			$progress->advance();
		}

		$progress->finish();
		$this->io()->newLine(2);
	}

	protected function gitlab()
	{
		if (empty($this->gitlab))
		{
			$this->gitlab = new Gitlab($this->config()->gitlab());
		}
		return $this->gitlab;
	}

	protected function mantis()
	{
		if (empty($this->mantis))
		{
			$this->mantis = new Mantis($this->config()->mantis());
		}
		return $this->mantis;
	}

	protected function project()
	{
		if (empty($this->gitlab->project))
		{
			$this->gitlab->project = $this->gitlab()->project($this->config()->gitlab('project'));
		}
		return $this->gitlab->project;
	}

	protected function issues()
	{
		if (empty($this->gitlab->issues))
		{
			$this->gitlab->issues = $this->project()->issues();
		}
		return $this->gitlab->issues;
	}

	protected function milestones($clear = false)
	{
		if (empty($this->gitlab->milestones) || $clear)
		{
			$this->gitlab->milestones = $this->project()->milestones();
		}
		return $this->gitlab->milestones;
	}

	protected function mantisProject()
	{
		if (empty($this->mantis->project))
		{
			$this->mantis->project = $this->mantis()->project($this->config()->mantis('project'));
		}
		return $this->mantis->project;
	}

	protected function mantisVersions()
	{
		if (empty($this->mantis->versions))
		{
			$this->mantis->versions = $this->mantisProject()->versions();
		}
		return $this->mantis->versions;
	}

	protected function mantisIssues()
	{
		if (empty($this->mantis->issues))
		{
			$this->mantis->issues = $this->mantisProject()->issues();
		}
		return $this->mantis->issues;
	}

	protected function config($input = null)
	{
		if (empty($this->configuration)) {
			if (empty($input)) {
				throw new \Exception('First call to config() should have a InputInterface class');
			}
			// clear options 
			$override = $this->sanitizeOptions($input->getOptions());
			$override = $this->splitIndexes($override);
			$this->configuration = new Configuration('./config', $override);
		}

		return $this->configuration;
	}
}
