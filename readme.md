# WordPress.org News Theme, 2021 edition

This is starting as a fork of [Blockbase](https://github.com/Automattic/themes/tree/trunk/blockbase).

ℹ️ The header/footer live in [the mu-plugins repository](https://github.com/WordPress/wporg-mu-plugins/), but are
automatically provisioned into this one. Changes to the header/footer should be made in the `mu-plugins` repo.

## Development

### Prerequisites

* Docker
* Node/npm
* Yarn
* Composer

### Setup

1. Set up repo dependencies: `yarn run initial-setup`
1. Build the assets: `yarn workspaces run build`. The theme can't be activated until this step is done.
1. Start up and provision the environment: `yarn run env:setup`
1. Visit site at `localhost:8888`
1. Log in with username `admin` and password `password`

### Environment management

These must be run in the project's root folder, _not_ in theme/plugin subfolders.

* Stop the environment: `yarn run env:stop` or `yarn run wp-env stop`
* Restart the environment: `yarn run env` or `yarn run wp-env start`
* Reset the dev site's content: `yarn run env:reset && yarn run env:import`
* SSH into docker container: `docker exec -it {container ID} /bin/bash`. You can get the ID from `docker ps`.

### Asset management

* Build all assets once: `yarn workspaces run build`
* Rebuild all assets on change: `yarn run start:all`


### Sync/Deploy

The built files are committed to `dotorg.svn` so they can be deployed. They aren't synced to `meta.svn`, since they're already open in GitHub.

To sync these to `dotorg.svn`, run `bin/sync/news.sh` on a w.org sandbox. Once they're committed, you can deploy like normal.
