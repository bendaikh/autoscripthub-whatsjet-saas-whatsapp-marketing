# WhatsJet

WhatsApp marketing app (SaaS). This package is WhatsJet 7.5.2.

Customers install it on their own PHP server. The short version:

1. You need PHP 8.2+, MySQL or MariaDB, and Composer (the setup script can download Composer).
2. Create an empty database.
3. Open the `Source` folder and run `php install.php`.
4. Point your domain at `Source/public`, sign in, change the default password, and enter your Envato purchase code if the app asks for it.

The purchase-code check is part of the product. Setup does not turn it off.

Step-by-step instructions, requirements, and common errors are in [INSTALL.md](INSTALL.md).

The `Source/vendor` folder is not included. `php install.php` runs `composer install` to create it.
