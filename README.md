# Airwolf data-collection
The Airwolf data collection app is built to support the data collection efforts by [Text to Change's](http://texttochange.com) call center. The application uses the Openrosa Xform standard and relies on [Enketo](https://github.com/MartijnR/enketo) for the webform capabilities. For more information, please see [the blog post on flipside.org](http://flipside.org/notes/data-collection-for-call-centers/).

![aw-survey](https://cloud.githubusercontent.com/assets/751330/2946564/d98970ee-d9ec-11e3-9781-ff9e27067c64.png)

Built using [Codeigniter](http://ellislab.com/codeigniter)

## Team
Daniel da Silva - [Github](https://github.com/danielfdsilva)  
Ricardo Mestre - [Github](https://github.com/ricardomestre)  
Olaf Veerman - [Github](https://github.com/olafveerman)

## Development environment
### Requirements
- Node & Npm
- Grunt ( $ npm install -g grunt-cli )
- Bower ($ npm install -g bower)

### Setup
**This is to be done in the local machine, not on vagrant**

After cloning the repository, setup the public files directory and its subfolders at the root of the app:
```
$ mkdir -p files/surveys
$ chmod -R 777 files
```

Update git submodules (**This should be also done after pulling changes**):
```
$ git submodule update --recursive
```
The enketo library needs to be built. Change into its directory:
```
$ cd assets/libs/enketo-core
$ npm install
$ grunt
```

### Task automation
After these basic requirements are met, run the following commands in the website's folder:
```
$ npm install

```
```
$ bower install
```
Bower will create a ```bower_components``` directory in the src with all the sass and js needed for foundation. Nothing needs to be done there.

You might have to run these as sudo.

#### Getting started
```
$ grunt
```
Compiles the compass files, javascripts and generates the website.
The system will watch files and execute tasks whenever one of them changes.

#### Other commands
Clean the compiled sass and javascript:
```
$ grunt clean
```

Compile the compass files, javascripts and generate the website. Use this instead of ```grunt``` if you just want to render it once:
```
$ grunt build
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


## Testing
Testing should be done from within the vagrant machine.

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

