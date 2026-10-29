# WordPress.org News Theme, 2021 edition

This is starting as a fork of [Blockbase](https://github.com/Automattic/themes/tree/trunk/blockbase).

ℹ️ The header/footer live in [the mu-plugins repository](https://github.com/WordPress/wporg-mu-plugins/), but are
automatically provisioned into this one. Changes to the header/footer should be made in the `mu-plugins` repo.

## Development

### Prerequisites

* Docker
* Node/npm (see `.nvmrc` for the version)
* Composer

### Setup

1. Set up repo dependencies: `npm run initial-setup`
1. Start up and provision the environment: `npm run env:setup`
1. Visit site at `localhost:8888`
1. Log in with username `admin` and password `password`

### Environment management

These must be run in the project's root folder, _not_ in theme/plugin subfolders.

* Stop the environment: `npm run env:stop` or `npm run wp-env stop`
* Restart the environment: `npm run env` or `npm run wp-env start`
* Reset the dev site's content: `npm run env:reset && npm run env:import`
* SSH into docker container: `docker exec -it {container ID} /bin/bash`. You can get the ID from `docker ps`.

### Asset management

The theme's styles live in `sass/` and are compiled with `@wordpress/scripts` into `build/style.css`, which is committed. CI fails a pull request whose `build/` doesn't match its `sass/`, so rebuild and commit `build/` along with any style change.

* Build the theme's CSS once: `npm run build`
* Rebuild the theme's CSS on change: `npm start`
* Rebuild the theme's and header/footer assets on change, and reload the browser: `npm run start:all`
* Lint: `npm run lint:css` and `npm run lint:js`


### Sync/Deploy

The theme is deployable as-is from `trunk`, including its built CSS, so syncing doesn't need a build step. The files are committed to `dotorg.svn` so they can be deployed. They aren't synced to `meta.svn`, since they're already open in GitHub.

To sync them to `dotorg.svn`, run `bin/sync/wporg-news-2021.sh` on a w.org sandbox. Once they're committed, you can deploy like normal.
