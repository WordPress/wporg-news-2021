#!/bin/bash

root=$( dirname $( wp config path ) )

wp import "${root}/env/media.xml" --authors=create
wp import "${root}/env/podcasts.xml" --authors=create
wp import "${root}/env/posts.xml" --authors=create
