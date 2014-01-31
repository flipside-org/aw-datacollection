/*jshint node:true*/
"use strict";

module.exports = function( grunt ) {
    grunt.initConfig( {
        pkg: grunt.file.readJSON( 'package.json' ),
        jsbeautifier: {
            test: {
                src: [ "*.js" ],
                options: {
                    config: "./.jsbeautifyrc",
                    mode: "VERIFY_ONLY"
                }
            }
        },
        jshint: {
            options: {
                jshintrc: '.jshintrc'
            },
            all: [ '*.js' ]
        },
        jasmine: {
            test: {
                src: 'jquery.xpath.js',
                options: {
                    keepRunner: true,
                    specs: 'test/spec/*.js',
                    helpers: [],
                    vendor: [ 'http://codeorigin.jquery.com/jquery-2.0.3.min.js' ]
                }
            }
        }
    } );

    grunt.loadNpmTasks( 'grunt-jsbeautifier' );
    grunt.loadNpmTasks( 'grunt-contrib-jasmine' );
    grunt.loadNpmTasks( 'grunt-contrib-jshint' );

    grunt.registerTask( 'test', [ 'jsbeautifier:test', 'jshint', 'jasmine' ] );
    grunt.registerTask( 'default', [ 'test' ] );
};
