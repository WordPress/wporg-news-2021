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
2. Start up and provision the environment: `yarn run env:setup`
3. Build the assets: `yarn workspaces run build`
1. Visit site at `localhost:8888`
1. Log in with username `admin` and password `password`

### Environment management

* Stop the environment: `yarn run env:stop` or `yarn run wp-env stop`
* Restart the environment: `yarn run env` or `yarn run wp-env start`
* Reset the dev site's content: `yarn run env:reset && yarn run env:import`

### Asset management

* Build all assets once: `yarn workspaces run build`
* Rebuild all assets on change: `yarn workspaces run start`
