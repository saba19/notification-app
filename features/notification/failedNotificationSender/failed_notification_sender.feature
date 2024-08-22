Feature: Failed notification sender command
  In order to resend failed notifications
  As a developer
  I want to run the command that resends failed notifications

  Background:
    Given There are following notification providers:
      | name       | channel | enabled | position |
      | twilio     | sms     | true    | 2        |
      | fake_sms   | sms     | false   | 1        |
      | sendgrid   | email   | true    | 2        |
      | fake_email | email   | false   | 1        |


  Scenario: No failed notifications to resend
    Given there is an "sms" notification "66c36b376b987" with status "sent"
    When I run "app:resent-failed-notification"
    Then I should see "No notification to resend"

  Scenario: Notification with status created and sent cannot be resent again
    And there is an "email" notification "66c36b376b989" with status "created"
    And there is an "email" notification "66c36b376b950" with status "sent"
    When I run "app:resent-failed-notification"
    Then I should see "No notification to resend"

  Scenario: Only delayed and failed notifications can be successfully resent
    Given there is an "sms" notification "66c36b376b987" with status "failed" and last failure attempt "10 minutes" ago
    And there is an "email" notification "66c36b376b988" with status "failed" failed just know
    And the SMS provider "twilio" is available
    And the Email provider "sendgrid" is available
    When I run "app:resent-failed-notification"
    Then I should see notification "66c36b376b987" has status "sent"
    Then I should see notification "66c36b376b988" has status "failed"
    And the command should finish successfully
