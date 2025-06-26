# Twellr

Twellr is a web application built with Laravel and Livewire Volt. Below is a summary of the application's main HTTP routes and their purposes, auto-generated from the route definitions in the `routes/web.php`, `routes/auth.php`, `routes/admin.php`, and `routes/user.php` files.

---

![Twellr Logo](public/assets/twellr-bg-white.jpg)

---

## Table of Contents

- [General Routes](#general-routes)
- [Authentication Routes](#authentication-routes)
- [Admin Routes](#admin-routes)
- [User Routes](#user-routes)
- [Fallback](#fallback)
- [Middleware Used](#middleware-used)
- [Screenshots](#screenshots)

---

## General Routes

| Method | URI          | Name        | Middleware         | Description                        |
|--------|--------------|-------------|--------------------|------------------------------------|
| GET    | /            | home        | -                  | Landing/welcome page.              |
| GET    | /r/{slug?}   | -           | guest              | User referral landing (guest only).|

---

## Authentication Routes

| Method | URI                         | Name                          | Middleware     | Description                                      |
|--------|-----------------------------|-------------------------------|----------------|--------------------------------------------------|
| GET    | /admin/register             | admin.register                | guest, AdminExists | Admin registration page.                      |
| GET    | /admin/login                | admin.login                   | guest          | Admin login page.                                |
| GET    | /register                   | register                      | guest          | User registration page.                          |
| GET    | /login                      | login                         | guest          | User login page.                                 |
| GET    | /auth/google                | auth.google.login             | guest          | Google login.                                    |
| GET    | /auth/google/signup         | auth.google.signup            | guest          | Google signup.                                   |
| GET    | /auth/google/callback       | auth.google.callback          | guest          | Google OAuth callback.                           |
| GET    | /email/verification         | email.verification            | guest          | Email verification prompt.                       |
| GET    | /complete/registration      | complete.registration         | guest          | Complete registration page.                      |
| GET    | /forgot-password            | password.request              | guest          | Password reset request page.                     |
| GET    | /reset-password/{token}     | password.reset                | guest          | Password reset page with token.                  |
| GET    | /verify-email               | verification.notice           | auth           | Email verification notice (after login).         |
| GET    | /verify-email/{id}/{hash}   | verification.verify           | auth, signed, throttle:6,1 | Email verification callback.      |
| GET    | /confirm-password           | password.confirm              | auth           | Password confirmation page.                      |

---

## Admin Routes

> All admin routes require `auth`, `verified`, and `IsAdmin` middleware unless otherwise specified.

| Method | URI                      | Name                    | Description                     |
|--------|--------------------------|-------------------------|---------------------------------|
| GET    | /admin/email/verification| admin.email.verification| Admin email verification prompt |
| GET    | /admin/dashboard         | admin.dashboard         | Admin dashboard                 |
| GET    | /admin/profile           | admin.profile           | Admin profile page              |
| GET    | /admin/settings          | admin.settings          | Admin settings                  |
| GET    | /admin/system/preferences| admin.preferences       | Admin system preferences        |
| GET    | /admin/uploaded/designs  | admin.designs           | Uploaded designs management     |
| GET    | /admin/orders            | admin.orders            | Admin order management          |
| GET    | /admin/withdrawals       | admin.withdrawal        | Admin withdrawals               |
| GET    | /admin/blog              | admin.blog.post         | Admin blog post management      |
| GET    | /admin/blog/upload       | admin.blog.post.upload  | Blog post upload                |
| GET    | /admin/user/management   | admin.user.management   | User management                 |

---

## User Routes

> All user routes require `auth` and `verified` middleware unless otherwise specified.

| Method | URI                    | Name                        | Middleware            | Description                        |
|--------|------------------------|-----------------------------|-----------------------|------------------------------------|
| GET    | /payment/preference    | creative.payment.preference | PaymentPreference     | User payment preferences           |
| GET    | /upload                | creative.upload             | -                     | Upload page for creatives          |
| GET    | /marketplace/{slug?}   | market.place                | -                     | Marketplace browsing               |
| GET    | /explore               | explore                     | -                     | Explore page                       |
| GET    | /support               | support                     | -                     | Support page                       |
| GET    | /wallet                | wallet                      | -                     | User wallet page                   |
| GET    | /blog                  | blog                        | -                     | Blog overview                      |
| GET    | /settings              | settings                    | -                     | User settings                      |
| GET    | /cart                  | cart                        | -                     | Shopping cart                      |
| GET    | /{slug}                | creative.profile            | -                     | Creative profile page              |
| GET    | /fund/wallet           | fund.wallet                 | -                     | Fund wallet (initiate payment)     |
| GET    | /payment/confirm       | confirm.payment             | -                     | Confirm payment                    |
| GET    | /design/contests       | design.contest              | -                     | Design contests                    |

Additionally, the following user profile and dashboard routes are available (with extra middleware):

| Method | URI          | Name      | Middleware                 | Description           |
|--------|--------------|-----------|----------------------------|-----------------------|
| GET    | /dashboard   | dashboard | auth, verified, referred, IsCreative, IsUser | User dashboard        |
| GET    | /profile     | profile   | auth, verified, IsCreative, IsUser | User profile page     |

---

## Fallback

If no route matches, a 404 error will be returned.

---

## Middleware Used

- **auth**: User must be authenticated.
- **guest**: Only accessible to non-authenticated users.
- **verified**: User must have a verified email.
- **referred**: User must be referred.
- **IsAdmin**: User must be an admin.
- **IsCreative**, **IsUser**: Custom checks for user roles.
- **AdminExists**: Admin registration only if admin exists.
- **PaymentPreference**: Checks user payment preference.
- **signed, throttle:6,1**: Signature and rate limiting for email verification.

---

## Screenshots

Below are some example visuals from the project. You can add more screenshots or visual assets from the `public/assets/` folder as needed.

![App Screenshot](public/assets/twellr-bg-white.jpg)

---

For more details, see the individual controllers and Livewire Volt pages referenced in the route definitions.
