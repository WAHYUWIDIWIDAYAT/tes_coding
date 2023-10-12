<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Getting started and How to Run

### Launch the starter project

_(Assuming you've [installed Laravel](https://laravel.com/docs/5.5/installation))_

Fork this repository, then clone your fork, and run this in your newly created directory:

```bash
composer install
```

Next you need to make a copy of the `.env.example` file and rename it to `.env` inside your project root.
- example of .env database of my .env
- using PostgreSQL
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=edwin
DB_USERNAME=postgres
DB_PASSWORD=12345

```
- using MySQL
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=edwin
DB_USERNAME=root
DB_PASSWORD=
```

Run the following command to generate your app key:

```
php artisan key:generate
```
if you have backup/export database then just run "php artisan server" but you have another way like migration you can follow this step
- make database and add your database to .env
then

```
php artisan migrate
```
- after run migration, the table is automatic created, and you can run the seeder for dummy data

```
php artisan db:seed
```

Then start your server:

```
php artisan serve
```


Your Laravel starter project is now up and running!



## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.
