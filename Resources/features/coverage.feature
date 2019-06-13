Feature: PHP Spec Code Coverage

  @coverage
  Scenario: Loading required service
    Given the spec file "spec/Test/FooSpec.php" contains:
      """
      <?php

      namespace spec\Test;

      use PhpSpec\ObjectBehavior;

      class FooSpec extends ObjectBehavior
      {
          function it_is_initializable()
          {
                $this->shouldHaveType('Test\Foo');
          }

          function it_should_say_foo()
          {
              $this->say()->shouldReturn('Foo Bar');
          }
      }
      """
    And the class file "src/Test/Foo.php" contains:
      """
      <?php
      
      namespace Test;
      
      class Foo
      {
          public function say()
          {
              return "Foo Bar";
          }
      }
      """
    And the config file contains:
      """
      extensions:
          Doyo\PhpSpec\CodeCoverage\Extension:
              filter:
                  - directory: src
              reports:
                  php: build/cov/phpspec.cov
                  html: build/phpspec
                  text: ~
      """
    When I run phpspec with option coverage
    And service "doyo.coverage.container" should loaded
    And service "doyo.coverage.listener" should loaded
    And I should see "2 passed"
    And I should see "generated html"
    And I should see "generated php"
    And I should see "\Test::Test\Foo"
    When I read coverage report "build/cov/phpspec.cov"
    Then file "src/Test/Foo.php" line 9 should covered
