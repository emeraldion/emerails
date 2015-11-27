.PHONY: docs test install update

update:
	composer update
install:
	composer install
test: install
	phpunit test/**/*.php
docs:
	doxygen Doxyfile
