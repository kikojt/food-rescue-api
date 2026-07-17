# Food Rescue API

A RESTful API for a food rescue platform, developed as part of a university coursework assignment. The API provides authentication, partner management, daily offers, and purchase operations for client applications.

## Features

### Authentication

- User registration
- Secure login
- Token-based authentication
- Logout

### Entities

- Retrieve active partners
- Retrieve partners with available offers
- View partner details

### Offers

- Retrieve available offers
- Retrieve offers by partner
- Purchase offers

### Purchases

- View user purchases
- Cancel purchases (same-day only)

## Technologies

- PHP
- Slim Framework 4 Skeleton Application
- MySQL
- PDO
- JSON
- REST API

## Security

- Token-based authentication
- Authorization middleware
- Password hashing
- Prepared Statements (PDO)
- Input validation

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/users/` | Register user |
| PATCH | `/api/users/` | Authenticate user |
| PATCH | `/api/users/logout.php` | Logout |
| GET | `/api/entities/` | Active partners |
| GET | `/api/entities/withoffers/` | Partners with offers |
| GET | `/api/entities/{id}` | Partner details |
| GET | `/api/entities/offers/{id}` | Partner offers |
| GET | `/api/entities/offers/` | All available offers |
| POST | `/api/entities/offers/buy/` | Purchase offer |
| GET | `/api/users/purchases/` | Purchase history |
| PATCH | `/api/users/purchases/` | Cancel purchase |

## Testing

The API can be tested using:

- REST Client (VS Code)
- Postman

## Copyright

© 2026 Francisco Rodrigues. All rights reserved.

This project was developed as part of a university coursework assignment and is published for portfolio purposes only.

The source code, documentation, and other project materials may not be copied, modified, redistributed, or used without the author's prior written permission.
