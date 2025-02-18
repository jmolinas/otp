# OTP Verification System
## Installation
1. Setting up, make sure that your local machine has PHP, Composer, Mysql installed. In addition, you should either install Node and npm or Bun.
 ```
  $ composer install
  $ cd otp
  $ npm install && npm run build
  $ composer run dev
  ```
2. Configure and run migration, copy .env.example and make necessary changes based on your local development.
 ```
  $ cp .env.example .env
  $ php artisan migrate
  ```
## Testing
1. Run Feature test
```
  $ php artisan test --filter=OtpVerificationTest
```
2. Run Unit test
```
  $ php artisan test --filter=OtpTest
```

## Assumptions
1. User Authentication is Required
* The OTP model assumes that a valid user (user_id) exists before an OTP can be created or verified.
* The system expects Auth::user() to return a logged-in user during OTP verification.

2. OTP Codes are Numeric and Exactly 6 Digits
* The OTP model and tests assume that all OTPs are 6-digit numeric values.
* No alphanumeric or variable-length OTPs were considered.

3. OTP Expiry is Based on Timestamps
* The OTP verification relies on expires_at > now().
* Expired OTPs are never valid, even if the user enters the correct code.

4. Each OTP is Used Only Once
* The system assumes an OTP is deleted after successful verification.
* Users must request a new OTP if they need another verification attempt.

5. Database Integrity is Maintained
* The OTP model stores user_id, code, expires_at, and type as required fields.
* There is no soft delete mechanism for OTPs (they are hard deleted after use).

6. OTP Type is Required for Verification
* The OtpType::EMAIL enum was assumed to be a required field, meaning OTPs are associated with a verification type (e.g., email, SMS, etc.).

## Additional Features
1. Multi-Channel OTP Delivery
Feature: Allow OTPs to be sent via multiple channels (e.g., SMS, Email, Push Notifications).

2. Resend OTP with Cooldown Period
Feature: Implement a resend OTP feature with a cooldown period (e.g., 30 seconds between OTP resend attempts).

3. OTP Validation with Expiry Notification
Feature: Notify the user when the OTP is about to expire (e.g., 1 minute before expiration).

4. OTP for Different Account Actions
Feature: Use OTPs for different account actions such as changing password, updating sensitive profile information, or logging in from new devices.

5. OTP Verification for New Devices or IPs
Feature: Trigger OTP verification when a user attempts to login from a new device or unrecognized

6. Automatic OTP Resend on Failure
Feature: If the OTP entered is incorrect, prompt the user with an option to automatically resend the OTP after a specified interval (e.g., 10 seconds).

## Technical Decisions
1. Use of UUID for OTP Model IDs
Decision: Switch from auto-incrementing integer IDs to UUIDs for OTP records.

Reasoning:
Security: UUIDs are more difficult to predict and are better suited for systems where public-facing data (like OTPs) should not reveal information about the database structure.
Scalability: UUIDs are globally unique, which is useful in distributed systems where multiple instances of the app might generate OTPs concurrently without risking collisions.
Consistency: UUIDs maintain uniqueness across systems, which is beneficial for systems that need to synchronize data across different environments or services.

2. Rate Limiting OTP Requests
Decision: Implement rate limiting using Laravel’s built-in RateLimiter class to prevent users from requesting OTPs too frequently.

Reasoning:
Abuse Prevention: Rate limiting prevents users or malicious actors from spamming OTP requests and overloading the system.
User Experience: By allowing only a limited number of OTP requests within a given time window (e.g., 3 requests every 10 minutes), users can’t request OTPs repeatedly, ensuring that they only get one in a reasonable time frame.
Security: Limiting OTP requests reduces the risk of brute-force attacks, as attackers can’t flood the system with multiple OTP requests in a short period.

3. Use of Livewire Volt for OTP Input
Decision: Use Livewire Volt to handle OTP input dynamically.

Reasoning:
Reactive Components: Livewire allows for interactive, reactive components without writing custom JavaScript. This is ideal for the OTP input field where we need to handle user input in real-time.
Ease of Integration: Livewire works seamlessly with Laravel, and Volt simplifies managing the state of OTP input fields, which reduces the complexity of handling JavaScript and live updates.
User Experience: The OTP field is updated automatically as users type or paste their OTP, providing a smooth and intuitive interface.

4. Use of Bootstrap for Styling
Decision: Use Bootstrap for styling the OTP component instead of Tailwind CSS.

Reasoning:
Consistency: Bootstrap is a widely used CSS framework that already provides consistent, responsive, and well-tested components. It’s a good fit for projects that want to implement common UI elements quickly.
Familiarity: Bootstrap is familiar to many developers, including those who might be new to the project, so it's easier for collaboration and maintenance.
Responsive Design: Bootstrap offers an out-of-the-box responsive design, ensuring the OTP component works well on both desktop and mobile devices.