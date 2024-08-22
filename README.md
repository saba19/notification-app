# Notification Service 

## Getting Started

1. Have Docker / Docker Desktop installed on local computer 
2. Have installed Make on local computer
2. Run `make build` to build fresh images 
3. Run `make start` to create and spin up the app 
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `make down` to stop the Docker containers.
6. A working implementation for sending notifications via SMS using Twilio and email using SendGrid has been added. 
If you wish to test this functionality, you need to fill in the missing environment variables in the .env file:

Check out Makefile to see more commands.

## The BACKGROUND
The company has multiple services including one that provides a user identity and several that require sending notifications. 
The goal is to create a new service abstracting the notification part.
This is not a complete solution. It just shows my approach to solving the problem. Many things are simplified.



The new service is capable of the following:

1. Send notifications via different channels.
   It can use different messaging services/technologies for communication (e.g. SMS, email, push notification, Facebook Messenger, etc). 
2. Currently, a working implementation for SMS (Twillio) and email (Sendgrid) notifications has been added 
3. It is possible to define several providers for the same type of notification channel. e.g. two providers for SMS.
4. A notification is delayed and later resent if all providers fail.
5. The service is Configuration-driven: It is possible to enable/disable different communication channels with configuration in database (see Notification Providers)
6. You can track what messages were sent, when, and to whom. All information are stored in Notification table
7. Solution reflects some ideas of DDD and CQRS.But not everything is according to theory. For example, doctrine annotations should be thrown out of the domain to a separate folder with configuration.

### TASK

### Running Tests

To run tests in the application, use the following commands:

1. **Run Unit Tests**  

   To execute unit tests, use the command:

   ```bash
   make test-unit
   ```
2.  **Run Behat Tests** 

   To execute Behat tests, use the command:

   ```bash
   make test-behat
   ```
3.  **Run All Tests**

   To run all tests (unit and Behat), use the command:

   ```bash
   make test
   ```

### Default Providers Configuration

To facilitate testing, the following providers are enabled by default in the database:


|  name        | channel | enabled | position |
|--------------|---------|---------|----------|
|  twilio      | sms     | 1       | 2        |
|  fake_sms    | sms     | 0       | 1        |
|  sendgrid    | email   | 1       | 2        |
|  fake_email  | email   | 0       | 1        |

### Sending An Sms Notification Using Twilio

To send a real email using SendGrid, follow these steps:

1. **Create a Twilio Account**  
   Sign up for an account on [Twilio](https://www.twilio.com/).

2. **Configure Sms Settings**  
   Add the necessary environment variables in your `.env` file:

   ```
   TWILIO_ACCOUNT_SID=<your_twilio_account_sid>
   TWILIO_AUTH_TOKEN=<your_twilio_auth_token>
   TWILIO_NUMBER=<your_twilio_phone_number>

3. Use the POST method to create a user in the application:

   POST http://localhost/user

   Request body:

      ```
      {
        "email": "test+tests@gmail.com",
        "phoneNumber": "+48123456789"
      }
      ```

   The method will return the id of the created user.

4. Send an SMS Notification
   Use the following endpoint to send an email (or SMS):
   POST http://localhost/send-notification

   ```
   {
     "recipient": "66c36b376b987", // Use the user ID returned by the POST create user method
     "channel": "email",// Available options: sms, email, push
     "content": "example content email" // Notification content
   }
   ```
5. You should receive sms notification

### Sending a Notification Using SendGrid

To send a real email using SendGrid, follow these steps:

1. **Create a SendGrid Account**  
   Sign up for an account on [SendGrid](https://sendgrid.com/).

2. **Configure Email Settings**  
   Add the necessary environment variables in your `.env` file:

   ```plaintext
   SENDGRID_SENDER=<your_sendgrid_sender_email>
   MAILER_DSN=<ysendgrid://KEY@default>

3. Use the POST method to create a user in the application:

   POST http://localhost/user

   Request body:

      ```
      {
        "email": "test+tests@gmail.com",
        "phoneNumber": "+48123456789"
      }
      ```

   The method will return the id of the created user.

4. Send an Email Notification
   Use the following endpoint to send an email (or SMS):
   POST http://localhost/send-notification

   ```
   {
     "recipient": "66c36b376b987", // Use the user ID returned by the POST create user method
     "channel": "email",// Available options: sms, email, push
     "subject": "Some example subject", // Required for emails
     "content": "example content email" // Notification content
   }
   ```
5. You should receive an email notification

