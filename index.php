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

	$mantisProject = $mantis->project($configuration->mantis('project'));
	$mantisIssues = $mantisProject->issues();
	$project = $gitlab->project($configuration->gitlab('project'));

	echo "\e[1;38mSelected Mantis project: '\e[1;32m" . $configuration->mantis('project') . "\e[0m\e[1;38m'\n";
	echo "\e[1;38mSelected Gitlab project: '\e[1;32m" . $project->raw('name_with_namespace') . "\e[0m\e[1;38m'\n";

	$users = $gitlab->user()->all();

	foreach($mantisIssues as $mantisIssue) {
		$mantisIssueArr = $mantisIssue->toArray();
		$labels = $labelHandler->handle($mantisIssue);
		$title = $titleHandler->handle($mantisIssue);
		$description = $descriptionHandler->handle($mantisIssue);

		$handler = $mantisIssue->handler() ? $mantisIssue->handler()->name : null;
		$assignee_id = $handler && isset($users[$handler]) ? $users[$handler]->raw('id') : null;

		$issue = array(
			'title' => $title,
			'description' => $description,
			'confidential' => $mantisIssue->visibility() !== 'public' && $mantisIssue->visibility() !== null,
			'assignee_id' => $assignee_id,
			'milestone_id' => null,
			'labels' => $labels,
			'created_at' => $mantisIssue->dateSubmitted()->format('Y-m-d'),
			'due_date' => null
		);

		if ($mantisIssueArr['status'] == 'closed') {
			$issue['state_event'] = 'close';
		}

		$newIssue = $project->newIssue($issue);
		echo "\e[1;38mNew issue imported '\e[1;32m" . $title . "\e[0m\e[1;38m'\n";

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
			echo "\e[1;38mNew note #" . ($i+1) . " imported for issue '\e[1;32m" . $title . "\e[0m\e[1;38m'\n";
		}
	}
}

// we are the child
while(microtime(true) < $expire) {
	sleep(0.5);
}

exit(0);
