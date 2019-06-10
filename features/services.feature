Feature: PhpSpec Coverage Extension

  Background:
    Given I configure phpspec with:
    """
    extensions:
      Doyo\PhpSpec\CodeCoverage\Extension:
          filters:
              whitelist:
                  - src
              blacklist:
                  - src/Context
          reports:
              php: build/cov/phpspec.cov
              html: build/phpspec
    """

  Scenario: Core Service should loaded
    Then service "doyo.coverage.driver" should exist
    And service "doyo.coverage.filter" should exist
    And service "doyo.coverage.processor" should exist
    And service "doyo.coverage.dispatcher" should exist
    And service "doyo.coverage.reports.html" should exist
    And service "doyo.coverage.reports.php" should exist
    And service "doyo.coverage.listener" should exist

