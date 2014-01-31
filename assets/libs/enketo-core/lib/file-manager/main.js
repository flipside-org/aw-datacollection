requirejs.config( {
  baseUrl: 'lib/',
  paths: {
    src: '../src'
  }
} );

requirejs( [ 'src/file-manager.js' ], function( fileManager ) {

  console.log( 'loaded file manager!', fileManager );

  fileManager.init( );

  if ( fileManager.isSupported( ) ) {
    console.log( 'and it is supported in this browser!' );
  } else {
    console.error( 'but it is not supported in this browser' );
  }

} );