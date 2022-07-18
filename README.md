# api-example
Example simple API written in PHP with Laravel

# How to run?

```bash
make prepare
```

**Warning**: Sometimes mysql gets restart after run, then command 'refresh-database' fails. If it failed please manually run "make refresh-database".

# Commands to manage environment

Build containers:
```bash
make build
```

Run app:
```bash
make up
```

Stop app:
```bash
make down
```

Refresh database:
```bash
make refresh-database
```

Remove app:
```bash
make rm
```

# How to run unit tests:
```bash
make test
```
