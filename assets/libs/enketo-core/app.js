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

requirejs(['jquery', 'Modernizr', 'enketo-js/Form'], function($, Modernizr, Form) {
  var loadErrors, form;

  //check if HTML form is hardcoded or needs to be retrieved
  $.get(Aw.settings.xslt_transform_path, function(data) {    
    var $data;
    //this replacement should move to XSLT after which the GET can just return 'xml' and $data = $(data)
    data = data.replace(/jr\:template=/gi, 'template=');
    $data = $($.parseXML(data));
    formStr = (new XMLSerializer() ).serializeToString($data.find( 'form:eq(0)' )[0]);
    modelStr = (new XMLSerializer() ).serializeToString($data.find( 'model:eq(0)' )[0]);
    
    $('#validate-form').before(formStr);
    
    initializeForm();
    
  }, 'text');

  //validate handler for validate button
  $('#validate-form').on('click', function() {
    form.validate();
    if (!form.isValid()) {
      alert('Form contains errors. Please see fields marked in red.');
    } else {
      alert('Form is correctly filled. However, since this is a test run, no data will be collected.');
      //alert('Form is valid! (see XML record in the console)');
      console.log('record:', form.getDataStr());
    }
  });

  //initialize the form

  function initializeForm() {
    var edit_str = '<survey_1_xls id="survey_1_xls"> <name>Daniel</name> <age>22</age> <gender>male</gender> <photo/> <date/> <location/> <pizza_fan>no</pizza_fan> <pizza_hater/> <pizza_type/> <favorite_toppings/> <thanks/> <start_time>2014-01-30T12:33:24.000-00:00</start_time> <end_time>2014-01-30T12:33:24.000-00:00</end_time> <today>2014-01-30</today> <imei>no device properties in enketo</imei> <phonenumber>no device properties in enketo</phonenumber> <meta> <instanceID>uuid:241b2643-531b-46f2-aceb-cc09f68b9c82</instanceID> </meta> </survey_1_xls>';
    
    
    form = new Form('form.or:eq(0)', modelStr, edit_str);
    //for debugging
    window.form = form;
    //initialize form and check for load errors
    loadErrors = form.init();
    if (loadErrors.length > 0) {
      alert('loadErrors: ' + loadErrors.join(', '));
    }
  }

});
