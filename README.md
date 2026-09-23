# Daymark To-Do List System

A responsive Laravel-based task management system for creating, organizing, searching, completing, editing, and deleting personal tasks. Daymark includes task due dates, priority levels, task filters, a responsive workspace layout, and a mobile-friendly navigation dock.

## Project Information

- **Developer:** JOHN LOYD SERAPION
- **Section:** BSIT 4-3
- **Framework:** Laravel 12
- **Database:** MySQL
- **GitHub Repository:** https://github.com/KaiiGritt/to-do-list.git

## Features

- Create new tasks with notes, due dates, and priority levels
- Mark tasks as complete or reopen them
- Edit and delete tasks
- Search tasks by title or notes
- Filter tasks by My Tasks, Today, Upcoming, and Completed
- Responsive desktop and mobile interface
- Database-backed task storage through Laravel migrations

## Software Requirements

Install the following software before setting up the project:

- PHP 8.2 or higher
- Composer 2.x
- Node.js 20 or higher and npm
- MySQL 8.0 or higher, or MariaDB 10.4 or higher
- Git
- A web browser

Verify the installations:

```bash
php --version
composer --version
node --version
npm --version
mysql --version
```

## Laravel Installation Instructions

1. Clone the repository:

```bash
git clone https://github.com/KaiiGritt/to-do-list.git
cd to-do-list
```

2. Install PHP dependencies:

```bash
composer install
```

3. Create the environment file:

```bash
copy .env.example .env
```

For macOS or Linux, use:

```bash
cp .env.example .env
```

4. Generate the Laravel application key:

```bash
php artisan key:generate
```

5. Install frontend dependencies:

```bash
npm install
```

## Database Configuration

Create a MySQL database with the following name:

```text
to_do_list_system
```

Update the database values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=to_do_list_system
DB_USERNAME=root
DB_PASSWORD=
```

Set `DB_PASSWORD` to your local MySQL password if your MySQL installation requires one.

## Database Import Instructions

### Recommended: Import Using Laravel Migrations

This project includes migrations for the application tables, including the tasks table. After configuring `.env`, run:

```bash
php artisan migrate
```

To reset and recreate all database tables during development:

```bash
php artisan migrate:fresh
```

To migrate and seed the database, if seed data is available:

```bash
php artisan migrate --seed
```

### Optional: Import an SQL Dump

If you have an SQL backup file such as `database.sql`, create the database first and import it with:

```bash
mysql -u root -p to_do_list_system < database.sql
```

On Windows PowerShell, you can use:

```powershell
Get-Content .\database.sql | mysql -u root -p to_do_list_system
```

This repository uses Laravel migrations as the primary database setup method. No separate SQL dump is required for a fresh installation.

## Commands Needed to Run the Project

### Start the Laravel development server

```bash
php artisan serve
```

Open the application at:

```text
http://127.0.0.1:8000
```

### Compile frontend assets

For a production build:

```bash
npm run build
```

For frontend development with automatic rebuilding:

```bash
npm run dev
```

### Run Laravel and frontend development services together

```bash
composer run dev
```

### Run tests

```bash
php artisan test
```

Or use the configured Composer script:

```bash
composer run test
```

### Clear application caches

```bash
php artisan optimize:clear
```

## Quick Setup

After cloning the repository, the following commands provide the usual setup flow:

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan migrate
npm run build
php artisan serve
```

For macOS or Linux, replace `copy .env.example .env` with `cp .env.example .env`.

## Repository

[View the project on GitHub](https://github.com/KaiiGritt/to-do-list.git)
