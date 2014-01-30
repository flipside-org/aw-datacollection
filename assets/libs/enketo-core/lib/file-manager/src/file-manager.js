/**
 * This library copies liberally from http://www.html5rocks.com/en/tutorials/file/filesystem/#toc-filesystemurls
 * by Eric Bidelman. Thanks a lot, Eric!
 *
 * Copyright 2013 Martijn van de Rijdt & Modi Labs
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * FileManager Class for PERSISTENT file storage, used to storage file inputs for later submission
 * Could be expanded, if ever needed, to use TEMPORARY file storage as well
 *
 * @constructor
 */

define( [ 'jquery' ], function( $ ) {
  "use strict";

  var getCurrentQuota, getCurrentQuotaUsed, supported, isSupported, fs, init, setDir, requestQuota,
    requestFileSystem, errorHandler, setCurrentQuotaUsed, traverseAll, saveFile, retrieveFile,
    retrieveFileEntry, retrieveFileFromFileEntry, deleteFile, createDir, deleteDir, currentDir,
    deleteAll, getDirPrefix, listAll,
    currentQuota = null,
    currentQuotaUsed = null,
    DEFAULTBYTESREQUESTED = 100 * 1024 * 1024;

  console.log( 'in file manager' );

  getCurrentQuota = function( ) {
    return currentQuota;
  };
  getCurrentQuotaUsed = function( ) {
    return currentQuotaUsed;
  };
  //function getFS ( ) {
  //  return fs;
  //};

  /**
   * Initializes the File Manager
   * @return {boolean} returns true/false if File API is supported by browser
   */
  init = function( ) {
    //check if filesystem API is supported by browser
    window.requestFileSystem = window.requestFileSystem || window.webkitRequestFileSystem;
    window.resolveLocalFileSystemURL = window.resolveLocalFileSystemURL || window.webkitResolveLocalFileSystemURL;
    supported = ( typeof window.requestFileSystem !== 'undefined' && typeof window.resolveLocalFileSystemURL !== 'undefined' && typeof navigator.webkitPersistentStorage !== 'undefined' );
    console.log( 'supported: ', supported );
    if ( supported ) {
      var that = this;
      setCurrentQuotaUsed( );
      requestQuota(
        DEFAULTBYTESREQUESTED, {
          success: function( grantedBytes ) {
            requestFileSystem(
              grantedBytes, {
                success: function( fsys ) {
                  fs = fsys;
                  console.log( 'got filesystem', fs );
                  $( document ).trigger( 'filesystemready' );
                },
                error: function( e ) {
                  errorHandler( e );
                }
              }
            );
          },
          error: errorHandler
        }
      );
      return true;
    } else {
      console.log( 'filesystem API not supported in this browser' );
      return false;
    }
  };

  /**
   * @param  {string}                             dirName name of directory to store files in
   * @param  {{success:Function, error:Function}} callbacks callback functions (error, and success)
   */
  setDir = function( dirName, callbacks ) {
    var currentSuccessCb = callbacks.success,
      that = this;
    if ( dirName && dirName.length > 0 ) {
      callbacks.success = function( dirEntry ) {
        currentDir = dirName;
        currentSuccessCb( );
      };
      if ( typeof fs == "undefined" ) {
        console.log( 'fs not yet defined, waiting for filesystemready event' );
        $( document ).on( 'filesystemready', function( ) {
          that.createDir( dirName, callbacks );
        } );
      } else {
        this.createDir( dirName, callbacks );
      }
    } else {
      console.error( 'directory name empty or missing' );
      return false;
    }
  };

  /**
   * Checks if File API is supported by browser
   * @return {boolean}
   */
  isSupported = function( ) {
    return supported;
  };

  /**
   * returns dir prefix to be use to build a filesystem path
   * @param  {string=} dirName the dirName to use if provided, otherwise the current directory name is used
   * @return {string} returns the path prefix or '/' (root)
   */
  getDirPrefix = function( dirName ) {
    return ( dirName ) ? '/' + dirName + '/' : ( currentDir ) ? '/' + currentDir + '/' : '/';
  };

  /**
   * Requests PERSISTENT file storage (may prompt user) asynchronously
   * @param  {number}                     bytesRequested the storage size in bytes that is requested
   * @param  {Object.<string, Function>}  callbacks      callback functions (error, and success)
   */
  requestQuota = function( bytesRequested, callbacks ) {
    console.log( 'requesting persistent filesystem quota' );
    $( document ).trigger( 'quotarequest', bytesRequested ); //just to facilitate testing
    navigator.webkitPersistentStorage.requestQuota(
      bytesRequested,
      //successhandler is called immediately if asking for increase in quota
      callbacks.success,
      callbacks.error
    );
  };

  /**
   * requests filesystem
   * @param  {number} bytes       when called by requestQuota for PERSISTENt storage this is the number of bytes granted
   * @param  {Object.<string, Function>} callbacks   callback functions (error, and success)
   */
  requestFileSystem = function( bytes, callbacks ) {
    console.log( 'quota for persistent storage granted in MegaBytes: ' + bytes / ( 1024 * 1024 ) );
    if ( bytes > 0 ) {
      currentQuota = bytes;
      window.requestFileSystem(
        window.PERSISTENT,
        bytes,
        callbacks.success,
        callbacks.error
      );
    } else {
      //actually not correct to treat this as an error
      console.error( 'User did not approve storage of local data using the File API' );
      callbacks.error( );
    }
  };

  /**
   * generic error handler
   * @param  {(Error|FileError|string)=} e [description]
   */
  errorHandler = function( e ) {
    var msg = '';

    if ( typeof e !== 'undefined' ) {
      switch ( e.code ) {
        case window.FileError.QUOTA_EXCEEDED_ERR:
          msg = 'QUOTA_EXCEEDED_ERR';
          break;
        case window.FileError.NOT_FOUND_ERR:
          msg = 'NOT_FOUND_ERR';
          break;
        case window.FileError.SECURITY_ERR:
          msg = 'SECURITY_ERR';
          break;
        case window.FileError.INVALID_MODIFICATION_ERR:
          msg = 'INVALID_MODIFICATION_ERR';
          break;
        case window.FileError.INVALID_STATE_ERR:
          msg = 'INVALID_STATE_ERR';
          break;
        default:
          msg = 'Unknown Error';
          break;
      }
    }
    console.log( 'Error occurred: ' + msg );
    if ( typeof console.trace !== 'undefined' ) console.trace( );
  };

  /**
   * Requests the amount of storage used (asynchronously) and sets variable (EXPERIMENTAL/UNSTABLE API)
   */
  setCurrentQuotaUsed = function( ) {
    if ( typeof navigator.webkitPersistentStorage.queryUsageAndQuota !== 'undefined' ) {
      navigator.webkitPersistentStorage.queryUsageAndQuota(
        function( quotaUsed ) {
          currentQuotaUsed = quotaUsed;
        },
        errorHandler
      );
    } else {
      console.error( 'browser does not support queryUsageAndQuota' );
    }
  };

  /**
   * Saves a file (asynchronously) in the directory provided upon initialization
   * @param  {Blob}                       file      File object from input field
   * @param  {Object.<string, Function>}  callbacks callback functions (error, and success)
   */
  saveFile = function( file, callbacks ) {
    var that = this;

    if ( typeof fs == "undefined" ) {
      $( document ).on( 'filesystemready', function( ) {
        saveThisFile( );
      } );
    } else {
      saveThisFile( );
    }

    function saveThisFile( ) {
      console.log( 'saving file with url: ', getDirPrefix( ) + file.name );
      fs.root.getFile(
        getDirPrefix( ) + file.name, {
          create: true,
          exclusive: false
        },
        function( fileEntry ) {
          fileEntry.createWriter( function( fileWriter ) {
            fileWriter.write( file );
            fileWriter.onwriteend = function( e ) {
              //if a file write does not complete because the file is larger than the granted quota
              //the onwriteend event still fires. (This may be a browser bug.)
              //so we're checking if the complete file was saved and if not, do nothing and assume
              //that the onerror event will fire
              if ( e.total === e.loaded ) {
                //console.log('write completed', e);
                setCurrentQuotaUsed( );
                console.log( 'complete file stored, with persistent url:' + fileEntry.toURL( ) );
                callbacks.success( fileEntry.toURL( ) );
              }
            };
            fileWriter.onerror = function( e ) {
              var newBytesRequest,
                targetError = e.target.error;
              if ( targetError instanceof FileError && targetError.code === window.FileError.QUOTA_EXCEEDED_ERR ) {
                newBytesRequest = ( ( e.total * 5 ) < DEFAULTBYTESREQUESTED ) ? currentQuota + DEFAULTBYTESREQUESTED : currentQuota + ( 5 * e.total );
                console.log( 'Required storage exceeding quota, going to request more, in bytes: ' + newBytesRequest );
                requestQuota(
                  newBytesRequest, {
                    success: function( bytes ) {
                      console.log( 'request for additional quota approved! (quota: ' + bytes + ' bytes)' );
                      currentQuota = bytes;
                      that.saveFile( file, callbacks );
                    },
                    error: callbacks.error
                  }
                );
              } else {
                callbacks.error( e );
              }
            };
          }, callbacks.error );
        },
        callbacks.error
      );
    }
  };

  /**
   * Obtains specified files from a specified directory (asynchronously)
   * @param {string}                              directoryName   directory to look in for files
   * @param {{newName: string, fileName: string}} fileO           object of file properties
   * @param {{success:Function, error:Function}}  callbacks       callback functions (error, and success)
   */
  retrieveFile = function( directoryName, fileO, callbacks ) {
    //check if filesystem is ready??
    var retrievedFile = {},
      pathPrefix = getDirPrefix( directoryName ),
      callbacksForFileEntry = {
        success: function( fileEntry ) {
          retrieveFileFromFileEntry( fileEntry, {
            success: function( file ) {
              console.debug( 'retrieved file! ', file );
              fileO.file = file;
              callbacks.success( fileO );
            },
            error: callbacks.error
          } );
        },
        error: callbacks.error
      };

    retrieveFileEntry( pathPrefix + fileO.fileName, {
      success: callbacksForFileEntry.success,
      error: callbacksForFileEntry.error
    } );
  };

  /**
   * Obtains a fileEntry (asynchronously)
   * @param  {string}                             fullPath    full filesystem path to the file
   * @param {{success:Function, error:Function}}  callbacks   callback functions (error, and success)
   */
  retrieveFileEntry = function( fullPath, callbacks ) {
    console.debug( 'retrieving fileEntry for: ' + fullPath );
    fs.root.getFile( fullPath, {},
      function( fileEntry ) {
        console.log( 'fileEntry retrieved: ', fileEntry, 'persistent URL: ', fileEntry.toURL( ) );
        callbacks.success( fileEntry );
      },
      function( e ) {
        console.error( 'file with path: ' + fullPath + ' not found', e );
        callbacks.error( e );
      }
    );
  };

  /**
   * Retrieves a file from a fileEntry (asynchronously)
   * @param  {FileEntry} fileEntry [description]
   * @param  {{success:function(File), error: ?function(FileError)}} callbacks [description]
   */
  retrieveFileFromFileEntry = function( fileEntry, callbacks ) {
    fileEntry.file( callbacks.success, callbacks.error );
  };

  /**
   * Deletes a file from the file system (asynchronously) from the directory set upon initialization
   * @param {string}                              fileName        file name
   * @param {{success:Function, error:Function}}  callbacks       callback functions (error, and success)
   */
  deleteFile = function( fileName, callbacks ) {
    //check if filesystem is ready?
    //console.log('amount of storage used: '+this.getStorageUsed());
    console.log( 'deleting file: ' + fileName );
    callbacks = callbacks || {
      success: function( ) {},
      error: function( ) {}
    };
    //console.log('amount of storage used: '+this.getStorageUsed());
    fs.root.getFile( getDirPrefix( ) + fileName, {
        create: false
      },
      function( fileEntry ) {
        fileEntry.remove( function( ) {
          setCurrentQuotaUsed( );
          console.log( fileName + ' removed from file system' );
          callbacks.success( );
        } );
      },
      function( e ) {
        errorHandler( e );
        callbacks.error( );
      }
    );
  };

  /**
   * Creates a directory
   * @param  {string}                                 name      name of directory
   * @param  {{success: Function, error: Function}}   callbacks callback functions (error, and success)
   */
  createDir = function( name, callbacks ) {
    var that = this;

    callbacks = callbacks || {
      success: function( ) {},
      error: function( ) {}
    };
    fs.root.getDirectory( name, {
        create: true
      },
      function( dirEntry ) {
        setCurrentQuotaUsed( );
        console.log( 'Directory: ' + name + ' created (or found)', dirEntry );
        callbacks.success( );
      },
      function( e ) {
        console.log( 'error during creation of directory', e );
        var newBytesRequest; //,
        if ( e instanceof FileError && e.code === window.FileError.QUOTA_EXCEEDED_ERR ) {
          console.log( 'Required storage exceeding quota, going to request more.' );
          newBytesRequest = ( ( e.total * 5 ) < DEFAULTBYTESREQUESTED ) ? currentQuota + DEFAULTBYTESREQUESTED : currentQuota + ( 5 * e.total );
          requestQuota(
            newBytesRequest, {
              success: function( bytes ) {
                currentQuota = bytes;
                that.createDir( name, callbacks );
              },
              error: callbacks.error
            }
          );
        } else {
          callbacks.error( e );
        }
      }
      //TODO: ADD similar request for additional storage if FileError.QUOTA_EXCEEEDED_ERR is thrown as done in saveFile()
    );
  };

  /**
   * Deletes a complete directory with all its contents
   * @param {string}                                  name        name of directory
   * @param {{success: Function, error: Function}}    callbacks   callback functions (error, and success)
   */
  deleteDir = function( name, callbacks ) {
    //check if filesystem is ready?
    callbacks = callbacks || {
      success: function( ) {},
      error: function( ) {}
    };
    console.log( 'going to delete directory: ' + name );
    fs.root.getDirectory( name, {},
      function( dirEntry ) {
        dirEntry.removeRecursively(
          function( ) {
            setCurrentQuotaUsed( );
            callbacks.success( );
          },
          function( e ) {
            errorHandler( e );
            callbacks.error( );
          }
        );
      },
      errorHandler
    );
  };

  /**
   * Deletes all files stored (for a subsubdomain)
   * @param {Function=} callbackComplete  function to call when complete
   */
  deleteAll = function( callbackComplete ) {
    callbackComplete = callbackComplete || function( ) {};

    var process = {
      entryFound: function( entry ) {
        if ( entry.isDirectory ) {
          entry.removeRecursively(
            function( ) {
              setCurrentQuotaUsed( );
              console.log( 'Directory: ' + entry.name + ' deleted' );
            },
            errorHandler
          );
        } else {
          entry.remove( function( ) {
              setCurrentQuotaUsed( );
              console.log( 'File: ' + entry.name + ' deleted' );
            },
            errorHandler
          );
        }
      },
      complete: callbackComplete
    };

    //check if filesystem is ready
    if ( typeof fs == "undefined" ) {
      $( document ).on( 'filesystemready', function( ) {
        traverseAll( process );
      } );
    } else {
      traverseAll( process );
    }
  };

  /**
   * Lists all files/folders in root (function may not be required)
   * @param {Function=} callbackComplete  function to call when complete
   */
  listAll = function( callbackComplete ) {
    //check if filesystem is ready?
    callbackComplete = callbackComplete || function( ) {};
    var entries = [ ],
      process = {
        entryFound: function( entry ) {
          if ( entry.isDirectory ) {
            entries.push( 'folder: ' + entry.name );
          } else {
            entries.push( 'file: ' + entry.name );
          }
        },
        complete: function( ) {
          console.log( 'entries: ', entries );
          callbackComplete( );
        }
      };
    traverseAll( process );
  };

  /**
   * traverses all folders and files in root
   * @param  {{entryFound: Function, complete}} process [description]
   */
  traverseAll = function( process ) {
    var entry, type,
      dirReader = fs.root.createReader( );

    // Call the reader.readEntries() until no more results are returned.
    var readEntries = function( ) {
      dirReader.readEntries( function( results ) {
        if ( !results.length ) {
          process.complete( );
        } else {
          for ( var i = 0; i < results.length; i++ ) {
            entry = results[ i ];
            process.entryFound( entry );
          }
          readEntries( );
        }
      }, errorHandler );
    };
    readEntries( );
  };

  init( );

  return {
    init: init,
    isSupported: isSupported,
    setDir: setDir,
    getCurrentQuota: getCurrentQuota,
    getCurrentQuotaUsed: getCurrentQuotaUsed,
    saveFile: saveFile,
    retrieveFile: retrieveFile,
    deleteFile: deleteFile,
    createDir: createDir,
    deleteDir: deleteDir,
    deleteAll: deleteAll,
    listAll: listAll
  };

} );