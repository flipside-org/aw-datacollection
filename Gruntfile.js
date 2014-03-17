module.exports = function(grunt) {
  grunt.initConfig({
    // https://github.com/gruntjs/grunt-contrib-clean
    clean : ['assets/css/*', 'assets/scripts/*'],
    
    // https://github.com/gruntjs/grunt-contrib-compass
    compass : {
      // Default options.
      options : {
          sassDir : 'src/sass',
          cssDir : 'assets/css',
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
        src : ['src/js/*.js']
      },
      
      prod: ['src/js/*.js']
    },
    
    // https://github.com/gruntjs/grunt-contrib-uglify
    uglify: {
      prod: {
        files: {
          'assets/scripts/website.min.js': [
            'src/js/libs/chosen/chosen.jquery.min.js',
            'src/js/*.js'
           ],
          
          'assets/scripts/media.min.js': [
            'src/bower_components/modernizr/modernizr.js',
          ],
          
          // Enketo stuff
          'assets/scripts/enketo_base.min.js' : [
            'src/js/enketo/connection.js',
            'src/js/enketo/respondentQueue.js',
            'src/js/enketo/submissionQueue.js',
          ],
          'assets/scripts/enketo_collection_single.min.js' : [
            'src/js/enketo/enketo_collection_single.js'
          ],
          'assets/scripts/enketo_collection.min.js' : [
            'src/js/enketo/enketo_collection.js'
          ],
          'assets/scripts/enketo_testrun.min.js' : [
            'src/js/enketo/enketo_testrun.js'
          ],
          
          'assets/scripts/foundation.min.js': [
            'src/bower_components/jquery/dist/jquery.js',
            
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
            'src/bower_components/foundation/js/foundation/foundation.topbar.js'
          ]
        }
      }
    },
    
    // https://npmjs.org/package/grunt-contrib-watch
    watch : {
      src: {
        files: ['src/js/*.js', 'src/sass/*.scss'],
        tasks: ['default']
      }
    },
    
    // https://github.com/gruntjs/grunt-contrib-concat
    concat : {
      
    }
    
  });

  // Load tasks.
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-concat');
  
  // Register tasks.
  grunt.registerTask('build', ['compass:dev', 'jshint:dev', 'uglify']);
  
  grunt.registerTask('default', ['build', 'watch']);
  
  grunt.registerTask('prod', ['clean', 'compass:prod', 'jshint:prod', 'uglify']);

};
