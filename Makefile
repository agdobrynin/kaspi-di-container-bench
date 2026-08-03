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

.PHONY: fixtures-clean
fixtures-clean-cmd := find src/Services -name "*.php" -exec rm -f {} \;

fixtures-clean:
	$(fixtures-clean-cmd)

.PHONY: vendor-clean
vendor-clean-v4.5.0-cmd := rm -rf $(working-dir-v4.5.0)/vendor/ $(working-dir-v4.5.0)/composer.lock
vendor-clean-v4.x-dev-cmd := rm -rf $(working-dir-v4.x-dev)/vendor/ $(working-dir-v4.x-dev)/composer.lock

vendor-clean:
	$(vendor-clean-v4.5.0-cmd)
	$(vendor-clean-v4.x-dev-cmd)

clean-all: fixtures-clean vendor-clean

.PHONY: composer-prepare
composer-install-cmd := composer i --no-dev

# --working-dir
composer-prepare:
	$(composer-install-cmd) --working-dir=$(working-dir-v4.5.0)
	$(composer-install-cmd) --working-dir=$(working-dir-v4.x-dev)

.PHONY: bench
bench:
	$(bench-v4.5.0-cmd)
	$(bench-v4.x-dev-cmd)

.PHONY: bench-in-docker
bench-in-docker:
	$(docker-run) sh -c "$(generate-fixtures) && \
	$(composer-install-cmd) --working-dir=$(working-dir-v4.5.0) && \
	$(composer-install-cmd) --working-dir=$(working-dir-v4.x-dev) && \
	php -v && \
	$(bench-v4.5.0-cmd) && \
	$(bench-v4.x-dev-cmd)"
