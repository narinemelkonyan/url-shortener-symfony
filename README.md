# URL Shortener

Asynchronous URL shortener. The API takes a long URL and returns a short one.
Code generation runs in the background via a queue.

## Stack

PHP 8.4, Symfony 8.1, MySQL 8.4, Symfony Messenger, Docker, nginx.

## Requirements

- Docker
- Docker Compose

## Setup

```bash
cp .env.example .env
make up
make migration-migrate
```

App runs at `http://localhost:8080`.

## API

### Shorten or fetch a link

GET /api/shortlink?url={original_url}

- Existing URL — returns the short code (**200**).
- New URL — queues a job, returns "processing" (**202**). Retry to get the code.
- Invalid URL — **400**.

```bash
curl -i "http://localhost:8080/api/shortlink?url=https://example.com/long/path"
# 202 -> {"status":"processing", ...}
# retry: 200 -> {"status":"ready","short_code":"GpEg7yW"}
```

### Follow a short link

GET /{code}

Redirects to the original URL (**301**), or **404** if the code is unknown or
not yet generated.

```bash
curl -i "http://localhost:8080/GpEg7yW"
# 301 -> Location: https://example.com/long/path
```

## Tests

```bash
make test-setup       # one-time: create and migrate the test database
make test             # run all tests (unit + functional)
make test-unit        # unit tests only
make test-functional  # functional tests only
```