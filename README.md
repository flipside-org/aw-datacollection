# Airwolf data-collection app

The Airwolf application that handles data collection.

Built using [Codeigniter](http://ellislab.com/codeigniter)


## Requirements
- Apache
- PHP 5.3
- MongoDB

`@todo`


## Setup

After cloning the repository, setup the public files directory and its subfolders at the root of the app:
```
$ mkdir -p files/surveys
$ chmod -R 777 files
```

## Testing
Testing is being done with phpunit.  
There isn't a seamless way to integrate codeigniter and phpunit. To allow fully integration, two changes must be done to core.  
Check https://github.com/fmalk/codeigniter-phpunit for more.

The "TOAST - Unit Testing for CodeIgniter" was also tried but it isn't as powerful as phpunit. TOAST has a controller that only implements some methods that are available by default on phpunit. Also TOAST requires files (views, controllers) to be side by side with the application while phpunit can be separated. 