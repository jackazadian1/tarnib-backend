# Tarnib Backend

This repository contains the Laravel backend for the Tarnib card game project. It provides APIs and handles the game logic for the Tarnib web application.

## Table of Contents

-   [Installation](#installation)
-   [Configuration](#configuration)
-   [API Endpoints](#api-endpoints)
-   [Websockets](#websockets)
-   [License](#license)

## Installation

1. Clone the repository:

```
git clone https://github.com/jackazadian1/tarnib-backend.git
```

2. Change directory to the project folder:

```
cd tarnib-backend
```

3. Install dependencies using Composer:

```
composer install
```

4. Create a `.env` file by copying the `.env.example`:

```
cp .env.example .env
```

5. Generate an application key:

```
php artisan key:generate
```

6. Set up your database and update the `.env` file with your database configuration.

7. Run the database migrations:

```
php artisan migrate
```

8. Start the Laravel development server:

```
php artisan serve
```

Now, the Tarnib backend should be up and running at `http://localhost:8000`.

## Configuration

Update the `.env` file with your environment-specific configurations, such as database credentials, application URL, and other settings.

## API Endpoints

The Tarnib backend provides several API endpoints for managing game state and user interactions. For a complete list of available endpoints, refer to the Laravel routes file (`routes/api.php`).

## Websockets

The Tarnib backend uses Laravel Websockets to handle real-time updates and communication between clients. Make sure to configure your Websockets settings in the `.env` file.

To start the Websockets server, run the following command:

```
php artisan websockets:serve
```

## License

The Tarnib Backend is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
