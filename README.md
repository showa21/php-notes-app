# PHP Notes App

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE) ![Built with](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)

A small multi-user notes app written in **plain PHP 8** (no framework) with SQLite. It focuses on the fundamentals employers look for: clean structure and web security basics.

## Features
- Register / log in / log out, with per-user notes (create, edit, delete, search)
- **Security:** `password_hash` / `password_verify`, PDO prepared statements (no SQL injection), output escaping on every value (no XSS), CSRF tokens on every POST, session ID regeneration on login, `HttpOnly` + `SameSite` cookies
- **Authorization:** every note query includes the owner's id, so users can't read or change each other's notes
- Front-controller routing, simple template system, repository classes, PHP 8 features (`match`, constructor promotion, `never`, strict types)
- Dependency-free test runner

## Run
Requires PHP 8.1+ with the `pdo_sqlite` extension.
```bash
php -S localhost:8000 -t public
# open http://localhost:8000
```

## Test
```bash
php tests/run.php
```

## Structure
```text
public/index.php        front controller (routing, sessions, CSRF gate)
src/Database.php        PDO connection + schema
src/UserRepository.php  register / verify users
src/NoteRepository.php  note CRUD, always scoped to the owner
src/Csrf.php            token generate / check
src/helpers.php         e() escaping, render(), redirect()
templates/              layout + login, register, notes views
tests/run.php           tests
```
