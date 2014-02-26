/**
 * @file
 * Script to test login functionnalities.
 *
 * @author Nuno Veloso (nunoveloso18@gmail.com)
 */




/**
 * Initialise the variables
 */
if (!casper.cli.has("uri")) {
  casper
    .log('You must specify a base URL to run the tests.', 'error')
    .exit()
}

var uri = casper.cli.get("uri") + '/login'


/**
 * Test the login page.
 */
casper.test.begin('AW login', function suite(test) {
  casper.start(uri, function() {
    // Checking login form.
    casper.echo('Checking login form.', 'INFO')
    test_field_present(test, 'username', 'text')
    test_field_present(test, 'password', 'password')
    test_field_present(test, 'submit', 'submit')

    // Attempting login without credentials.
    casper.echo('Attempting login without credentials.', 'INFO')
    this.click('input[type="submit"]')
  });

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'username')
    test_field_error(test, 'password')

    // Attempting login without password.
    casper.echo('Attempting login without password.', 'INFO')
    this.fill('form#login-form', {
      'signin_username': 'admin',
    }, true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'password')

    // Attempting login without username.
    casper.echo('Attempting login without username.', 'INFO')
    this.fill('form#login-form', {
      'signin_password': 'admin',
    }, true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'username')

    // Attempting login with unknown username.
    casper.echo('Attempting login with unknown username.', 'INFO')
    this.fill('form#login-form', {
      'signin_username': 'i_am_casper',
      'signin_password': 'i_am_casper',
    }, true)
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'password')

    // Attempting login with known username but bad password.
    casper.echo('Attempting login with known username but bad password.', 'INFO')
    this.fill('form#login-form', {
      'signin_username': 'admin',
      'signin_password': 'wrong_password',
    }, true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'password')

    // Attempting login with correct credentials.
    casper.echo('Attempting login with correct credentials.', 'INFO')
    this.fill('form#login-form', {
      'signin_username': 'admin',
      'signin_password': 'admin',
    }, true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    test.assertExists('a#logout-button', "log in successful: logout button was found")
  })


  casper.run(function() {
      test.done()
  })


  /**
   * Test if we are still on login page.
   * @param [object] test
   */
  function test_url_is_login(test) {
    test.assertUrlMatch(/login$/, "Checking if we are still on login page.")
  }


  /**
   * Test if we are still on login page.
   * @param [object] test
   * @param [string] field
   */
  function test_field_error(test, field) {
    test.assertExists('div.error[data-ref="signin_' + field + '"]',
      "Found " + field + " error: " + casper.getHTML('div.error[data-ref="signin_' + field + '"]'))
  }


  /**
   * Test if we are still on login page.
   * @param [object] test
   * @param [string] name
   * @param [string] type
   */
  function test_field_present(test, name, type) {
    test.assertExists('input[type="' + type + '"][name="signin_' + name + '"]',
      "Found " + name + " field")
  }

})


