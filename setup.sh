#!/bin/bash

if [ ! -d app ]; then
    mkdir app;
fi

sqlite3 app/calcine.sqlite3 <<EOT
CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT, "date" TEXT, "slug" TEXT, "title" TEXT, "body" TEXT);
CREATE INDEX "posts:date" ON posts (date);
CREATE INDEX "posts:slug" ON posts (slug);

CREATE TABLE tags (id INTEGER PRIMARY KEY AUTOINCREMENT, "name" TEXT);
CREATE UNIQUE INDEX "tags:name" ON tags ("name");

CREATE TABLE posts_tags (post_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY (post_id, tag_id));
CREATE INDEX "posts_tags:tag_id" ON posts_tags (tag_id);
EOT
