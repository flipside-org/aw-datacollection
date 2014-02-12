module.exports = function(grunt) {
  grunt.initConfig({
    // https://github.com/gruntjs/grunt-contrib-clean
    clean : ['public/css/*', 'public/scripts/*', '_site/*'],
    
    // https://github.com/gruntjs/grunt-contrib-compass
    compass : {
      // Default options.
      options : {
          sassDir : 'src/sass',
          cssDir : 'public/css',
          require : ['compass']
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
          'public/scripts/modernizr.min.js': ['src/js/vendor/custom.modernizr.js'],
          'public/scripts/script.min.js': [
            'src/js/vendor/jquery.js',
            'src/js/*.js'
          ]
        }
      }
    },
    
    // https://npmjs.org/package/grunt-contrib-watch
    watch : {
      src: {
        files: ['src/js/*.js', 'src/sass/**/*.scss'],
        tasks: ['default']
      },
      jekyll : {
        files: ['public/**/*.html', 'public/**/*.md', '!public/_site/**/*'],
        tasks: ['jekyll:generate']
      }
    },
    
    // https://github.com/dannygarcia/grunt-jekyll
    jekyll : {
      generate : {
        options : {
          config: 'public/_config.yml',
          src: 'public/',
          dest: './_site',
        }
      },
      server : {
        options : {
          config: 'public/_config.yml',
          src: 'public/',
          dest: './_site',
          serve: true
        }
      }
    }
    
  });

  // Load tasks.
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-jekyll');
  
  // Register tasks.
  grunt.registerTask('default', ['compass:dev', 'jshint:dev', 'uglify', 'jekyll:generate']);
  
  grunt.registerTask('prod', ['clean', 'compass:prod', 'jshint:prod', 'uglify']);

};
