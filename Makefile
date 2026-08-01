SHELL := /bin/sh

docker-run := docker-compose -f docker-compose.yml run -q --rm php
generate-fixtures := php generate.php
working-dir-v4.5.0 := v4.5.0
working-dir-v4.x-dev := v4.x-dev
bench-v4.5.0-cmd := php $(working-dir-v4.5.0)/src/bench.php
bench-v4.x-dev-cmd := php $(working-dir-v4.x-dev)/src/bench.php


all: fixtures composer-prepare bench

.PHONY: fixtures
fixtures:
	$(generate-fixtures)

.PHONY: composer-prepare
# --working-dir
composer-prepare:
	composer i --no-dev --working-dir=$(working-dir-v4.5.0)
	composer dump-autoload -o -a --working-dir=$(working-dir-v4.5.0)
	composer i --no-dev --working-dir=$(working-dir-v4.x-dev)
	composer dump-autoload -o -a --working-dir=$(working-dir-v4.x-dev)

.PHONY: bench
bench:
	$(bench-v4.5.0-cmd)
	$(bench-v4.x-dev-cmd)

.PHONY: bench-in-docker
bench-in-docker:
	$(docker-run) sh -c "$(generate-fixtures) && \
	composer i --no-dev --working-dir=$(working-dir-v4.5.0) && \
	composer dump-autoload -o -a --working-dir=$(working-dir-v4.5.0) && \
	composer i --no-dev --working-dir=$(working-dir-v4.x-dev) && \
	composer dump-autoload -o -a --working-dir=$(working-dir-v4.x-dev) && \
	php -v && \
	$(bench-v4.5.0-cmd) && \
	$(bench-v4.x-dev-cmd)"
