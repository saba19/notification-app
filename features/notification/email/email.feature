Feature:
  In order to implementation of the notification system, user can receive Email notification.
  As a external service I can send Email notification to client

  Background:
    Given There are following notification providers:
      | name       | channel | enabled | position |
      | sendgrid   | email   | true    | 2        |
      | fake_email | email   | false   | 1        |

  Scenario: Client receives one Email notification when multiple providers are available
    Given There is user with email "miripi6266@brinkc.com" and phone number "+48000000000"
    And the Email provider "sendgrid" is available
    And the Email provider "fakeEmail" is available
    When an external service sends an Email to the user with the following details:
      | client                | channel | subject | content                                       |
      | miripi6266@brinkc.com | email   |         | Your account has been successfully registered |
    Then the response status code should be 200
    And the user "miripi6266@brinkc.com" should have received 1 notification with status "sent" and content "Your account has been successfully registered"

  Scenario: Client receives one Email notification when only one provider is available
    Given There is user with email "miripi6267@brinkc.com" and phone number "+48000000000"
    And the Email provider "sendgrid" is available
    And the Email provider "fakeEmail" is not available
    When an external service sends an Email to the user with the following details:
      | client                | channel | subject | content                       |
      | miripi6267@brinkc.com | email   |         | Your account has been updated |
    Then the response status code should be 200
    And the user "miripi6267@brinkc.com" should have received 1 notification with status "sent" and content "Your account has been updated"

  Scenario: No Email providers are available, so the client does not receive a notification
    Given There is user with email "miripi6265@brinkc.com" and phone number "+48000000000"
    And the Email provider "sendgrid" is not available
    And the Email provider "fakeEmail" is not available
    When an external service sends an Email to the user with the following details:
      | client                | channel | subject | content       |
      | miripi6265@brinkc.com | email   |         | Welcome again |
    Then the response status code should be 200
    And the user "miripi6265@brinkc.com" should have received 1 notification with status "failed" and content "Welcome again"

  Scenario: External service cannot send Email if client data is incorrect
    And the Email provider "sendgrid" is not available
    And the Email provider "fakeEmail" is not available
    When an external service sends an Email to the user with the following details:
      | client  | channel | subject | content       |
      | electra | email   |         | Welcome again |
    Then the response status code should be 400
    Then the response message contains "User with id electra not found"


