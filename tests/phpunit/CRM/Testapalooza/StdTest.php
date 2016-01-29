<?php

/**
 * This is a standard unit-test based on CiviUnitTestCase.
 *
 * CiviUnitTestCase has a bunch of built-in features, such as:
 *   - Auto-populating the headless test database with clean data.
 *   - Resetting Civi's singletons and static variables.
 *   - Wrapping tests inside transactions (optional).
 */
class CRM_Testapalooza_StdTest extends CiviUnitTestCase {

  function setUp() {
    parent::setUp();
    $this->useTransaction();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Test that version is returned.
   */
  function testWellFormedVersion() {
    $this->assertRegExp('/^([0-9\.]|alpha|beta)*$/', CRM_Utils_System::version());
  }

}
