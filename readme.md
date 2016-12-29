# mantis2gitlab

This tool is used to migrate a mantis project to GitLab. It's currently a work-in-progress project and we are expecting collaboration from anyone.

## Features

- Issues
- Issues Notes
- Issues Uploads
- Map Categories to Labels
- Map Custom Fields to Labels
- Versions to Milestones
- Closed Milestones
- Closed Issues

If you want something that is not yet implemented, feel free to drop a request.

## Installation

Run the composer require:

`composer global require pentagramacs/mantis2gitlab-php`

Make sure you have the `COMPOSER_DIR/bin` added to your path.

Run `mantis2gitlab list` for the commands list and help.

## Steps to use

* Configure the `configs/*.php` as followed by the templates.
    * There's no obrigatory file to configure
    * The configuration files now are used for defaulting options like gitlab and mantis endpoint, project, access token, projects and so on...
    * The `label.php` file is the only way to map mantis information to gitlab as labels.
    * The `gitlab.php` file is the only way to change set what is mapped and how a few things are mapped.
