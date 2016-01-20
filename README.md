# CiviCRM Extension Testapalooza

This project demonstrates of how to write tests for CiviCRM.  The techniques here are pretty
generic -- they ought to work with different package types (CiviCRM extensions, Drupal modules,
WordPress plugins) and different test-runners (e.g.  `phpunit`, `codeception`, `protractor`,
`behat`).

In all cases, the tests use a local helper command, [`cv`](https://github.com/civicrm/cv), to work
with the code or data from a local CiviCRM instance.

(*Note: At time of writing, I have not yet updated `civicrm-core.git` to comply with this
structure...  but it will happen... AND GET RID OF `packages/PHPUnit`!!*)

## Requirements

 * Install a local copy of CiviCRM
   * (*Strongly suggested*: Use [buildkit](https://github.com/civicrm/civicrm-buildkit/)'s `civibuild` with default file hierarchy)
 * Install [`cv`](https://github.com/civicrm/cv) somewhere in the `PATH`.
   * (*Note*: This is bundled with the buildkit.)
 * Create your test-suite somewhere under the Drupal/WordPress web root.

## Mind your data

Civi is DB-oriented application.  When writing tests, you'll often do crazy things to the DB.  I
recommend keeping a recent DB snapshot so that you can quickly restore.

(If you use [buildkit](https://github.com/civicrm/civicrm-buildkit/)'s `civibuild`, it stores a
DB snapshot by default.  You can manage it with `civibuild snapshot <mybuild>` and `civibuild
restore <mybuild>`.)

## Examples

This README focuses on generic guidance, but the git repo also has examples for particular tools in
alternative branches:

 * `codeception-2.x` (in-memory or end-to-end tests)
 * `phpunit` (in-memory or end-to-end tests)
 * `protractor` (end-to-end tests only)

## Approach: Fast, in-process testing

The fastest test-suites will load Civi once -- using a "bootstrap" or "setup" function.  As long as
the test is executed in PHP, this is pretty simple.  Copy the *Generic Wrapper* (below) and
then execute this during setup:

```php
eval(cv('php:boot'), TRUE);
```

### Approach: End-to-end, multi-process testing

End-to-end test-suites perform a more thorough simulation of the system.  Throughout execution, the
test will frequently issue new requests which setup/teardown the CiviCRM system (like a normal PHP
request).

You can use the `cv` helper to read configuration data and perform basic setup.

```php
// Configure the system
cv('api setting.create foo=bar');
cv('api extension.install key=org.example.foobar');
cv('api system.flush');

// Get the dashboard URL
$url = cv('url "civicrm/dashboard?reset=1"');

// Get credentials for DB, admin user, and demo user.
$config = cv('show --buildkit');

// Do some border-crossing evil
$hiddenData = cv('ev \'return Civi::service("top_secret")->getHiddenData();\'');

// Run a helper script
cv('scr /path/to/mysetup.php');
```

The same design works in other languages (Javascript, Ruby, Python, bash, etal), but you'll need to
reimplement the *Generic Wrapper* in the target language.

## Generic Wrapper

If the test is written in PHP, then copy this `cv` wrapper function.  It simply executes the `cv`
command, checks for an error, and parses the JSON output.

```php
/**
 * Call the "cv" command.
 *
 * @param string $cmd
 *   The rest of the command to send.
 * @param bool $raw
 *   If TRUE, return the raw output. If FALSE, parse JSON output.
 * @return string
 *   Response output (if the command executed normally).
 * @throws \RuntimeException
 *   If the command terminates abnormally.
 */
function cv($cmd, $raw = FALSE) {
  $cmd = 'cv ' . $cmd;
  $descriptorSpec = array(0 => array("pipe", "r"), 1 => array("pipe", "w"), 2 => STDERR);
  $env = $_ENV + array('CV_OUTPUT' => 'json');
  $process = proc_open($cmd, $descriptorSpec, $pipes, __DIR__, $env);
  fclose($pipes[0]);
  $bootCode = stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  if (proc_close($process) !== 0) {
    throw new RuntimeException("Command failed ($cmd)");
  }
  return $raw ? $bootCode : json_decode($bootCode, 1);
}
```

For end-to-end testing in different languages (eg Javascript, Ruby, Python), it should be easy to
write similar wrappers.

## Wherephar art thou executable?

To be more dynamic!

There is no single URL, CMS, directory structure, or username/password shared by all Civi
developers, so we can't use boilerplate config files.  And configuring each test-suite in each
extension would get pretty tedious.

The `cv` command performs a lookup of the Civi configuration details which:

 * Scans the directory tree to find CMS+Civi.
 * Accepts options through environment variables (`CIVICRM_SETTINGS`).
 * Works with Drupal-based and WordPress-based installations. (Maybe Joomla... haven't tested.)
 * Can be used from any programming language.

It has one major limitation -- the test and Civi site should be on the same system.  For in-process
testing, this is a strong, logical requirement.  For end-to-end testing, it's a weak requirement;
patch-welcome if you have a way to resolve.
