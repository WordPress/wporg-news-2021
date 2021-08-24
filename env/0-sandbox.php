<?php

/*
 * These are stubs for closed source code, or things that only apply to local environments.
 */

namespace WordPress_org\News\Stubs;

defined( 'WPINC' ) || die();

define( 'WPORG_GIT_MUPLUGINS_DIR', dirname( ABSPATH ) . '/vendor/wporg/wporg-mu-plugins' );
// name better - how to distinguish from the muplugins in the git repo, the ones in meta.svn, and the ones in dotorg.svn
// this might need a different path for wp-env vs my local nginx env vs production
// maybe have composer install it into mu-plugins?
