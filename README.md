[![Build Status](https://travis-ci.org/emeraldion/emerails.svg?branch=master)](https://travis-ci.org/emeraldion/emerails)

# EmeRails

In the middle of February 2008, I became unsatisfied with the current homebrew framework I was using for the Emeraldion Lodge, EmePavilion. I felt the need for a solid platform that was object-oriented, fully MVC compliant, and had an ORM layer that relieved me from having to hardcode repetitive query patterns.

I then tried to write from scratch a lightweight clone of [Ruby on Rails](http://www.rubyonrails.org/), by replicating much of their ActiveRecord model class, and a convention-based development model. I struggled against the syntactical and practical limitations of the language I am bound to with my hosting, [PHP](http://www.php.net/), but at the end I had a working framework in less than a month.

EmeRails supports page caching, action filtering and a lot of useful features that save coding time and server load. I am quite satisfied about the result, and I am looking forward to improving it and smudging the edges that are still rough in the future.

## Development

Run tests (limited coverage):

```sh
make test
```

## Docker

If you're familiar with [Docker](https://docs.docker.com/engine/) and [Docker Compose](), you may want to package your app as a Docker image thanks to the included `Dockerfile`:

```
docker build .
```

The included `docker-compose.yml` configuration also allows you to spin up the application locally:

```
docker-compose up --build -d
```

## Documentation

To generate documentation, you will need [Doxygen](https://github.com/doxygen/doxygen.git). You can build it from sources, download a binary, or install it via [homebrew](http://brew.sh/):

```sh
brew install doxygen
```

Once you have Doxygen, you can run the `docs` target:

```sh
make docs
```

## License

[MIT](http://opensource.org/licenses/MIT)

Copyright (c) 2008, 2016 Claudio Procida
http://www.emeraldion.it
