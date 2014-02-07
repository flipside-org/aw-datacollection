/**
 * Require Js configuration for enketo.
 * Coming from enketo core.
 * TODO: baseUrl should come from server.
 */
requirejs.config({
  baseUrl : Aw.settings.base_url + "assets/libs/enketo-core/lib",
  paths : {
    "enketo-js" : "../src/js",
    "enketo-widget" : "../src/widget",
    "enketo-config" : "../config.json",
    "text" : "text/text",
    "xpath" : "xpath/build/xpathjs_javarosa",
    "file-manager" : "file-manager/src/file-manager",
    "jquery.xpath" : "jquery-xpath/jquery.xpath",
    "jquery.touchswipe" : "jquery-touchswipe/jquery.touchSwipe"
  },
  shim : {
    "xpath" : {
      exports : "XPathJS"
    },
    "bootstrap" : {
      deps : ["jquery"],
      exports : "jQuery.fn.popover"
    },
    "widget/date/bootstrap3-datepicker/js/bootstrap-datepicker" : {
      deps : ["jquery"],
      exports : "jQuery.fn.datepicker"
    },
    "widget/time/bootstrap3-timepicker/js/bootstrap-timepicker" : {
      deps : ["jquery"],
      exports : "jQuery.fn.timepicker"
    },
    "Modernizr" : {
      exports : "Modernizr"
    }
  }
});
// Require needed scripts and start everything.
requirejs(['jquery', 'Modernizr', 'enketo-js/Form'], function($, Modernizr, Form) {
  // Errors when loading the form.
  var loadErrors, form;
  
  // Perform request for form. Form will be returned in xml.
  $.get(Connection.URL_XSLT_TRANSFORM, function(xml_form) {
    // Request numbers.
    // Enketo form stuff.
    var $data = $(xml_form);
    formStr = (new XMLSerializer() ).serializeToString($data.find( 'form:eq(0)' )[0]);
    modelStr = (new XMLSerializer() ).serializeToString($data.find( 'model:eq(0)' )[0]);
    
    // Insert form.
    $('#validate-form').before(formStr);
    
    // Initialize form.
    initializeForm();

    //validate handler for validate button
    $('#validate-form').on('click', function() {
      form.validate();
      if (!form.isValid()) {
        alert('Form contains errors. Please see fields marked in red.');
      } else {
        alert('The form was correctly filled but since this is a test run, no data will be collected.');
      }
    });      
  }, 'xml');
  
  /**
   * Initialize the form.
   */
  function initializeForm() {    
    // Initialize the form.
    form = new Form('form.or:eq(0)', modelStr);
    // For debugging.
    window.form = form;
    // Initialize form and check for load errors.
    loadErrors = form.init();
    if (loadErrors.length > 0) {
      // TODO: Find out what kind of errors.
      alert('loadErrors: ' + loadErrors.join(', '));
    }
  }
});