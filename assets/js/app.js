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
  
  var queue;

  $.get(Aw.settings.xslt_transform_path, function(response) {
    // Parse response
    response = JSON.parse(response);
    
    // Set number to call.
    queue = new resp_queue(response.respondents);
    
    // Enketo form stuff
    var $data;
    $data = $($.parseXML(response.form));
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
      form.resetView();
      initializeForm();
    }
  });

  //initialize the form

  function initializeForm() {
    var r = queue.get_next_resp();
    $('#totals').text(queue.get_current() + ' of ' + queue.get_total());
    if (r == false) {
      alert('Numbers exhausted.');
      return;
    }
    $('#respondent_number').text(r.number);
    
    
    form = new Form('form.or:eq(0)', modelStr);
    //for debugging
    window.form = form;
    //initialize form and check for load errors
    loadErrors = form.init();
    if (loadErrors.length > 0) {
      alert('loadErrors: ' + loadErrors.join(', '));
    }
  }

});




// Objects
var resp_queue = function(queue) {
  this.respondents = queue;
  this.current = 0;
  
  this.get_next_resp = function() {
    if (typeof this.respondents[this.current] == 'undefined') {
      return false;
    }
    else {
      var resp = this.respondents[this.current];
      this.current++;
      return resp;
    }
  };
  
  this.get_total = function() {
    return this.respondents.length;
  };
  
  this.get_current = function() {
    return this.current;
  };
};
