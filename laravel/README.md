# presensi-internship

## Login, reset password, and Google sign-in

After updating the application, run database migrations:

```sh
php artisan migrate
```

Password reset codes are valid for 10 minutes, can only be used once, and are
stored as hashes. Configure a real SMTP or transactional email provider in
`.env` before using password resets outside local development:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=your-mail-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_FROM_ADDRESS=an-address-on-your-verified-domain
MAIL_FROM_NAME="Let's Play Indonesia"
```

The sender address must belong to a domain verified with the chosen provider.
Do not commit provider credentials.

To enable Google sign-in, create a Google OAuth web client and add the
application callback URL (`APP_URL/auth/google/callback`) as an authorized
redirect URI. Set its credentials in `.env`:

```dotenv
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

Google sign-in only authenticates an existing active account with a matching,
Google-verified email address; it does not create accounts. Use HTTPS and the
production callback URL in production.