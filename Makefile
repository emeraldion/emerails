.PHONY: docs test install update

update:
	composer update
install:
	composer install
test: install
	phpunit test
docs:
	doxygen Doxyfile
