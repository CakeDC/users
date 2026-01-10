# CakeDC Users Plugin

[![Build Status](https://img.shields.io/github/actions/workflow/status/CakeDC/users/ci.yml?branch=master&style=flat-square)](https://github.com/CakeDC/users/actions?query=workflow%3ACI+branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/gh/CakeDC/users.svg?style=flat-square)](https://codecov.io/gh/CakeDC/users)
[![Total Downloads](https://img.shields.io/packagist/dt/CakeDC/users.svg?style=flat-square)](https://packagist.org/packages/CakeDC/users)
[![Latest Stable Version](https://img.shields.io/packagist/v/CakeDC/users.svg?style=flat-square)](https://packagist.org/packages/CakeDC/users)
[![License](https://img.shields.io/packagist/l/CakeDC/users.svg?style=flat-square)](https://packagist.org/packages/CakeDC/users)

The **Users** plugin for CakePHP provides a comprehensive, extensible solution for user management, authentication, and authorization. It's designed to get you up and running with a full-featured user system in minutes, while remaining flexible enough for complex, custom applications.

## Versions and branches

| CakePHP |                    CakeDC Users Plugin                     | Tag | Notes |
|:-------:|:----------------------------------------------------------:|:---:|:---|
|  ^5.3   | [16.x](https://github.com/cakedc/users/tree/16.next-cake5) | 16.0.0 | **Stable** (Current) |
|  ^5.0   | [15.x](https://github.com/cakedc/users/tree/15.next-cake5) | 15.1.3 | Stable |
|  ^5.0   | [14.x](https://github.com/cakedc/users/tree/14.next-cake5) | 14.3.4 | Stable |
|  ^4.5   | [13.x](https://github.com/cakedc/users/tree/13.next-cake4) | 13.0.1 | Stable |
|  ^4.3   | [11.x](https://github.com/cakedc/users/tree/11.next-cake4) | 11.1.0 | Stable |
|  ^4.0   |     [9.x](https://github.com/cakedc/users/tree/9.next)     | 9.0.5 | Stable |
|  ^3.7   |     [8.x](https://github.com/cakedc/users/tree/8.next)     | 8.5.1 | Stable |

## Key Features

* **User Lifecycle:** Registration, email validation, profile management, and password reset.
* **Authentication:** Login/Logout, "Remember Me" (Cookie), and Magic Link (one-click login).
* **Social Login:** Facebook, Twitter, Instagram, Google, LinkedIn (via OpenID Connect), Amazon, GitHub.
* **Security & 2FA:** 
    * One-Time Password (OTP) for Two-Factor Authentication.
    * Webauthn (Yubico Key, TouchID, etc.) for Two-Factor Authentication.
    * Account lockout policy after failed attempts.
    * Password strength meter.
    * reCaptcha v2 and v3 support.
* **Authorization:** Integrated RBAC (Role-Based Access Control) and Superuser support via [CakeDC/auth](https://github.com/CakeDC/auth).
* **Extensibility:** Easily extend Controllers, Models (Tables/Entities), Mailers, and Templates.
* **Admin Management:** Out-of-the-box CRUD for user management.

## Requirements

* CakePHP 5.0+
* PHP 8.1+

## Quick Start (Installation)

1. **Install via Composer:**
   ```bash
   composer require cakedc/users
   ```
2. **Load the Plugin:**
   In your `src/Application.php`:
   ```php
   public function bootstrap(): void
   {
       parent::bootstrap();
       $this->addPlugin('CakeDC/Users');
   }
   ```
3. **Run Migrations:**
   ```bash
   bin/cake migrations migrate -p CakeDC/Users
   ```
4. **Create a Superuser:**
   ```bash
   bin/cake users add_superuser
   ```

## Documentation

For full installation details, configuration options, and tutorials, please visit the [Documentation](Docs/Home.md).

## Support

* **Bugs & Features:** Please use the [GitHub issues](https://github.com/CakeDC/users/issues) section.
* **Commercial Support:** [Contact CakeDC](https://www.cakedc.com/contact) for professional assistance.

## Contributing

We welcome contributions! Please review our [Contribution Guidelines](CONTRIBUTING.md) and the [CakeDC Plugin Standard](https://www.cakedc.com/plugin-standard).

## License

Copyright 2010 - Present Cake Development Corporation (CakeDC). All rights reserved.

Licensed under the [MIT](LICENSE.txt) License. Redistributions of the source code must retain the copyright notice.
