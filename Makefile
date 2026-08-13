SHELL := /bin/sh

docker-run := docker-compose -f docker-compose.yml run -q --rm php
generate-fixtures := php generate_fixtures.php
working-dir-v4.5.x := v4.5.x
working-dir-v4.6.x := v4.6.x
working-dir-v4.x-dev := v4.x-dev
bench-v4.5.x-cmd := php $(working-dir-v4.5.x)/src/bench.php
bench-v4.6.x-cmd := php $(working-dir-v4.6.x)/src/bench.php
bench-v4.x-dev-cmd := php $(working-dir-v4.x-dev)/src/bench.php

bench-in-hosted: fixtures composer-prepare bench-in-hosted-only-bench

.PHONY: fixtures
fixtures:
	$(generate-fixtures)

.PHONY: clean-fixtures
fixtures-clean-cmd := find Fixtures -name "*.php" -exec rm -f {} \;

clean-fixtures:
	$(fixtures-clean-cmd)

.PHONY: clean-var-dir
clean-var-dir-cmd := find var/ -type f ! -name ".git-keep" -delete

clean-var-dir:
	$(clean-var-dir-cmd)

.PHONY: clean-vendor
vendor-clean-v4.5.x-cmd := rm -rf $(working-dir-v4.5.x)/vendor/ $(working-dir-v4.5.x)/composer.lock
vendor-clean-v4.6.x-cmd := rm -rf $(working-dir-v4.6.x)/vendor/ $(working-dir-v4.6.x)/composer.lock
vendor-clean-v4.x-dev-cmd := rm -rf $(working-dir-v4.x-dev)/vendor/ $(working-dir-v4.x-dev)/composer.lock

clean-vendor:
	$(vendor-clean-v4.5.x-cmd)
	$(vendor-clean-v4.6.x-cmd)
	$(if $(DEV), $(vendor-clean-v4.x-dev-cmd))

clean-all: clean-var-dir clean-fixtures clean-vendor

.PHONY: composer-prepare
composer-install-cmd := composer i --no-dev -n -q

# --working-dir
composer-prepare:
	$(composer-install-cmd) --working-dir=$(working-dir-v4.5.x)
	$(composer-install-cmd) --working-dir=$(working-dir-v4.6.x)
	$(if $(DEV), $(composer-install-cmd) --working-dir=$(working-dir-v4.x-dev))

.PHONY: bench-in-hosted-only-bench
bench-in-hosted-only-bench:
	@$(bench-v4.5.x-cmd)
	@$(bench-v4.6.x-cmd)
	$(if $(DEV), @$(bench-v4.x-dev-cmd))

.PHONY: bench-in-docker
bench-in-docker:
	@$(docker-run) sh -c "$(generate-fixtures)"

	@$(docker-run) sh -c "$(composer-install-cmd) --working-dir=$(working-dir-v4.5.x) && \
	$(bench-v4.5.x-cmd)"

	@$(docker-run) sh -c "$(composer-install-cmd) --working-dir=$(working-dir-v4.6.x) && \
	$(bench-v4.6.x-cmd)"

	$(if $(DEV),  @$(docker-run) sh -c "$(composer-install-cmd) --working-dir=$(working-dir-v4.x-dev) && \
		$(bench-v4.x-dev-cmd)")
