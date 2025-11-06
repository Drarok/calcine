# Calcine

Calcine is a static site generator; it takes Markdown input and produces a web site composed of static files.

It uses the [CommonMark](https://commonmark.thephpleague.com/2.7/) dialect of Markdown, and has these extensions enabled:
* [Attributes](https://commonmark.thephpleague.com/2.7/extensions/attributes/)
* [Footnotes](https://commonmark.thephpleague.com/2.7/extensions/footnotes/)

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
