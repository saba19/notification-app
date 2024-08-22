Feature:
  In order to implementation of the notification system, user can receive sms notification.
  As a external service I can send sms notification to client

  Background:
    Given There are following notification providers:
      | name     | channel | enabled | position |
      | twilio   | sms     | true    | 2        |
      | fake_sms | sms     | false   | 1        |

  Scenario: Client receives one SMS notification when multiple providers are available
    Given There is user with email "miripi6266@brinkc.com" and phone number "+48000000000"
    And the SMS provider "twilio" is available
    And the SMS provider "fakeSms" is available
    When an external service sends an SMS to the user with the following details:
      | client                | channel | subject | content                                       |
      | miripi6266@brinkc.com | sms     |         | Your account has been successfully registered |
    Then the response status code should be 200
    And the user "miripi6266@brinkc.com" should have received 1 notification with status "sent" and content "Your account has been successfully registered"

  Scenario: Client receives one SMS notification when only one provider is available
    Given There is user with email "miripi6267@brinkc.com" and phone number "+48000000000"
    And the SMS provider "twilio" is available
    And the SMS provider "fakeSms" is not available
    When an external service sends an SMS to the user with the following details:
      | client                | channel | subject | content                       |
      | miripi6267@brinkc.com | sms     |         | Your account has been updated |
    Then the response status code should be 200
    And the user "miripi6267@brinkc.com" should have received 1 notification with status "sent" and content "Your account has been updated"

  Scenario: No SMS providers are available, so the client does not receive a notification
    Given There is user with email "miripi6265@brinkc.com" and phone number "+48000000000"
    And the SMS provider "twilio" is not available
    And the SMS provider "fakeSms" is not available
    When an external service sends an SMS to the user with the following details:
      | client                | channel | subject | content       |
      | miripi6265@brinkc.com | sms     |         | Welcome again |
    Then the response status code should be 200
    And the user "miripi6265@brinkc.com" should have received 1 notification with status "failed" and content "Welcome again"

  Scenario: Client receives one SMS notification when only one provider is available
    Given There is user with email "miripi6267@brinkc.com" and phone number "+48000000000"
    And the SMS provider "twilio" is available
    And the SMS provider "fakeSms" is not available
    When an external service sends an SMS to the user with the following details:
      | client                | channel | subject | content                       |
      | miripi6267@brinkc.com | sms     |         | Your account has been updated |
    Then the response status code should be 200
    And the user "miripi6267@brinkc.com" should have received 1 notification with status "sent" and content "Your account has been updated"

  Scenario: External service cannot send SMS if client data is incorrect
    Given the SMS provider "twilio" is available
    And the SMS provider "fakeSms" is available
    When an external service sends an SMS to the user with the following details:
      | client                | channel   | subject | content                                       |
      | miripi6266 | invalid   |         | Your account has been successfully registered |
    Then the response status code should be 400
    Then the response message contains "User with id miripi6266 not found"

