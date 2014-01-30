XPathJS_JavaRosa
=======

XPathJS_JavaRosa is fork of XPathJS a pure JavaScript implementation of [XPath 1.0](http://www.w3.org/TR/xpath/) and [DOM Level 3 XPath](http://www.w3.org/TR/DOM-Level-3-XPath/) specifications. 

This fork extends XPathJS with custom JavaRosa/OpenRosa functions and the 'date' datatype. 


Features
--------

  * Works in all major browsers: IE6+, Firefox, Chrome, Safari, Opera
  * Supports XML namespaces!
  * No external dependencies, include just a single .js file
  * Regression tested against [hundreds of unit test cases](http://projects.aidwebsolutions.com/xpathjs_javarosa/tests/).
  * Works in pages served as both, _text/html_ and _application/xhtml+xml_ content types.
  * The core is [benchmarked](http://www.pokret.org/xpathjs/benchmark/) against other XPath implementations.

Getting Started
--------

  1. Download [build/xpathjs_javarosa.min.js](https://raw.github.com/martijnr/xpathjs_javarosa/master/build/xpathjs_javarosa.min.js) file.
  
  2. Include xpathjs_javarosa.min.js in the \<head> of your HTML document.
     NOTE: Make sure HTML document is in strict mode i.e. it has a !DOCTYPE declaration at the top!
  
  3. Initialize XPathJS:
     
     ```javascript
    // bind XPath methods to document and window objects
    // NOTE: This will overwrite native XPath implementation if it exists
    XPathJS.bindDomLevel3XPath();
    ```
     
  4. You can now use XPath expressions to query the DOM:
     
     ```javascript
    var result = document.evaluate(
        '//ul/li/text()', // XPath expression
        document, // context node
        null, // namespace resolver
        XPathResult.ORDERED_NODE_SNAPSHOT_TYPE
    );
    
    // loop through results
    for (var i = 0; i < result.snapshotLength; i++) {
        var node = result.snapshotItem(i);
        alert(node.nodeValue);
    }
    ```

Take a look at some [working examples](http://www.pokret.org/xpathjs/examples/) to get a better idea of how to use XPathJS.

Note that JavaRosa [deviates from the XPath spec in a few ways](https://bitbucket.org/javarosa/javarosa/wiki/XFormDeviations). Since these deviations deal with core XPath 1.0 functions, and will eventually be rectified, no workarounds have been introduced in this fork to mimic the deviated behaviour. (Enketo includes a workaround for the absolute-paths-within-repeats issue that adjusts an expression before sending it to the XPath evaluator. This way it can easily be removed in the future and the Evaluator stays 'pure'.)

An exception are the ODK deviations for native XPath 1.0 function round() and concat(). These functions were replaced with custom javarosa versions as they do not break the original functionality of the native functions.

Take a look at the [**CAVEATS**](https://github.com/andrejpavlovic/xpathjs/blob/master/CAVEATS.md) document to get a better understanding of XPathJS limitations.


Background
--------

While developing [Enketo](http://blog.aidwebsolutions.com/tag/enketo/) - an offline-web application to conduct surveys in areas with problematic Internet connectivity using the JavaRosa/OpenRosa form format - the need arose for a client-side XPath Processor that could be easily extended. Andrej Pavlovic' excellent XPathJS project was chosen due to the very easily readable code and seemingly robust implementation (and indeed no bugs were found!!) of the DOM Level 3 XPath Processor.

I hope this project helps to promote the adoption of a platform with multiple data collection, entry, collation and analysis apps that use an open format and can work together.

Development
--------

  * [Source code](https://github.com/MartijnR/xpathjs_javarosa)
  * [Issue tracker](https://github.com/MartijnR/xpathjs_javarosa/issues)

XPathJS_javarosa fork is developed by [Martijn van de Rijdt](mailto:martijn@aidwebsolutions.com). You are more than welcome to contribute by [logging issues](https://github.com/MartijnR/xpathjs_javarosa/issues), [sending pull requests](http://help.github.com/send-pull-requests/), or [just giving feedback](mailto:martijn@aidwebsolutions.com).

License
--------

See the [XPathJS project](https://github.com/andrejpavlovic/xpathjs) for license information of XPathJS. This fork has the same license.


Build
--------

In order to build the code yourself, you will need the following tools:

  1. [Apache Ant](http://ant.apache.org/)
  2. [Node.js](http://nodejs.org/)
  3. [PEG.js](http://pegjs.majda.cz/) (_npm install --global pegjs_)


Copy/Clone the repository and run build.xml with ANT.
