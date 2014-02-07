/**
 * Connection
 * Responsible to asses the status of the connection. After initialization
 * it will periodically check for connectivity and trigger a 
 * connection_status_change event every time there's a change.
 */
Connection = function() {
  this.connection_check_interval = 15 * 1000;
  this.csrf_token = null;
  this.online = false;
  
  this.initialized = false;
  
  this.init();
};

// Add some static variables.
// TODO: Urls should be in Aw.settings.url.
$.extend(Connection, {
  /**
   * Url to periodically query to check for connectivity.
   */
  URL_CHECK_CONNECTION     : Aw.settings.check_connection,
  /**
   * Url to request a CSRF token if the current one is void.
   */
  URL_REQUEST_CSRF         : Aw.settings.base_url + 'survey/survey_request_csrf_token/',
  /**
   * Url to get the survey form.
   */
  URL_XSLT_TRANSFORM       : Aw.settings.xslt_transform_path,
  /**
   * Url to request respondents.
   */
  URL_REQUEST_RESPONDENTS  : Aw.settings.base_url + 'survey/survey_request_numbers/1',
  /**
   * Url to where the form must be submitted.
   */
  URL_FORM_SUBMIT          : Aw.settings.base_url + 'survey/survey_submit_enketo_form',
});

/**
 * Initializes the connection setting up the checking interval.
 */
Connection.prototype.init = function() {
  var self = this;
  // Only initialize once.
  if (self.initialized) { return; }
  self.initialized = true;
  
  // Check for the first time.
  self.checkConnection();
  
  // Interval to check for connection.
  window.setInterval( function() {
    self.checkConnection();
  }, self.connection_check_interval );
};

/**
 * Checks if there's connectivity with the server. 
 */
Connection.prototype.checkConnection = function() {
  var self = this;
  // As found in enketo.
  // navigator.onLine is totally unreliable (returns incorrect trues)
  // on Firefox, Chrome, Safari (on OS X 10.8), but we assume
  // falses are correct.
  if ( navigator.onLine ) {
      $.ajax({
        type: 'GET',
        url: Connection.URL_CHECK_CONNECTION,
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
  } else {
    self.setOnlineStatus( false );
  }
};

/**
 * Sets the status of the connection if it changed.
 * 
 * Trigger Event connection_status_change
 *  When the status change.
 * 
 * @param {boolean} newStatus
 */
Connection.prototype.setOnlineStatus = function(newStatus) {
  var self = this;
  console.log('Is online: ' + newStatus);
  if (newStatus != self.online) {
    self.online = newStatus;
    $(window).trigger('connection_status_change', self);
  }
};

/**
 * Returns whether there's connectivity or not.
 * 
 * @returns {boolean}
 */
Connection.prototype.isOnline = function() {
  return this.online;
};

/**
 * Requests new CSRF token
 * Executes callback function if the request is successful.
 * 
 * @param {function} callback
 */
Connection.prototype.requestCSRF = function(callback) {
  var self = this;
  console.log('Requesting CSRF token. Current: ' + self.csrf_token);
  $.get(Connection.URL_REQUEST_CSRF, function(response) {
    self.csrf_token = response.csrf;
    console.log('New CSRF Token: ' + self.csrf_token);
    callback();
  }, 'json');
};

/**
 * Invalidates the current CSRF token.
 * 
 * @return {Connection} this
 *   To allow chaining.
 */
Connection.prototype.invalidateCSRF = function() {
  this.csrf_token = null;
  return this;
};

/**
 * Returns the current CSRF token.
 * 
 * @return {string}
 */
Connection.prototype.getCSRF = function() {
  return this.csrf_token;
};

/**
 * Checks whether the localStorage is available.
 * @static
 * @return {boolean}
 */
Connection.supportsLocalStorage = function() {
  try {
    return 'localStorage' in window && window['localStorage'] !== null;
  } catch (e) {
    return false;
  }
};