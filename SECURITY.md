# Deployment credentials

Set database and M-Pesa credentials in the PHP process environment using the names in .env.example. This legacy project does not load .env files automatically: configure the web server or process environment. Missing credentials do not fall back to embedded values. Rotate previously committed database passwords, M-Pesa consumer credentials, passkey and security password through their administrators. No deployed service was changed by this source cleanup.
