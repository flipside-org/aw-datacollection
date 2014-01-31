/*jshint node:true*/
"use strict";

module.exports = function( grunt ) {
  var js;

  // Project configuration.
  grunt.initConfig( {
    pkg: grunt.file.readJSON( 'package.json' ),
    connect: {
      server: {
        options: {
          port: 8887
        }
      }
      /*,
        test: {
          options: {
            port: 8000
          }
        }*/
    },
    jshint: {
      options: {
        jshintrc: '.jshintrc'
      },
      all: [ 'Gruntfile.js', 'src/**/*.js' ]
    }
    /*,
      jasmine: {
        test: {
          src: 'src/*.js',
          options: {
            specs: 'test/spec/*.js',
            host: 'http://127.0.0.1:8000/',
            template: require( 'grunt-template-jasmine-requirejs' ),
            templateOptions: {
              requireConfig: {
                baseUrl: 'lib',
                paths: {
                  src: '../src'
                },
                map: {
                  '*': {
                    'src': '../src'
                  }
                }
              }
            }
          },
        } 
    }*/
  } );

  //grunt.loadNpmTasks( 'grunt-contrib-jasmine' );
  grunt.loadNpmTasks( 'grunt-contrib-jshint' );
  grunt.loadNpmTasks( 'grunt-contrib-connect' );

  //grunt.registerTask( 'test', [ 'connect:test', 'jasmine' ] );
  grunt.registerTask( 'server', [ 'connect:server:keepalive' ] );
  grunt.registerTask( 'default', [ 'jshint' ] );
};