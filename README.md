# Airwolf data-collection app

The Airwolf application that handles data collection. 
Built using [Codeigniter](http://ellislab.com/codeigniter)

## Local Machine
### Requirements
- Node & Npm
- Grunt ( $ npm install -g grunt-cli )

### Setup
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

### Task automation
After these basic requirements are met, run the following command in the website's folder:
```
$ npm install
```
You might have to run these as sudo.

#### Getting started
```
$ grunt watch
```
Compiles the compass files, javascripts and generates the website.
The system will watch files and execute tasks whenever one of them changes.

#### Other commands
Clean the compiled sass and javascript:
```
$ grunt clean
```

Compile the compass files, javascripts and generate the website. Use this instead of ```grunt watch``` if you just want to render it once:
```
$ grunt
```

Compile the compass files and javascripts prepared for production (minified, uglyfied). Every time changes will be pushed to production, this command needs to be run:
```
$ grunt prod
```

### Running
**Temporary**  
The aw-datacollection app requires mongo.  
Ssh into the vagrant machine and start mongo.
```
$ vagrant ssh
$ mongod
```

## Vagrant machine

### Requirements
- No requirements (Everything needed is installed during bootstrap)

### Project Testing
Server side testing is being done with phpunit. 
Check the documentation for more.

Testing is also done with casper.
`@todo`