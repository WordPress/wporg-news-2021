# WordPress.org News Theme, 2021 edition

This is starting as a fork of [Blockbase](https://github.com/Automattic/themes/tree/trunk/blockbase).

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
* Rebuild all assets on change: `npm run start:all`
