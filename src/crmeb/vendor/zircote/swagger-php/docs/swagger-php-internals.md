# How swagger-php works under the hood

## Flow

- Finder crawls the filesystem
- The (Static)Analyser reads the files and builds an Analysis object.
- The Analysis object is then processed by the Processors.
- The Analysis/Annotations are validated to notify the user of any known issues.
- The OpenApi annotation then contains all annotations and generates the openapi.json

## Annotation Context

The annotations contain metadata stored in a Context object which:

- Contains the data thats needed by the processors to infer values.
- When validation detects an error it can print the location (file and line number) of the offending annotation.

## Analysis

Contains all detected annotations and other relevant meta data.

It uses a SplObjectStorage to store the annotations, which is like an array but prevents duplicate entries.

# Documentation

Documentation is generated with [vuepress](https://vuepress.vuejs.org/)

```bash
npm -g install vuepress
cd docs
vuepress dev
```
