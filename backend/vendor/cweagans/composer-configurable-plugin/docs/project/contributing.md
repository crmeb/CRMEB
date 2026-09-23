---
title: Contributing
weight: 10
---

{{< lead text="Want to contribute to the project? Here's how." >}}

## Reporting issues

There are templates for bug reports and feature requests. Please use them. Issues opened with minimal information or not using one of the templates may be closed without comment.

When reporting issues, please try to be as descriptive as possible and include as much relevant information as you can. When providing the output of a command, please run the command in "very very verbose" mode (for Composer commands, this is `-vvv`; e.g. `composer install -vvv`).

### Security vulnerabilities

Please see the [security policy]({{< relref "security.md" >}}).

### Support

Support is handled through GitHub Discussions. Before asking for support, please read the relevant documentation. If an answer is too hard to find, please open an issue. Support requests submitted as an issue may be converted to a discussion or closed.

## Pull requests

In general, it's better to discuss a potential change before implementing it. If you have a specific feature in mind, please open a feature request and mention that you're planning on working on it. If a feature request already exists and has the `help-wanted` label, it's likely that a pull request adding the feature would be accepted.

### Workflow

Fork the project, create a feature branch, and send a pull request. Please sign your commits if possible.

Any added code should have test coverage of some kind. All PHP files in the project should comply with [PSR-12](https://www.php-fig.org/psr/psr-12/).

### Coding style fixes

Pull requests that are solely code style fixes are generally not accepted. Style fixes should be done as part of other pull requests to avoid unnecessary churn.

## Documentation

There are _always_ improvements that can be made to the documentation. There is some information about how to write documentation for this project and others in the [meta documentation](https://docs.cweagans.net/meta). If you need any assistance with documentation improvements, feel free to open an issue with some details on what you need.

## Sponsorship

If you don't have the time/energy to directly work on the project (or you're simply feeling generous), sponsorships are gratefully accepted via [GitHub Sponsors](https://github.com/sponsors/cweagans).
