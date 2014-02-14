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
$ cd assets/libs/enketo-core/
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
Testing is being done with phpunit.  

`@todo Explain how to work with this and move explanation.`

There isn't a seamless way to integrate codeigniter and phpunit. To allow fully integration, two changes must be done to core. 
Check https://github.com/fmalk/codeigniter-phpunit for more.

The "TOAST - Unit Testing for CodeIgniter" was also tried but it isn't as powerful as phpunit. TOAST has a controller that only implements some methods that are available by default on phpunit. Also TOAST requires files (views, controllers) to be side by side with the application while phpunit can be separated.
