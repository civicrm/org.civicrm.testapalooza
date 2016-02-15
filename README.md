# CiviCRM Testapalooza

This project demonstrates of how to write tests for CiviCRM.  The techniques here are pretty
generic -- they ought to work with different package types (CiviCRM core, CiviCRM extensions,
Drupal modules, WordPress plugins) and different test-runners (e.g.  `phpunit`, `codeception`,
`protractor`, `behat`).

Testing CiviCRM is trickier than testing a basic library -- tests may involve system services (from
Civi or the CMS), and CiviCRM developers may use different CMS's, file structures, and URLs.  This
problem can be mitigated by creating more configuration files for each extension/test-suite/installation, but
that grows unwieldy with multiple extensions.

To resolve this, we use the helper command, [`cv`](https://github.com/civicrm/cv). This command
automatically searches the directory tree for CiviCRM and bootstraps it.

In the remainder of this document, we'll discuss some general testing rules and end with links
to specific examples.

## Requirements

 * Working understanding of LAMP and CiviCRM installation.
 * General familiarity with some test tool (eg `phpunit` or `codeception`).
 * Vanilla file structure.
   * For example, in Drupal use `sites/all/modules`; in WP, use `wp-content`. Avoid symlinks. Use single-site. More sophisticated schemes *may* work, but they haven't been tested. You may need to patch `cv` to support other schemes.
 * CiviCRM v4.7+ (via [git](http://wiki.civicrm.org/confluence/display/CRMDOC/Contributing+to+CiviCRM+using+GitHub))
   * Note: You may be able to engineer tests to work with other versions, but this is best because:
     * The `civicrm.settings.php` template was updated in v4.7.1 to facilitate testing.
     * The `civicrm-core` test classes in v4.7.1 were refactored to be more re-usable.
     * The `civicrm-core` test classes do not ship with the tarballs.

## Setup: Option A: Buildkit

 * Install [buildkit](https://github.com/civicrm/civicrm-buildkit/).
 * Create a test site using [civibuild](https://github.com/civicrm/civicrm-buildkit/blob/master/doc/civibuild.md).

## Setup: Option B: Manual

 * Install Drupal/WordPress. (Backdrop/Joomla may also work - but not tested as much.)
 * Install CiviCRM v4.7+
 * Install [`cv`](https://github.com/civicrm/cv). Ensure it is located somewhere in the `PATH`.
 * `cd` into your Drupal/WordPress site and run `cv vars:fill`. This will create a file `~/.cv.json`.
   * Tip: If you need to share this installation with other local users, you may specify `export CV_CONFIG=/path/to/other/file.json`
 * Edit the file `~/.cv.json`. You may need to fill some or all of these details:
   * Credentials for an administrative CMS user (`ADMIN_USER`, `ADMIN_PASS`, `ADMIN_EMAIL`)
   * Credentials for a non-administrative CMS user (`DEMO_USER`, `DEMO_PASS`, `DEMO_EMAIL`)
   * Credentials for an empty test database (`TEST_DB_DSN`)
 * Install your favorite test-runner (e.g. [phpunit](phpunit.de), [codeception](http://codeception.com/), [behat](behat.org)).

## Mind your data

Civi is DB-oriented application.  When writing tests, you'll often do [crazy things](https://www.reddit.com/r/Jokes/comments/2m1b9b/a_code_tester_walks_into_a_bar_orders_a_beer/)
to the DB.  I recommend keeping a recent DB snapshot so that you can quickly restore.

(If you use [civibuild](https://github.com/civicrm/civicrm-buildkit/blob/master/doc/civibuild.md), it stores a
DB snapshot by default.  You can manage it with `civibuild snapshot <mybuild>` and `civibuild
restore <mybuild>`.)

## Approach: Fast, in-process testing

The fastest test-suites will load Civi one time. With most PHP test runners, you can edit the main
config file (`phpunit.xml.dist`, `codeception.yml`, or `behat.yml`) and specify a bootstrap
script.

To perform in-process testing, create or edit your bootstrap script and include this:

```php
eval(`cv php:boot`);
```

To get better error reporting, copy the *Generic Wrapper* (addendum) and use:

```php
eval(cv('php:boot --level=settings', 'phpcode'));
```

Evaluating this `php:boot` command will do a few things:

 * Locate the closest instance of CiviCRM
 * Register the class-loader
 * Read the settings (`civicrm.settings.php`)
 * Put some local configuration data in `$GLOBALS['_CV']` (such as `ADMIN_USER` and `ADMIN_PASS`).

Now, you can write tests that use CiviCRM classes and functions.

Tip: If your tests are likely to make a mess in the database, then you may want to run them on a
headless test database.  You can instruct `cv` to use a headless database by either:

 * Setting environment variable `CIVICRM_UF=UnitTests`
 * Calling `cv` with `-t` (e.g. `cv php:boot --level=settings -t`)

### Approach: End-to-end, multi-process testing

End-to-end test-suites perform a more thorough simulation of the system.  Throughout execution, the
test will frequently issue new requests which setup/teardown the CMS+CiviCRM systems (like a normal PHP
request).

To use this approach, copy the *Generic Wrapper* (addendum). Call `cv` to read configuration data and perform basic setup.

```php
// Configure the system
cv('api setting.create foo=bar');
cv('api extension.install key=org.example.foobar');
cv('api system.flush');

// Get the dashboard URL
$url = cv('url "civicrm/dashboard?reset=1"');

// Get credentials for DB, admin user, and demo user.
$config = cv('vars:show');

// Do some border-crossing evil
$hiddenData = cv('ev \'return Civi::service("top_secret")->getHiddenData();\'');

// Run a helper script
cv('scr /path/to/mysetup.php');
```

The same design works in other languages (Javascript, Ruby, Python, bash, etal), but you'll need to
reimplement the *Generic Wrapper* in the target language.

To learn more about commands in `cv`, simply run `cv` via CLI. You can try out
commands directly (eg `cv url "civicrm/dashboard?reset=1"`), get a list of
commands (`cv list`), or read help for specific commands (eg `cv url -h`).

(Note: Each call to `cv` spawns a new process.  If you need to make many calls to setup
configuration, consider putting them in a helper script to run them en masse.)

## Addendum: Examples

This README focuses on generic guidance, but the git repo also has examples for particular tools in
alternative branches:

 * [`phpunit`](https://github.com/civicrm/org.civicrm.testapalooza/tree/phpunit) (in-memory or end-to-end tests)
 * [`codeception-2.x`](https://github.com/civicrm/org.civicrm.testapalooza/tree/codeception-2.x) (in-memory or end-to-end tests; experimental)
 * [`protractor`](https://github.com/civicrm/org.civicrm.testapalooza/tree/protractor) (end-to-end tests only; TODO)

## Addendum: Generic Wrapper

For PHP-based tests, copy this `cv` wrapper function.  It simply executes the `cv`
command, checks for an error, and parses the JSON output.

```php
/**
 * Call the "cv" command.
 *
 * @param string $cmd
 *   The rest of the command to send.
 * @param string $decode
 *   Ex: 'json' or 'phpcode'.
 * @return string
 *   Response output (if the command executed normally).
 * @throws \RuntimeException
 *   If the command terminates abnormally.
 */
function cv($cmd, $decode = 'json') {
  $cmd = 'cv ' . $cmd;
  $descriptorSpec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => STDERR);
  $oldOutput = getenv('CV_OUTPUT');
  putenv("CV_OUTPUT=json");
  $process = proc_open($cmd, $descriptorSpec, $pipes, __DIR__);
  putenv("CV_OUTPUT=$oldOutput");
  fclose($pipes[0]);
  $result = stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  if (proc_close($process) !== 0) {
    throw new RuntimeException("Command failed ($cmd):\n$result");
  }
  switch ($decode) {
    case 'raw':
      return $result;

    case 'phpcode':
      // If the last output is /*PHPCODE*/, then we managed to complete execution.
      if (substr(trim($result), 0, 12) !== "/*BEGINPHP*/" || substr(trim($result), -10) !== "/*ENDPHP*/") {
        throw new \RuntimeException("Command failed ($cmd):\n$result");
      }
      return $result;

    case 'json':
      return json_decode($result, 1);

    default:
      throw new RuntimeException("Bad decoder format ($decode)");
  }
}
```

For end-to-end testing in different languages (eg Javascript, Ruby, Python), it should be easy to
write similar wrappers.
