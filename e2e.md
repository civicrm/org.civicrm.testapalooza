# Bootstrap for end-to-end, multi-process testing

End-to-end test-suites perform a more thorough simulation of the system.  Throughout execution, the
test will frequently issue new requests which setup/teardown the CMS+CiviCRM systems (like a normal PHP
request).

The [`cv`](https://github.com/civicrm/cv) shell command allows you to read configuration data (e.g. `cv vars:show`) and perform basic setup (e.g. `cv api setting.create foo=bar`). To use it as part of an E2E test, create a generic wrapper ([PHP example](wrapper.php)) and then send commands like:

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

The same design should work in any language (Javascript, Ruby, Python, etal), but you'll need to
reimplement the generic wrapper in the target language.

To learn more about commands in `cv`, simply run `cv` via CLI. You can try out
commands directly (eg `cv url "civicrm/dashboard?reset=1"`), get a list of
commands (`cv list`), or read help for specific commands (eg `cv url -h`).

(Note: Each call to `cv` spawns a new process.  If you need to make many calls to setup
configuration, consider putting them in a helper script to run them en masse.)
