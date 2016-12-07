.PHONY: docs test install update

update:
	composer update
install:
	composer install
create_test_db:
	mysql -u root -p < schemas/emerails_test.sql
test: install
	phpunit --test-suffix=.test.php test/unit
docs:
	doxygen Doxyfile
