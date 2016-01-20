<?php

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

/**
 * @file
 *
 * The CvBoot extension uses the "cv" command line helper to bootstrap CiviCRM.
 *
 * @code
 * ## codeception.yml
 * extensions:
 *     enabled:
 *         - CvBoot
 * @endcode
 *
 * Note: If you use PhpBrowser or WebDriver, leave the default URL value of
 * 'http://localhost/myapp'. CvBoot will automatically replace this with the
 * actual URL of the target CiviCRM instance.
 *
 * Advanced options:
 *   - To load Civi classes *without* DB, set "extensions.config.CvBoot.level: classloader"
 *   - To get Civi config without any bootstrap, set "extensions.config.CvBoot.level: none"
 *   - To change the dummy/placeholder URL, set "extensions.config.CvBoot.dummy_url: http://newdummy.extample"
 */
class CvBoot extends \Codeception\Extension {
  const DEFAULT_DUMMY_URL = 'http://localhost/myapp';

  public static $events = array(
    'suite.before' => 'beforeSuite',
    'test.before' => 'beforeTest',
  );

  public static $defaults = array(
    // How far to go in bootstrapping Civi.
    // Options: 'none', 'classloader', 'full'.
    'level' => 'full',

    // If any acceptance tests ar configured for dummy_url, they
    // will be updated with the real URL.
    'dummy_url' => 'http://localhost/myapp',
  );

  /**
   * @var array
   */
  public static $CONFIG = NULL;

  private $startUrl = NULL;

  public function beforeSuite(\Codeception\Event\SuiteEvent $e) {
    $this->boot();
  }

  public function beforeTest(\Codeception\Event\TestEvent $e) {
    if (in_array('PhpBrowser', $this->getCurrentModuleNames())) {
      /** @var \Codeception\Module\PhpBrowser $phpBrowser */
      $phpBrowser = $this->getModule('PhpBrowser');
      if ($this->isDummyUrl($phpBrowser->_getConfig('url'))) {
        //$this->writeln("\n\ALTER PhpBrowser.url\n\n");
        $phpBrowser->_reconfigure(array(
          'url' => $this->getStartUrl(),
        ));
      }
    }
    if (in_array('WebDriver', $this->getCurrentModuleNames())) {
      /** @var \Codeception\Module\WebDriver $webDriver */
      $webDriver = $this->getModule('WebDriver');
      if ($this->isDummyUrl($webDriver->_getConfig('url'))) {
        //$this->writeln("\n\ALTER WebDriver.url\n\n");
        $webDriver->_reconfigure(array(
          'url' => $this->getStartUrl(),
        ));
      }
    }
  }

  protected function boot() {
    static $booted = FALSE;
    if ($booted) {
      return;
    }
    $booted = TRUE;

    $extConfig = $this->getExtConfig();
    if (in_array($extConfig['level'], array('full', 'classloader'))) {
      //$this->writeln("\n\nBOOT\n\n");
      eval(\cv('php:boot --level=' . $extConfig['level'], TRUE));
    }

    self::$CONFIG = cv('show --buildkit');
  }

  protected function getExtConfig() {
    $config = $this->getGlobalConfig();
    $extConfig = self::$defaults;
    if (isset($config['extensions']['config']['CvBoot'])) {
      $extConfig = array_merge($extConfig, $config['extensions']['config']['CvBoot']);
    }
    return $extConfig;
  }

  protected function getStartUrl() {
    if ($this->startUrl === NULL) {
      $this->startUrl = cv('url civicrm/dashboard');
    }
    return $this->startUrl;
  }

  protected function isDummyUrl($url) {
    $extConfig = $this->getExtConfig();
    return $url === $extConfig['dummy_url'];
  }

}
