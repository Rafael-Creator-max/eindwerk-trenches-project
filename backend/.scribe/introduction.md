# Cryptocurrency Tracker API

Welcome to the Cryptocurrency Tracker API documentation. This API provides real-time and historical cryptocurrency data, user authentication, and portfolio tracking capabilities.

## Features

- **Real-time Cryptocurrency Data**: Get the latest prices, market caps, and trading volumes for various cryptocurrencies.
- **Historical Price Data**: Access historical price data with various time intervals.
- **User Authentication**: Secure user registration and authentication using Laravel Sanctum.
- **Portfolio Management**: Users can track their favorite cryptocurrencies.
- **Trending Cryptocurrencies**: Discover trending cryptocurrencies based on market data.

## Authentication

This API uses token-based authentication. To authenticate your requests, include the following header:

```
Authorization: Bearer {your-api-token}
```

## Rate Limiting

- **Public Endpoints**: 60 requests per minute
- **Authenticated Endpoints**: 120 requests per minute

## Base URL

All API endpoints are relative to the base URL:

```
https://api.yourdomain.com/v1
```

## Response Format

All API responses are in JSON format and include appropriate HTTP status codes.

## Error Handling

Errors are returned as JSON objects with the following structure:

```json
{
    "message": "Error description",
    "errors": {
        "field_name": ["Error message"]
    },
    "code": "error_code"
}
```

## Support

For support, please contact [support@yourdomain.com](mailto:support@yourdomain.com)
