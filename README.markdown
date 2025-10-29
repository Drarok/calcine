# Calcine [![Build Status](https://travis-ci.org/Drarok/calcine.svg?branch=develop)](https://travis-ci.org/Drarok/calcine)

Calcine is a "baked" blog generator, using Markdown files to produce a site using only static assets.

## Quick start

```shell
git clone git@github.com:drarok/calcine
cd calcine
composer install
cp app/config/calcine.json.sample app/config/calcine.json
edit app/config/calcine.json

# You now need to create a file or make a post in your CMS, then:

bin/calcine build
```

## Development

You will need a functioning Docker Desktop install. There's a small script for basic Docker operations:

```shell
./scripts/docker build # Builds the Docker image
./scripts/docker composer [install] # Runs composer in Docker container
./scripts/docker shell # Open an interactive shell in Docker container
./scripts/docker test # Execute PHPUnit in Docker container
```
