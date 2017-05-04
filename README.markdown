# Calcine [![Build Status](https://travis-ci.org/Drarok/calcine.svg?branch=develop)](https://travis-ci.org/Drarok/calcine)

Calcine is a "baked" blog generator, using Markdown files to produce a site using only static assets.

## Quick start

```bash
git clone git@github.com:drarok/calcine
cd calcine
composer install
cp app/config/calcine.json.sample app/config/calcine.json
edit app/config/calcine.json
bin/calcine new-post blog-post-filename
# You now need to edit the file created by the previous command ^
bin/calcine build
```
