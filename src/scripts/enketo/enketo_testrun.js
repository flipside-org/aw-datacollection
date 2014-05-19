// Enketo uses its own jquery, but for the toast
// we need the one used across the website.
// In this way we avoid conflicts.
$jQuery = $;
/**
 * Show a toast with defined message.
 */
function showToast(msg, type, sticky) {
  $jQuery().toastmessage('showToast', {
    sticky   : sticky,
    text     : msg,
    type     : type,
    inEffectDuration : 100,
    position : 'bottom-right',
  });
}

/**
 * Require Js configuration for enketo.
 * Coming from enketo core.
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
  $.get(Connection.URL_XSLT_TRANSFORM, function(response) {
    var xml_form = response['xml_form'];
    // Request numbers.
    // Enketo form stuff.
    // XML Parser.
    var parser = new DOMParser();
    var $data = parser.parseFromString(xml_form, 'text/xml');
    // Convert to jQuery object to allow find.
    $data = $($data);
    
    formStr = (new XMLSerializer() ).serializeToString($data.find( 'form:eq(0)' )[0]);
    modelStr = (new XMLSerializer() ).serializeToString($data.find( 'model:eq(0)' )[0]);
    
    // Insert form.
    $('#enketo-form').append(formStr);
    
    // Initialise form.
    initializeForm();
    
    // Show enketo form. The testrun is a variation of the data collection file.
    $('.step2').addClass('revealed'); 

    //validate handler for validate button
    $('#enketo-validate').on('click', function(e) {
      e.preventDefault();
      
      form.validate();
      if (!form.isValid()) {
        showToast('Form contains errors. Please see fields marked in red.', 'error', true);
      } else {
        showToast('The form was correctly filled but since this is a testrun, no data will be collected.', 'success', true);
      }
    });
  }, 'json');
  
  /**
   * Initialise the form.
   */
  function initializeForm() {
    // Initialise the form.
    form = new Form('form.or:eq(0)', modelStr);
    // For debugging.
    //window.form = form;
    // Initialise form and check for load errors.
    loadErrors = form.init();
    if (loadErrors.length > 0) {
      // TODO: Find out what kind of errors.
      console.log('loadErrors: ' + loadErrors.join(', '));
    }
  }
});