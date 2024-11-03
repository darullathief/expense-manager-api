# Expense Manager API

## Overview
This project is a RESTful API built with Laravel and MariaDb that allows users to manage their expenses. This solution is based off the [Roadmap.sh](https://roadmap.sh/projects/expense-tracker-api) Expense Tracker API project.

## Prerequisites
This project requires :
- PHP 8.2 or higher
- Laravel 11.x or higher

## How to run

- Clone this repo using SSH `git@github.com:darullathief/expense-manager-api.git` or HTTPS `https://github.com/darullathief/expense-manager-api.git`
- download required modules, `composer install`
- set your environment, copy from `.env.example`  into `.env`
- clear your config cache first, `php artisan config:clear`
- set your application key, `php artisan key:generate`
- set your config, `php artisan config:cache`
- Set your virtual hosts, point it to `public` folder.
- You're good to go `php artisan serve`

## Documentacions
API documentation can be viewed via: `http://your_url/docs/api`
