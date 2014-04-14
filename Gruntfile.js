module.exports = function(grunt) {
  grunt.initConfig({
    // https://github.com/gruntjs/grunt-contrib-clean
    clean : {
      // Delete everything.
      reset: ['assets/styles/*', 'assets/scripts/*', 'assets/images/vendor/*'],
      // Delete build files.
      build: ['src/temp/*']
    },
    
    // https://github.com/gruntjs/grunt-contrib-compass
    compass : {
      // Default options.
      options : {
          sassDir : 'src/styles',
          cssDir : 'src/temp',
          raw : 'add_import_path "src/bower_components/foundation/scss"'
      },
      
      dev : {
        options : {
          environment: 'development',
          outputStyle: 'expanded',
          debugInfo : true
        }
      },
      prod : {
        options : {
          environment: 'production',
          outputStyle: 'compressed',
        }
      }
    },
        
    // https://github.com/gruntjs/grunt-contrib-jshint
    jshint: {
      // Default options.
      options : {
        unused : true
      },
      
      dev : {
        options : {
          force : true
        },
        src : ['src/scripts/**.js']
      },
      
      prod: ['src/scripts/**.js']
    },
    
    // https://github.com/gruntjs/grunt-contrib-uglify
    uglify: {
      prod: {
        files: {
          'assets/scripts/basic.min.js': [
            'src/bower_components/modernizr/modernizr.js',
            'src/bower_components/jquery/dist/jquery.js'
          ],

          // Enketo stuff.
          'assets/scripts/enketo_base.min.js' : [
            'src/scripts/enketo/connection.js',
            'src/scripts/enketo/respondentQueue.js',
            'src/scripts/enketo/submissionQueue.js',
          ],
          'assets/scripts/enketo_collection_single.min.js' : [
            'src/scripts/enketo/enketo_collection_single.js'
          ],
          'assets/scripts/enketo_collection.min.js' : [
            'src/scripts/enketo/enketo_collection.js'
          ],
          'assets/scripts/enketo_testrun.min.js' : [
            'src/scripts/enketo/enketo_testrun.js'
          ],

          'assets/scripts/website.min.js': [
            // Foundation includes.
            'src/bower_components/foundation/js/foundation/foundation.js',
            //'src/bower_components/foundation/js/foundation/foundation.abide.js',
            //'src/bower_components/foundation/js/foundation/foundation.accordion.js',
            'src/bower_components/foundation/js/foundation/foundation.clearing.js',
            'src/bower_components/foundation/js/foundation/foundation.dropdown.js',
            'src/bower_components/foundation/js/foundation/foundation.interchange.js',
            //'src/bower_components/foundation/js/foundation/foundation.joyride.js',
            //'src/bower_components/foundation/js/foundation/foundation.magellan.js',
            //'src/bower_components/foundation/js/foundation/foundation.offcanvas.js',
            //'src/bower_components/foundation/js/foundation/foundation.orbit.js',
            //'src/bower_components/foundation/js/foundation/foundation.reveal.js',
            //'src/bower_components/foundation/js/foundation/foundation.tab.js',
            //'src/bower_components/foundation/js/foundation/foundation.tooltips.js',
            'src/bower_components/foundation/js/foundation/foundation.topbar.js',

            // Site scripts.
            'src/vendor/chosen/chosen.jquery.min.js',
            'src/vendor/jquery-toastmessage/src/main/javascript/jquery.toastmessage.js',

            'src/vendor/raphael/raphael-min.js',
            'src/vendor/morris/morris.js',

            'src/scripts/*.js'
           ],
        }
      }
    },
    
    // https://github.com/gruntjs/grunt-contrib-concat
    concat : {
      dev : {
        files : {
          'assets/styles/main.css' : [
            'src/vendor/chosen/chosen.css',
            'src/vendor/jquery-toastmessage/src/main/resources/css/jquery.toastmessage.css',
            'src/vendor/morris/morris.css',
            'src/temp/path_override.css',
            'src/temp/main.css'
          ]
        }
      },
      prod : {
        files : {
          'assets/styles/main.css' : [
            'src/vendor/chosen/chosen.min.css',
            'src/vendor/jquery-toastmessage/src/main/resources/css/jquery.toastmessage.css',
            'src/vendor/morris/morris.css',
            'src/temp/path_override.css',
            'src/temp/main.css'
          ]
        }
      }
    },
    
    // https://github.com/gruntjs/grunt-contrib-copy
    copy: {
      main: {
        files: {
          'assets/images/vendor/chosen-sprite.png' : 'src/vendor/chosen/chosen-sprite.png',
          'assets/images/vendor/chosen-sprite@2x.png' : 'src/vendor/chosen/chosen-sprite@2x.png'
        }
      }
    },
    
    // https://npmjs.org/package/grunt-contrib-watch
    watch : {
      src: {
        files: ['src/scripts/**.js', 'src/scripts/enketo/**.js', 'src/styles/*.scss'],
        tasks: ['build']
      }
    }
    
  });

  // Load tasks.
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-copy');
  
  // Register tasks.
  grunt.registerTask('build', ['compass:dev', 'jshint:dev', 'uglify', 'concat:dev']);
  
  grunt.registerTask('default', ['build', 'watch']);
  
  grunt.registerTask('prod', ['clean', 'compass:prod', 'jshint:prod', 'uglify', 'concat:prod', 'copy']);

};