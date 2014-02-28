# Airwolf data-collection app

The Airwolf application that handles data collection.

Built using [Codeigniter](http://ellislab.com/codeigniter)


## Requirements
- Apache
- PHP 5.3
- MongoDB
- Node & Npm
- Grunt

`@todo`


## Setup
**This is to be done in the local machine, not on vagrant**

After cloning the repository, setup the public files directory and its subfolders at the root of the app:
```
$ mkdir -p files/surveys
$ chmod -R 777 files
```

Update git submodules (**This should be also done after pulling changes**):
```
$ git submdodule update --recursive
```
The enketo library needs to be built. Change into its directory:
```
$ cd assets/libs/enketo-core
$ npm install
$ grunt
```

## Running
**Temporary**  
The aw-datacollection app requires mongo.  
Ssh into the vagrant machine and start mongo.
```
$ vagrant ssh
$ mongod
```

## Testing

### Code testing

Server side testing is being done with phpunit.
Check the documentation for more.

### Behaviour testing

This is implemented with [CasperJS](http://casperjs.org/) and currently living under `tests/casperjs`.

```
@todo
- integration with grunt
- document usage
```
