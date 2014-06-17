# Airwolf data-collection
The Airwolf data collection app is built to support the data collection efforts by [Text to Change's](http://texttochange.com) call center. The application uses the Openrosa Xform standard and relies on [Enketo](https://github.com/MartijnR/enketo) for the webform capabilities. For more information, please see [the blog post on flipside.org](http://flipside.org/notes/data-collection-for-call-centers/).

![aw-survey](https://cloud.githubusercontent.com/assets/751330/2946564/d98970ee-d9ec-11e3-9781-ff9e27067c64.png)

Built using [Codeigniter](http://ellislab.com/codeigniter)

## Team
Daniel da Silva - [Github](https://github.com/danielfdsilva)  
Olaf Veerman - [Github](https://github.com/olafveerman)  
Nuno Veloso - [Github](https://github.com/nunoveloso)  
Ricardo Mestre - [Github](https://github.com/ricardomestre)

___

## Development environment
To ease development everything was bundled in a vagrant box. To setup the vagrant box check out the [instructions in the wiki]().  

If you want to develop locally, without using the vagrant box, check the [local development section]() for the needed dependencies.


## Requirements
These dependencies and setup are needed to build the app no matter the development environment you choose to use, and must be done on your machine.
- Node & Npm
- Grunt ( $ npm install -g grunt-cli )
- Bower ($ npm install -g bower)

### Setup

Update git submodules:
```
$ git submodule update --recursive
```

Install airwolf dependencies:
```
$ npm install

```
```
$ bower install
```

The enketo library needs to be built. Change into its directory:
```
$ cd assets/libs/enketo-core
$ npm install
$ grunt
```
Edit pyxform (xls2xform.py)
```
vim application/third_party/pyxform/pyxform/xls2xform.py
```
On line ```22``` change ```validate=True``` to ```validate=False```
___

## Getting started
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

## First run
To setup the application go to ```http://your-domain.com/fixtures``` or ```http://192.168.99.10/work/aw-datacollection/fixtures``` if you're using the vagrant box.  

This will give you 2 options to setup the application:
- **Live**
  - All the data present in the application will be removed and a user will be added.
  - Default credentials: **admin** | admin
- **Development**
  - All the data present in the application will be replaced with dummy data.
  - This data includes surveys in various statuses and several users.
  - @todo: add link with more info.
___

## Testing
Testing is done using phpunit. To rn the tests you just need to run ```phpunit``` in the app root folder.  
If you're using the Vagrant box environment be sure to do this inside the vagrant machine.