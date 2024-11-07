# RTP Payment Verification

This is a simple PHP application for verifying RTP (Real-Time Payments) status using an API. It generates a JWT token for authentication and makes API requests to check the payment status.

## Requirements

- Docker

## Getting started

1. Clone this repository

   ```
   git clone https://github.com/Dubbie/rtp-test-app
   cd rtp-test-app
   ```

2. Add `.env` file to root (Outside the `src` folder.)

   ```
   RTP_API_URL="your-api-url"
   RTP_MERCHANT_ID="your-merchant-id"
   ```

3. Copy your certificates to the `src/certs/` folders. Make sure the folder contains:

   - `private.pem` (your private key)

4. Build and run Docker container

   ```
   docker-compose up --build
   ```

5. Access the service in your browser or make a GET request to `http://localhost:8000/?endToEndId=<id>` to check the payment status.

## Files

- **src:** Contains the ain PHP code for the service
- **.env:** Holds environment variables for API URL and Merchant ID.
- **Dockerfile:** Defines the environment for running the service in Docker.
- **docker-compose.yml:** Manage Docker containers
