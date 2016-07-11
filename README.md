# CiviCRM Testapalooza

This project demonstrates of how to write tests for CiviCRM.  The techniques here are pretty
generic -- they ought to work with different package types (CiviCRM core, CiviCRM extensions,
Drupal modules, WordPress plugins) and different test-runners (e.g.  `phpunit`, `codeception`,
`protractor`, `behat`).

Testing CiviCRM is trickier than testing a basic library -- tests may involve system services (from
Civi or the CMS), and CiviCRM administrators may use different CMS's, file structures, and URLs.  One
way to mitigate this would be creating configuration files (e.g. each extension could have its own
copy of `codeception.yml` or `karma.conf.js` which must be fine-tuned with file-paths and URLs
for a local CiviCRM). Unfortunately, managing those configuration files grows unwieldy with multiple extensions.

To resolve this, we use the helper command, [`cv`](https://github.com/civicrm/cv). This command
automatically searches for CiviCRM and bootstraps it. It can be integrated into your extension tests,
enabling them to execute without custom configuration files.

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

## Techniques

 * **Bootstrap**: [Fast, in-process testing](in-process.md) ("Headless") - The fastest test-suites will load Civi one time, but they provide a less thorough simulation.
 * **Bootstrap**: [End-to-end, multi-process testing](e2e.md) ("E2E") - The most realistic test-suites execute tests in a fully configured Civi+CMS installation, but they incur greater performance penalties.
 * **Data management**: [Civi\Test and Transactions](civi-test.md) - Declare the configuration in which tests should run.

## Examples

This README focuses on generic guidance, but the git repo also has examples for particular tools in
alternative branches:

 * [`phpunit`](https://github.com/civicrm/org.civicrm.testapalooza/tree/phpunit) (in-memory or end-to-end tests)
 * [`codeception-2.x`](https://github.com/civicrm/org.civicrm.testapalooza/tree/codeception-2.x) (in-memory or end-to-end tests; experimental)
 * [`protractor`](https://github.com/civicrm/org.civicrm.testapalooza/tree/protractor) (end-to-end tests only; TODO)
