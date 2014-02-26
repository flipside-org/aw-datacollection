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
 * Initialise user object
 */
function User(username, password) {
    this.username = username
    this.password = password

    this.fill_login = function() {
      return {
        'signin_username': this.username,
        'signin_password': this.password,
      }
    }
}

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
    var user = new User('admin', '')
    this.fill('form#login-form', user.fill_login(), true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'password')

    // Attempting login without username.
    casper.echo('Attempting login without username.', 'INFO')
    var user = new User('', 'password')
    this.fill('form#login-form', user.fill_login(), true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'username')

    // Attempting login with unknown username.
    casper.echo('Attempting login with unknown username.', 'INFO')
    var user = new User('i_am_casper_username', 'i_am_casper_password')
    this.fill('form#login-form', user.fill_login(), true)
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'password')

    // Attempting login with known username but bad password.
    casper.echo('Attempting login with known username but bad password.', 'INFO')
    var user = new User('admin', 'wrong_password')
    this.fill('form#login-form', user.fill_login(), true)
    this.click('input[type="submit"]')
  })

  casper.then(function() {
    // test errors
    test_url_is_login(test)
    test_field_error(test, 'password')

    // Attempting login with correct credentials.
    casper.echo('Attempting login with correct credentials.', 'INFO')
    var user = new User('admin', 'admin')
    this.fill('form#login-form', user.fill_login(), true)
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


