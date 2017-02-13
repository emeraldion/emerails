#
#	Project EmeRails - Codename Ocarina
#
#	Copyright (c) 2008, 2017 Claudio Procida
#	http://www.emeraldion.it
#
#
.PHONY: docs test install update

update:
	composer update
install:
	composer install
create_test_db:
	mysql -u root -p < schemas/emerails_test.sql
test: install
	phpunit --test-suffix=.test.php test/unit --coverage-html coverage
test-ci: install
	vendor/bin/phpunit --test-suffix=.test.php test/unit --coverage-html coverage
docs:
	doxygen Doxyfile

# Docker goals
docker-build: install
	docker build -t emerails-app .
docker-run:
	docker run --name emerails-mysql -e MYSQL_ROOT_PASSWORD=root -d mysql:5.5
	docker run --name emerails --link emerails-mysql:mysql -p 8080:80 -d emerails-app
docker-stop:
	docker stop emerails
	docker stop emerails-mysql
	docker rm emerails
	docker rm emerails-mysql
docker-clean:
	docker rmi emerails-app
# docker-publish:
# 	docker build -t emeraldion/emerails:1.1 .
# 	docker push emeraldion/emerails:1.1
