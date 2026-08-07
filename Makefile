SHELL := /bin/sh

docker-run := docker-compose -f docker-compose.yml run -q --rm php
generate-fixtures := php generate.php
working-dir-v4.5.0 := v4.5.0
working-dir-v4.x-dev := v4.x-dev
bench-v4.5.0-cmd := php $(working-dir-v4.5.0)/src/bench.php
bench-v4.x-dev-cmd := php $(working-dir-v4.x-dev)/src/bench.php


bench-hosted: fixtures composer-prepare run-bench-only

.PHONY: fixtures
fixtures:
	$(generate-fixtures)

.PHONY: clean-fixtures
fixtures-clean-cmd := find src/Services -name "*.php" -exec rm -f {} \;

clean-fixtures:
	$(fixtures-clean-cmd)

.PHONY: clean-vendor
vendor-clean-v4.5.0-cmd := rm -rf $(working-dir-v4.5.0)/vendor/ $(working-dir-v4.5.0)/composer.lock
vendor-clean-v4.x-dev-cmd := rm -rf $(working-dir-v4.x-dev)/vendor/ $(working-dir-v4.x-dev)/composer.lock

clean-vendor:
	$(vendor-clean-v4.5.0-cmd)
	$(vendor-clean-v4.x-dev-cmd)

clean-all: clean-fixtures clean-vendor

.PHONY: composer-prepare
composer-install-cmd := composer i --no-dev

# --working-dir
composer-prepare:
	$(composer-install-cmd) --working-dir=$(working-dir-v4.5.0)
	$(composer-install-cmd) --working-dir=$(working-dir-v4.x-dev)

.PHONY: run-bench-only
run-bench-only:
	$(bench-v4.5.0-cmd)
	$(bench-v4.x-dev-cmd)

.PHONY: bench-in-docker
bench-in-docker:
	@$(docker-run) sh -c "$(generate-fixtures)"
	@$(docker-run) sh -c "$(composer-install-cmd) --working-dir=$(working-dir-v4.5.0) && \
	$(bench-v4.5.0-cmd)"

	@$(docker-run) sh -c "$(composer-install-cmd) --working-dir=$(working-dir-v4.x-dev) && \
	$(bench-v4.x-dev-cmd)"
