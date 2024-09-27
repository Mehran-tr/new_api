
# My  API Platform Project

This project is a RESTful API built with [Symfony](https://symfony.com/), [API Platform](https://api-platform.com/), and [JWT Authentication](https://github.com/lexik/LexikJWTAuthenticationBundle). It includes user authentication, company management, and user management features. The project also uses PHPUnit for automated testing.

## Features

- REST API built with Symfony and API Platform
- JWT-based authentication for secure API access
- Fully tested with PHPUnit
- CORS support for frontend integration

## Requirements

- PHP 8.2 or higher
- PostgreSQL (or any other supported database)
- Composer
- Symfony CLI (optional)

## Setup Instructions

### 1. Clone the Repository

```bash
git clone https://github.com/Mehran-tr/new_api.git
cd new_api
```

### 2. Install Dependencies

Install PHP and JavaScript dependencies using Composer and npm/yarn:

```bash
composer install
```

### 3. Setup Environment Variables

Create a `.env.local` file in the root directory and configure your database and JWT settings:

```bash
cp .env .env.local
```

Update the following variables:

```env
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/my_database"
CORS_ALLOW_ORIGIN="*"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

### 4. Generate JWT Keys

Generate the public and private keys for JWT Authentication:

```bash
mkdir -p config/jwt
openssl genpkey -algorithm RSA -out config/jwt/private.pem -aes256
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

### 5. Create the Database

Create and migrate the database:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

(Optional) Load initial data (fixtures) if you have any:

```bash
php bin/console doctrine:fixtures:load
```

### 6. Running the Application

Start the Symfony development server:

```bash
symfony server:start
```

Alternatively, you can use the PHP built-in server:

```bash
php -S localhost:8000 -t public
```

Access the API in your browser at `http://localhost:8000`.

### 7. Running Tests

The project includes PHPUnit tests. To run the tests:

```bash
php bin/phpunit
```

Make sure to set up the testing environment variables in `.env.test.local`:

```env
DATABASE_URL="postgresql://username:password@127.0.0.1:5432/test_database"
CORS_ALLOW_ORIGIN="*"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_passphrase
```

### 8. API Endpoints

| Endpoint              | Method | Description                      |
| --------------------- | ------ | -------------------------------- |
| `/api/login`          | `POST` | User authentication (JWT)        |
| `/api/companies`      | `GET`  | Get list of companies            |
| `/api/companies`      | `POST` | Create a new company             |
| `/api/companies/{id}` | `GET`  | Get company details              |
| `/api/users`          | `GET`  | Get list of users                |
| `/api/users`          | `POST` | Create a new user                |
| `/api/users/{id}`     | `GET`  | Get user details                 |

### 9. CORS Configuration

If you encounter CORS issues, make sure to update your `.env` file with the correct `CORS_ALLOW_ORIGIN` value, such as:

```env
CORS_ALLOW_ORIGIN="*"
```

### 10. JWT Authentication

To authenticate users, you will need to log in using the `/api/login` endpoint to get a JWT token. Pass the token in the `Authorization` header with the `Bearer` scheme for all protected routes.

Example:

```bash
Authorization: Bearer <your-jwt-token>
```

### 11. Frontend Setup (Optional)

If you have a frontend, you can configure it to communicate with the API. Ensure the CORS policy is properly configured in your `.env` file.

---

## Authors

- Mehran Taheri <mehran.taheri.t@gmail.com>

## License

This project is licensed under the MIT License.
