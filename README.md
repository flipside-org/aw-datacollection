# Airwolf data-collection
The Airwolf data collection app is built to support the data collection efforts by [Text to Change's](http://texttochange.com) call center. The application uses the Openrosa Xform standard and relies on [Enketo](https://github.com/MartijnR/enketo) for the webform capabilities. For more information, please see [the blog post on flipside.org](http://flipside.org/notes/data-collection-for-call-centers/).

![aw-survey](https://cloud.githubusercontent.com/assets/751330/2946564/d98970ee-d9ec-11e3-9781-ff9e27067c64.png)

Built using [Codeigniter](http://ellislab.com/codeigniter)

## Team
Daniel da Silva - [Github](https://github.com/danielfdsilva)  
Ricardo Mestre - [Github](https://github.com/ricardomestre)
Olaf Veerman - [Github](https://github.com/olafveerman)  
Nuno Veloso - [Github](https://github.com/nunoveloso)  

___

## Development environment
To ease development, everything was bundled in a vagrant box. To set it up, simply run ```vagrant up``` from the root of the project. For more details about the Vagrant box and useful commands, check out the [instructions in the wiki](https://github.com/flipside-org/aw-datacollection/wiki/Vagrant-box).  

If you want to develop locally without using the vagrant box, check the [local development section](https://github.com/flipside-org/aw-datacollection/wiki/Local-development) for the needed dependencies.


### Requirements
These dependencies are needed to build the app no matter whether you use the Vagrant box or manually set up the environment:

- Node & Npm
- Grunt ( $ npm install -g grunt-cli )
- Bower ($ npm install -g bower)
- Sass
- Compass (>= *1.0.0.alpha.19* ```$ sudo gem install compass -v 1.0.0.alpha.19 --pre```)

### Setup

Initialize and update git submodules:
```
$ git submodule update --init --recursive
```
Subsequent updates of git submodules must be done without the ```--init``` flag

Install airwolf dependencies:
```
$ npm install
```
```
$ bower install
```

The enketo library needs to be built:
```
$ cd assets/libs/enketo-core
$ npm install
$ grunt --force
```
Patch pyxform library, from the app root folder:
```
$ git apply --directory=application/third_party/pyxform/ pyxform_validate_and_constants.patch
```

Build the CSS and Javascript, from the root folder of the 

```
$ grunt build
```
___

## First run
To setup the application go to ```http://your-domain.com/fixtures``` or ```http://192.168.99.10/airwolf/fixtures``` if you're using the vagrant box.

This will give you 2 options to setup the application:
- **Live**
  - All the data present in the application will be removed and a user will be added.
  - Default credentials: **admin** | admin
- **Development**
  - All the data present in the application will be replaced with dummy data.
  - This data includes surveys in various statuses and several users.
  - Administrator: **admin** | admin
  - Moderator: **moderator** | moderator
  - Agent: **agent** | agent
  - User with all roles: **all_roles** | all_roles

After this setup, change the environment to *production* on ```index.php```
___

## Build automation
Grunt is used for the build automation.

```
$ grunt
```
Compiles the compass files, javascript and generates the website.
The system will watch files and execute tasks whenever one of them changes.

### Other commands
Clean the compiled sass and javascript:
```
$ grunt clean
```

Compile the compass files, javascript and generate the website. Use this instead of ```grunt``` if you just want to render it once:
```
$ grunt build
```

Compile the compass files and javascripts prepared for production (minified, uglyfied). Every time changes will be pushed to production, this command needs to be run:
```
$ grunt prod
```
___

## Testing
Testing is done using phpunit. To run the tests you just need to run ```phpunit``` in the app's root folder.  
If you're using the Vagrant box environment be sure to do this inside the vagrant machine.