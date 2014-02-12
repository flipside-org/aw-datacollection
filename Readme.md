## Installation
To set up the development environment for this website, you'll need to install the following on your system:

- Npm
- compass & sass
- Grunt ( $ npm install -g grunt-cli )
- jekyll ( $ gem install jekyll )


After these basic requirements are met, run the following command in the website's folder:
```
$ npm install
```

You might have to run these as sudo.

## Getting started
To set up your development environment, you'll have to run the following two commands in seperate terminals.

```
$ grunt watch
```
Compiles the compass files, javascripts and generates the website.
The system will watch files and execute tasks whenever one of them changes.

```
$ grunt jekyll:server
```
Spans a jekyll server, the website will be accesible on localhost:4000.

### Other commands
Clean the compiled sass, javascript, and _/site:
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