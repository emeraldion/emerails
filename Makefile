.PHONY: docs test install update

update:
	composer update
install:
	composer install
test: install
	phpunit --test-suffix=.test.php test/unit
docs:
	doxygen Doxyfile
