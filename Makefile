#                                   _ __
#   ___  ____ ___  ___  _________ _(_) /____
#  / _ \/ __ `__ \/ _ \/ ___/ __ `/ / / ___/
# /  __/ / / / / /  __/ /  / /_/ / / (__  )
# \___/_/ /_/ /_/\___/_/   \__,_/_/_/____/
#
# (c) Claudio Procida 2008-2024
#

.PHONY: docs test install update

update:
	composer update
	yarn upgrade
install:
	composer install
	yarn install
create_test_db:
	mysql -u root -p < schemas/emerails_test.sql
test: install
	vendor/bin/phpunit --test-suffix=.test.php test/unit --color auto --coverage-html coverage
test-ci: install
	vendor/bin/phpunit --test-suffix=.test.php test/unit --color auto --coverage-clover build/logs/clover.xml
docs:
	doxygen Doxyfile
format:
	yarn format
format-strings:
	scripts/emerails_localize format --recursive

# Localization goals
check-strings:
	scripts/emerails_localize check --recursive --strict

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

# Security goals
audit:
	composer audit
	yarn audit