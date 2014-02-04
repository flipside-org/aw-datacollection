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
  // Respondent queue.
  var resp_queue;
  var submission_queue = new SubmissionQueue();
  var current_respondent;
  // Perform request for form and data.
  $.get(Aw.settings.xslt_transform_path, function(xml_form) {
    RespondentQueue.requestNumbers(function(respondents) {
      // Set number to call.
      resp_queue = RespondentQueue.prepareQueue(respondents);
      
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
          alert('Form is correctly filled. However, since this is a test run, no data will be collected.');
          //alert('Form is valid! (see XML record in the console)');
          current_respondent.form_data = form.getDataStr();
          submission_queue.add(current_respondent);
          
          form.resetView();
          initializeForm();
        }
      });
      
    });
  }, 'xml');
  
  /**
   * Initialize the form.
   */
  function initializeForm() {
    // Get the next respondent.
    current_respondent = resp_queue.getNextResp();
    
    // Check if there are more respondents.
    if (current_respondent == false) {
      // TODO: Do something when there are no more numbers.
      alert('Numbers exhausted.');
      return;
    }
    
    // Setting data. Temporary.
    // TODO: Remove.
    $('#totals').text(resp_queue.getCurrentCount() + ' of ' + resp_queue.getTotal());
    $('#respondent_number').text(current_respondent.number);
    
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



  //******************************************/
  // Event listeners
  // EVENT submission_queue_change
  $(window).on('submission_queue_change', function() {
    console.log('Queue changed.');
    RespondentQueue.requestNumbers(function(respondents) { 
      // Set number to call.
      resp_queue = RespondentQueue.prepareQueue(respondents);
    });
    
    var $container = $('#queue');
    $container.html('');
    for (var i in submission_queue.queue) {
      $container.append($('<div>').text(submission_queue.queue[i].number));
    }
  });
  // End Event
  
  // EVENT submission_queue_change
  $(window).on('online_status_change', function() {
    if (submission_queue.online){
      submission_queue.submit();
    }
  });
  // End Event

});


// Objects
var RespondentQueue = function(queue) {
  this.respondents = queue;
  this.current = 0;
};

RespondentQueue.prepareQueue = function(respondent_queue) {
  if (SubmissionQueue.supportsLocalStorage() == false) {
    alert('You need a modern browser.');
    return null;
  }
  
  var stored_data;
  // Get stored data and convert it to JSON.
  try {
    stored_data = JSON.parse(localStorage.getItem('aw_submission_queue'));
  }
  catch(e) {
    stored_data = [];
  }
  
  // Remove from respondent_queue the respondents that are already
  // scheduled for submission.
  if ($.isArray(stored_data) && stored_data.length > 0) {
    
    var filtered = $(respondent_queue).filter(function() {
      for (var i in stored_data) {
        if (this.number == stored_data[i].number) {
          return false;
        }
      }
      return true;
    });
    
    // Assign filtered.
    respondent_queue = filtered;    
  }
  
  return new RespondentQueue(respondent_queue);
};

RespondentQueue.requestNumbers = function(callback) {
  $.get(Aw.settings.base_url + 'survey/survey_request_numbers/1', function(response) {
    // Set CSRF
    SubmissionQueue.csrf = response.csrf;
    callback(response.respondents);
  }, 'json');
};
 
RespondentQueue.prototype.getNextResp = function() {
  if (typeof this.respondents[this.current] == 'undefined') {
    return false;
  }
  else {
    var resp = this.respondents[this.current];
    this.current++;
    return resp;
  }
};

RespondentQueue.prototype.getTotal = function() {
  return this.respondents.length;
};

RespondentQueue.prototype.getCurrentCount = function() {
  return this.current;
};


















var SubmissionQueue = function() {
  this.connection_check_interval = 15 * 1000;
  this.csrf;
  this.queue = [];
  
  this.online = false;
  this.is_uploading = false;
  var self = this;
  
  if (SubmissionQueue.supportsLocalStorage() == false) {
    alert('You need a modern browser.');
    return null;
  }
  
  this.init();    
};

SubmissionQueue.supportsLocalStorage = function() {
  try {
    return 'localStorage' in window && window['localStorage'] !== null;
  } catch (e) {
    return false;
  }
};

SubmissionQueue.prototype.init = function() {
  console.log('Initializing Submission queue');
  var self = this;  
  
  var data = localStorage.getItem('aw_submission_queue');
  if (data != null) {
    try{
      var parsed = JSON.parse(data);
      if ($.isArray(parsed) && parsed.length > 0){
        this.queue = parsed;
        $(window).trigger('submission_queue_change');
      }
    }
    catch(e) {
      // Something went wrong with the parsing.
      // Probably the data is not correctly stored.
      // Init as empty.
      this.queue = [];
    }
  }
  
  this.checkConnection();
  
  // Interval to check for connection.
  window.setInterval( function() {
    self.checkConnection();
  }, this.connection_check_interval );
  
};

SubmissionQueue.prototype.add = function(value) {
  console.log('Adding value to submission queue.');
  this.queue.push(value);
  this.store();
  $(window).trigger('submission_queue_change');
  this.submit();
};

SubmissionQueue.prototype.store = function(value) {
  console.log('Storing on localStorage.');
  var to_store = JSON.stringify(this.queue);
  localStorage.setItem('aw_submission_queue', to_store);
};

SubmissionQueue.prototype.checkConnection = function() {
  var self = this;
  // navigator.onLine is totally unreliable (returns incorrect trues) on Firefox, Chrome, Safari (on OS X 10.8),
  // but I assume falses are correct
  if ( navigator.onLine ) {
    // When uploading do not check connection.
    if (!this.is_uploading) {
      $.ajax({
        type: 'GET',
        url: Aw.settings.check_connection,
        cache: false,
        dataType: 'json',
        timeout: 3000,
        complete: function( response ) {
          // Important to check for the content of the no-cache response as it will
          // start receiving the fallback page specified in the manifest!
          var online = typeof response.responseText !== 'undefined' && response.responseText === 'connected';
          self.setOnlineStatus( online );
        }
      });
    }
  } else {
    self.setOnlineStatus( false );
  }
};

SubmissionQueue.prototype.setOnlineStatus = function(newStatus) {
  console.log('Is online: ' + newStatus);
  if (newStatus != this.online) {
    this.online = newStatus;
    $(window).trigger('online_status_change');
  }
};

SubmissionQueue.prototype.submit = function() {//return;
  var self = this;
  self.checkConnection();
  if (self.online && !self.is_uploading && self.queue.length > 0) {
    self.is_uploading = true;
    
    var to_submit = self.queue[0];
    
    //MOCK
    if (Math.random() < 0.5) {
      // TODO: Always get the csrf back.
      $.post(Aw.settings.base_url + 'survey/survey_submit_enketo_form', {
        csrf_aw_datacollection : SubmissionQueue.csrf,
        respondent: to_submit
      }, function(r) {
        console.log(r);
      })
      
      // success
      console.log('Submit success');
      // The operation succeeded.
      // Remove the respondent and trigger change.
      self.queue.shift();
      self.store();
      $(window).trigger('submission_queue_change');
    }
    else {
      // error
      console.log('Submit error');
    }
    
    self.is_uploading = false;
    
    setTimeout(function(){self.submit();}, 2000);
  }
};

