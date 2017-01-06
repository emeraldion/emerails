[![Build Status](https://travis-ci.org/emeraldion/emerails.svg?branch=master)](https://travis-ci.org/emeraldion/emerails)

# EmeRails

EmeRails is a PHP web application framework loosely inspired to
[Ruby on Rails](http://www.rubyonrails.org).
It has a <acronym title="Model View Controller">MVC</acronym> architecture, an
<acronym title="Object Relational Mapping">ORM</acronym> layer that mimics ActiveRecord, and separates
presentation from business logic quite nicely, prioritizing conventions over configuration.

It supports templating, page caching, action filtering, and a lot of useful features out of the box
that save coding time and server load. Similarly to Rails, EmeRails has a `generate.php` script that
can quickly get you up and running, creating your controllers, models, and views in no time.

EmeRails comes with default support for MySQL via `mysql` (deprecated) and `mysqli` extensions, is
[continuously tested](https://travis-ci.org/emeraldion/emerails) on PHP `5.6`, `7.1`, and `nightly`.

## Generator

EmeRails comes with a generator script that creates controllers, views, and models with no code:

```sh
scripts/generate.php
Usage: generate.php controller controller_name [action1 [action2 ...]]
       generate.php model model_name [field1 [type1 [field2 [type2 ...]]]]
```

In order to generate a controller and its views, run the generator script as follows:

```sh
scripts/generate.php controller foo bar baz
```

This will create a controller class `FooController` with the default `FooController::index` action,
and two actions, `FooController::bar` and `FooController::baz`.
It will also generate the views `index`, `bar`, and `baz`.

In order to generate a model with a list of fields and types, run the generator script as follows:

```sh
scripts/generate.php model foo bar int baz float
```

This will create a model class `Foo` with two fields, `bar` of type `int`, and `baz` of type `float`.
It will also create the backing table in the DB.

## Contributing

Thank you for your interest in EmeRails! Feel free to open issues or submit a PR.
See the [Contributing Guidelines](https://github.com/emeraldion/emerails/blob/master/CONTRIBUTING.md)
for detailed instructions.


## Development

EmeRails is a PHP web application. If you are unsure what to do, follow these steps:

### Installing a local MySQL server

For development, it's best to use a
[local MySQL server](https://dev.mysql.com/doc/mysql-getting-started/).
I use [MAMP](https://www.mamp.info/) on Mac OS X, but you can also run MySQL server in
[a Docker container](https://hub.docker.com/r/mysql/mysql-server/).

### Install dependencies

```sh
make install
```

### Create test DB

This command will create a test MySQL DB: 

```sh
make create_test_db
```

Note the script assumes there is a `mysql` command in the `PATH`. It also assumes the database user is
`root` and will prompt for the password. If you want to use another user, you have to edit `Makefile`.

### Run tests

Run tests (limited coverage):

```sh
php_env=test make test
```

## Docker

If you're familiar with [Docker](https://docs.docker.com/engine/) and [Docker Compose](), you may want
to package your app as a Docker image thanks to the included `Dockerfile`:

```sh
make docker-build
```

This goal builds the app as the `emerails-app` image; to easily run the image in a container:

```sh
make docker-run
```

This goal runs a `mysql` DB container, spins up an app container, links them, and forwards the app container's port `80` to local port `8080`. You can then hit the app opening your browser on `http://localhost:8080`.

To stop the app and `mysql` containers, run the goal:

```sh
make docker-stop
```

There's also a handy goal to cleanup when you're done with Docker images:

```sh
make docker-clean
```

The included `docker-compose.yml` configuration also allows you to spin up the application locally:

```sh
docker-compose up --build -d
```

## Documentation

To generate documentation, you will need [Doxygen](https://github.com/doxygen/doxygen.git).
You can build it from sources, download a binary, or install it via [homebrew](http://brew.sh/):

```sh
brew install doxygen
```

Once you have Doxygen, you can run the `docs` target:

```sh
make docs
```

## License

[MIT](http://opensource.org/licenses/MIT)

Copyright (c) 2008, 2017 Claudio Procida

