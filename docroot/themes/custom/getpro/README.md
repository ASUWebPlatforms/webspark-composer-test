# Get Protected (getpro) subtheme

This is a clone of the Renovation theme in Webspark 2.0.

## Installation

Ensure that the Radix theme and the components module are installed. Later we can decouple this.

This theme uses [Webpack](https://webpack.js.org) to compile and bundle SASS and JS.

#### Step 1

Make sure you have Node and npm installed.
[Guide on how to install node]https://docs.npmjs.com/getting-started/installing-node

If you prefer to use [Yarn](https://yarnpkg.com) instead of npm, install Yarn by following the Yarn
[guide](https://yarnpkg.com/docs/install).

#### Step 2
Go to the directory root of this theme and run the following commands: `npm install` or `yarn install`.

#### Step 3
Update `proxy` in **webpack.mix.json**.

#### Step 4
Run the following command to compile Sass and watch for changes: `npm run watch` or `yarn watch`.
