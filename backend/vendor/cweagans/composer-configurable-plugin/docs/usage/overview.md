---
title: Overview
weight: 10
---

The goal of this library is to allow Composer plugin authors to have a unified way of defining strongly typed configuration in a standardized way.

Once a list of possible configuration values is defined, the specific value can come from one of three places:

1. A default value declared by the plugin.
2. The value set in `composer.json`
3. An environment variable.

Environment variables take precedence over values in `composer.json` or the default value, and values in `composer.json` take precedence over the default value.

This library was originally written as part of [Composer Patches](https://github.com/cweagans/composer-patches) and was later extracted into a standalone project.
