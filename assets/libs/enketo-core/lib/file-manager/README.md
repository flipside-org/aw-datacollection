fileManager
===========

A library that helps with storing files, retrieving, removing files using the FileSystem API. The Filesytem API is not stable and neither is this library. Consider this therefore _HIGHLY EXPERIMENTAL_

###Background
Developed for use in [Enketo Smart Paper](https://enketo.org), but without external dependencies. It can be used in any situation requiring persistent folder-based file storage inside a browser.

###Features
* persistent storage of files in folders with a unique ID
* lightweight - only includes features that are required for enketo

###Support
* Chrome for Desktop
* Opera for Desktop

###Prerequisites
* jQuery

###Development
* styleguide used: https://github.com/rwaldron/idiomatic.js/
* tests can only be run from a webserver, need to accept filesystem access upon first run 
* for now JSDoc is used to be able to compile with Google Closure (but not yet in Advanced Mode)
* peephole Chrome plugin or chrome://settings/cookies is your friend

###Future
* Sample usage page
* Automated testing on Travis (with node.js webserver?)
* Would like to refactor this and use a workflow that emits events to more elegantly deal with all the chained asynchronous function calls
