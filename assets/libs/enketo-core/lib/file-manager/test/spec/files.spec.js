if ( typeof define !== 'function' ) {
  var define = require( 'amdefine' )( module );
}

define( [ "src/file-manager", "jquery" ], function( fileManager, $ ) {

  function getFakeFile( name, type, mb ) {
    var i, fakefile, bytes, content = "";
    name = name || 'fakefile';
    mb = mb || 1;
    type = type || "image/png";
    bytes = mb * 1024 * 1024;
    for ( i = 0; i < bytes / 10; i++ ) {
      content += "abkdksajbl";
    }
    fakefile = new Blob( [ content ], {
      type: type
    } );
    fakefile.name = name;
    return fakefile;
  }

  describe( "File API", function( ) {
    var result;

    it( 'is supported by this browser', function( ) {
      expect( fileManager.isSupported( ) ).toBe( true );
    } );
  } );

  describe( "FileManager", function( ) {
    var result,
      initialized = false,
      folder = 'myfolder';

    $( document ).on( 'filesystemready', function( ) {
      initialized = true;
    } );

    beforeEach( function( ) {
      var complete = false;

      waitsFor( function( ) {
        return initialized; //typeof fileManager.getFS( ) !== 'undefined';
      }, 'the fileManager to initalize', 1000 );

      runs( function( ) {
        fileManager.setDir( folder, {
          success: function( ) {
            result = 'success';
            complete = true;
          },
          error: function( ) {
            result = 'error';
            complete = true;
          }
        } );
      } );

      waitsFor( function( ) {
        return complete;
      }, 'a folder to be created', 2000 );
    } );

    //cleanup
    afterEach( function( ) {
      var deleteResult = null,
        deleteComplete = null;

      $( document ).off( 'quotarequest' );

      runs( function( ) {
        fileManager.deleteDir( folder, {
          success: function( ) {
            deleteResult = 'success';
            deleteComplete = true;
          }
        } );
        complete = null;
        result = null;
      } );

      waitsFor( function( ) {
        return deleteComplete;
      }, 'the directory to be deleted', 1000 );
    } );

    it( 'initializes successfully (if the user gives permission), gets 100 Mb', function( ) {
      runs( function( ) {
        expect( result ).toEqual( 'success' );
        expect( fileManager.getCurrentQuota( ) ).toEqual( 100 * 1024 * 1024 );
        //expect(storageUsed >= 0  && storageUsed <200).toBe(true);
        //expect( fileManager.getCurrentQuotaUsed( ) ).toEqual( 162 );
      } );
    } );

    /**
		Some info on this important-but-difficult-to-run test.
	
		1. Best to clear all previous permissions for File Storage first.
		2. Run tests. Approve first permission.
		3. Run tests again.
		4. Don't approve second permission request. This should make all tests pass.

		If the quota requested and granted at some time in the past
		was greater than the quota requested currently for this subdomain, the user will not be prompted to
		increase storage if the required storage is larger than the available quota but less than that previously
		granted quota!

		HTML5 FileSystem Explorer chrome extension is your friend to clear all files for a subdomain

    Clearing permissions seems not possible at the time of writing in Chrome. The only way I get this to work is by changing the domain.
	**/
    it( 'detects when the approved storage quota is no longer sufficient and asks user for permission to use more', function( ) {
      var quotaAvailable = fileManager.getCurrentQuota( ),
        fileSize = quotaAvailable + 15000,
        file = getFakeFile( 'toolargefakefile', 'image/png', fileSize / ( 1024 * 1024 ) ),
        saveResult = null,
        saveComplete = null,
        fsURL = null,
        quotaUsedStart = fileManager.getCurrentQuotaUsed( );

      runs( function( ) {
        quotaRequestSpy = jasmine.createSpy( 'quotaRequestSpy' );

        $( document ).on( 'quotarequest', function( event, bytes ) {
          console.log( 'quotarequest detected for ' + bytes + ' bytes' );
          quotaRequestSpy( bytes );
        } );

        fileManager.saveFile( file, {
          success: function( url ) {
            saveComplete = true;
            saveResult = 'success';
            fsURL = url;
          },
          error: function( ) {
            saveComplete = true;
            saveResult = 'error';
          }
        } );
      } );

      waitsFor( function( ) {
        return quotaRequestSpy.calls.length === 1 || saveComplete === true;
        //user must not approve request (2nd request)
      }, 'the file save operation to complete', 1500 );

      runs( function( ) {
        expect( saveResult ).toEqual( null );
        expect( quotaRequestSpy.calls[ 0 ].args[ 0 ] ).toBeGreaterThan( fileSize );
      } );
    } );
  } );


  describe( 'A directory', function( ) {
    var quotaUsedStart,
      createResult = null,
      createComplete = null,
      dirName = "adirectory";

    beforeEach( function( ) {

      runs( function( ) {
        fileManager.setDir( dirName, {
          success: function( ) {
            createResult = 'success';
            createComplete = true;
          },
          error: function( ) {
            createResult = 'error';
            createComplete = true;
          }
        } );
      } );

      waitsFor( function( ) {
        return createComplete;
      }, 'directory creation is complete', 1000 );

    } );

    it( 'is created and removed successfully', function( ) {
      var delResult = null,
        delComplete = null,
        quotaUsedStart = fileManager.getCurrentQuotaUsed( );

      runs( function( ) {
        expect( createResult ).toEqual( 'success' );
        expect( fileManager.getCurrentQuotaUsed( ) ).toBeGreaterThan( 0 );

        fileManager.deleteDir( dirName, {
          success: function( ) {
            delResult = 'success';
            delComplete = true;
          },
          error: function( ) {
            delResult = 'error';
            delComplete = true;
          }
        } );
      } );

      waitsFor( function( ) {
        return delComplete;
      }, 'directory deletion to complete', 1000 );

      runs( function( ) {
        expect( delResult ).toEqual( 'success' );
      } );

      waitsFor( function( ) {
        return fileManager.getCurrentQuotaUsed( ) != quotaUsedStart;
      }, 'quotaused to be updated', 1000 );

      runs( function( ) {
        expect( fileManager.getCurrentQuotaUsed( ) ).toBeLessThan( quotaUsedStart );
      } );
    } );
  } );

  describe( 'A file', function( ) {
    var fileSizeMB = 1.5,
      fileName = 'fakefile2',
      file = getFakeFile( fileName, 'image/png', fileSizeMB ),
      saveResult = null,
      saveComplete = null,
      fsURL = null,
      folder = 'mydir';

    beforeEach( function( ) {
      var createComplete, createResult;

      runs( function( ) {
        fileManager.setDir( folder, {
          success: function( ) {
            createResult = 'success';
            createComplete = true;
          },
          error: function( ) {
            createResult = 'error';
            createComplete = true;
          }
        } );
      } );

      waitsFor( function( ) {
        return createComplete;
      }, 'directory creation is complete', 1000 );

      runs( function( ) {
        fileManager.saveFile( file, {
          success: function( url ) {
            saveComplete = true;
            saveResult = 'success';
            fsURL = url;
          },
          error: function( ) {
            saveComplete = true;
            saveResult = 'error';
          }
        } );
      } );

      waitsFor( function( ) {
        return saveComplete;
      }, 'the file save operation to complete', 1000 );

    } );

    afterEach( function( ) {
      var delComplete;
      runs( function( ) {
        fileManager.deleteDir( folder, {
          success: function( ) {
            delComplete = true;
          },
          error: function( ) {}
        } );
      } );

      waitsFor( function( ) {
        return delComplete === true;
      } );
    } );

    it( 'is stored successfully', function( ) {
      runs( function( ) {
        expect( saveResult ).toEqual( 'success' );
        expect( fsURL.indexOf( 'filesystem:http://' ) ).toEqual( 0 );
        expect( fsURL.length ).toBeGreaterThan( 18 );
        //expect(fileManager.getCurrentQuotaUsed()).toEqual(quotaUsedStart + (fileSizeMB * 1024 * 1024));
        //expect( fileManager.getCurrentQuotaUsed( ) ).toBeGreaterThan( quotaUsedStart );
      } );
    } );

    it( 'is retrieved successfully', function( ) {
      var retrieveSuccess = false,
        fileResult;

      runs( function( ) {
        fileManager.retrieveFile(
          folder, {
            newName: 'whatever',
            fileName: fileName
          }, {
            success: function( fileO ) {
              retrieveSuccess = true;
              fileResult = fileO.file;
            },
            error: function( ) {
              retrieveComplete = true;
            }
          }
        );
      } );

      waitsFor( function( ) {
        return retrieveSuccess;
      }, 'file retrieve attempt to complete', 1000 );

      runs( function( ) {
        expect( retrieveSuccess ).toBe( true );
        expect( fileResult instanceof File ).toBe( true );
      } );
    } );

    it( 'is deleted successfully', function( ) {
      var deleteResult = null,
        deleteComplete = null;

      runs( function( ) {
        fileManager.deleteFile(
          fileName, {
            success: function( ) {
              deleteResult = 'success';
              deleteComplete = true;
            },
            error: function( ) {
              deleteResult = 'error';
              deleteComplete = true;
            }
          }
        );
      } );

      waitsFor( function( ) {
        return deleteResult;
      }, 'file delete attempt to complete', 1000 );

      runs( function( ) {
        expect( deleteResult ).toEqual( 'success' );
        //expect( fileManager.getCurrentQuotaUsed( ) ).toEqual( quotaUsedStart );
      } );
    } );

    //test that when fileManager.saveFile() fails the instance does not get a value (whether event.stopPropagation works)
    //test that data-previous-file-name gets added after successful save
    //test that data-pervious-file-name gets updated to "" when field is cleared
    //test that file is deleted from file system if input is clear or a different file is selected
  } );
} );