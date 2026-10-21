# Portfolio Maker

## Project Description
A website where a user signs up, enters personal information (education, skills, projects, experience, links, profile picture),
saves it to an online database, chooses one of 3 portfolio templates (Simple, Modern, Creative), previews the result, and can
edit or delete it at any time.

## Technologies Used
- PHP 8 (server-side pages), HTML, CSS, JavaScript
- cURL (PHP talks to Supabase over HTTPS)

## Database Platform
- Database: Supabase (PostgreSQL)  |  Database Project: Portfolio Generator
- Login: Supabase Auth  |  Profile pictures: Supabase Storage (bucket `avatars`)

## Hosting / Deployment Platform
- Render (Docker web service)

## Published Website URL
- _add your Render URL here_

## Database Structure
Tables: `portfolios`, `education`, `skills`, `projects`, `experience`, `social_links`
(child tables link to `portfolios.id`, with Row Level Security so each user only sees their own data). Full SQL is in `schema.sql`.

## Run on your own computer (XAMPP)
1. Put this folder in `C:\xampp\htdocs\portfolio`.
2. Copy `config.local.php.example` to `config.local.php` and fill in your Supabase URL and public key.
3. Start Apache in the XAMPP Control Panel.
4. Open http://localhost/portfolio/test.php. All checks should show a green tick.

### Fix the cURL SSL error on XAMPP
If a check says "SSL certificate problem": download https://curl.se/ca/cacert.pem, save it to `C:\xampp\php\extras\ssl\cacert.pem`,
open `C:\xampp\php\php.ini`, set `curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"`, then restart Apache.

## Screenshots
_add screenshots of the system and the 3 templates here_
