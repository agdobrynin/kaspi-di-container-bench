SHELL := /bin/sh

docker-run := docker-compose -f docker-compose.yml run -q --rm php
generate-fixtures := php generate.php
bench-v45-cmd := php v45/src/bench.php
bench-v46-dev-cmd := php v46-dev/src/bench.php


all: fixtures composer-prepare bench

.PHONY: fixtures
fixtures:
	$(generate-fixtures)

.PHONY: composer-prepare
# --working-dir
composer-prepare:
	composer i --no-dev --working-dir=v45
	composer dump-autoload -o -a --working-dir=v45
	composer i --no-dev --working-dir=v46-dev
	composer dump-autoload -o -a --working-dir=v46-dev

.PHONY: bench
bench:
	$(bench-v45-cmd)
	$(bench-v46-dev-cmd)

.PHONY: bench-in-docker
bench-in-docker:
	$(docker-run) sh -c "$(generate-fixtures) && \
	composer i --no-dev --working-dir=v45 && composer dump-autoload -o -a --working-dir=v45 && \
	composer i --no-dev --working-dir=v46-dev && composer dump-autoload -o -a --working-dir=v46-dev && \
	php -v && \
	$(bench-v45-cmd) && \
	$(bench-v46-dev-cmd)"
