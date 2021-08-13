#!/bin/bash

wp theme activate wporg-news-2021

wp rewrite structure '/%year%/%monthnum%/%postname%/'
