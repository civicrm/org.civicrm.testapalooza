<?php

ini_set('memory_limit', '2G');
ini_set('safe_mode', 0);

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

eval(cv('php:boot', TRUE));
$GLOBALS['_CV'] = cv('vars:show');

set_include_path(__DIR__ . PATH_SEPARATOR . get_include_path());
