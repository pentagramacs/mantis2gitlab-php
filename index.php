<?php

include_once 'vendor/autoload.php';

$wait = 3;
$time = microtime(true);
$expire = $time + $wait;

// we fork the process so we don't have to wait for a timeout
$pid = pcntl_fork();
if ($pid == -1) {
	throw new \Exception('We could not fork a child.');
} else if ($pid) {
	$configuration = new M2G\Configuration('./config');

	$mantis = new M2G\Mantis($configuration->mantis());
	$gitlab = new M2G\Gitlab($configuration->gitlab());

	$labelHandler = new M2G\Handler\Label($configuration);
	$titleHandler = new M2G\Handler\Title($configuration);
	$descriptionHandler = new M2G\Handler\Description($configuration);

	echo "\e[1;38mSelected Mantis project: '\e[1;32m" . $configuration->mantis('project') . "\e[0m\e[1;38m'\e[0m\n";
	$mantisProject = $mantis->project($configuration->mantis('project'));
	$mantisIssues = $mantisProject->issues();

	echo "\e[1;38mFound \e[1;32m" . count($mantisIssues) . "\e[0m\e[1;38m issues on that project.\e[0m\n";
	
	$project = $gitlab->project($configuration->gitlab('project'));

	echo "\e[1;38mSelected Gitlab project: '\e[1;32m" . $project->raw('name_with_namespace') . "\e[0m\e[1;38m'\e[0m\n";
	$gitlabMilestones = $project->milestones();
	$gitlabIssues = $project->issues();

	echo "\e[1;38mFound \e[1;32m" . count($gitlabMilestones) . "\e[0m\e[1;38m milestones on that project.\e[0m\n";
	echo "\e[1;38mFound \e[1;32m" . count($gitlabIssues) . "\e[0m\e[1;38m issues on that project.\e[0m\n";

	echo "\n\n\e[1;38mShould we continue? (\e[1;32mY\e[0m\e[0m/\e[1;34mn\e[0m\e[1;38m)\e[0m ";

	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(strtolower(trim($line)) != 'y'){
		echo "\e[1;37mAborted.\e[0m\n";
		exit(0);
	}
	fclose($handle);

	$keys = array();
	foreach($mantisProject->versions() as $version) {
		$keys[] = $version->name();

		$existingMilestone = array_filter($gitlabMilestones, function($item) use ($version) {
			return $item->raw('title') == $version->name();
		});

		if ($existingMilestone) {
			echo "\e[1;33mAlready mapped milestone found '\e[1;32m" . $version->name() . "\e[0m\e[1;33m'\e[0m\n";
			continue;
		}

		$milestone = $project->newMilestone(array(
			'title' => $version->name(),
			'description' => $version->description(),
			'due_date' => $version->dateOrder()->format('Y-m-d')
		));

		if ($version->released()) {
			$milestone->raw('state', 'closed');
			$milestone->save();
		}

		echo "\e[1;38mNew milestone imported '\e[1;32m" . $version->name() . "\e[0m\e[1;32m'\e[0m\n";

		$gitlabMilestones[] = $milestone;
	}

	$gitlabMilestones = array_combine($keys, $gitlabMilestones);

	$users = $gitlab->user()->all();
	foreach($mantisIssues as $mantisIssue) {
		$mantisIssueArr = $mantisIssue->toArray();

		$labels = $labelHandler->handle($mantisIssue);
		$title = $titleHandler->handle($mantisIssue);
		$description = $descriptionHandler->handle($mantisIssue);

		$existingIssue = array_filter($gitlabIssues, function($item) use ($title) {
			return $item->raw('title') == $title;
		});

		if ($existingIssue) {
			echo "\e[1;33mAlready mapped issue found '\e[1;32m" . $title . "\e[0m\e[1;33m'\e[0m\n";
			continue;
		}

		$handler = $mantisIssue->handler() && property_exists($mantisIssue->handler(), 'name') ? $mantisIssue->handler()->name : null;
		if ($handler) {
			$assignee_id = $handler && isset($users[$handler]) ? $users[$handler]->raw('id') : null;
		} else {
			$assignee_id = null;
		}

		$milestone_id = (
			$gitlabMilestones && 
			$mantisIssue->target_version() &&
			!empty($gitlabMilestones[$mantisIssue->target_version()])
		) 
		? $gitlabMilestones[$mantisIssue->target_version()]->raw('id')
		: null;

		if (($attachments = $mantisIssue->attachments()) && count($attachments)) {
			$description .= "\n\n# Arquivos Anexados\n\n";
			foreach($mantisIssue->attachments() as $attachment) {
				$upload = new M2G\Gitlab\Upload($project, array(
					'file' => $attachment->filename(),
					'data' => $attachment->download(),
					'content_type' => $attachment->content_type()
				));

				try {
					$upload = $upload->save();
					$description .= "* " . $upload->raw('markdown') . "\n";
					echo "\e[1;38mNew file uploaded '\e[1;32m" . $attachment->filename() . "\e[0m\n";
				} catch(\Exception $e) {
					//@TODO Handle errors
				}
			}
		}

		$issue = array(
			'title' => $title,
			'description' => $description,
			'confidential' => $mantisIssue->visibility() !== 'public' && $mantisIssue->visibility() !== null,
			'assignee_id' => $assignee_id,
			'milestone_id' => $milestone_id,
			'labels' => $labels,
			'created_at' => $mantisIssue->dateSubmitted()->format('Y-m-d'),
			'due_date' => null
		);

		$newIssue = $project->newIssue($issue);
		echo "\e[1;38mNew issue imported '\e[1;32m" . $title . "\e[0m\e[0m\n";

		if (!isset($newIssue)) {
			exit(0);
		}

		$mantisNotes = $mantisIssue->notes();
		foreach($mantisNotes as $i => $mNote) {
			$reporter = $mNote->reporter()->name;

			$body = '### Originalmente escrito por: *' . $reporter . '*' . PHP_EOL . PHP_EOL;
			$body .= '-----';
			$body .= $mNote->text();

			$rawNote = array(
				'body' => $body,
				'created_at' => $mNote->date_submitted()
			);

			$newIssue->newNote($rawNote);
			echo "\e[1;38mNew note #" . ($i+1) . " imported for issue '\e[1;32m" . $title . "\e[0m\e[1;38m'\e[0m\n";
		}

		if ($mantisIssueArr['status'] == 'closed') {
			$newIssue->close();
			echo "\e[1;38mClosed issue '\e[1;32m" . $title . "\e[0m\e[1;32m'\e[0m\n";
		}

	}
}

// we are the child
while(microtime(true) < $expire) {
	sleep(0.5);
}

exit(0);
