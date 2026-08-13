SHELL := /bin/sh

.DEFAULT_GOAL := no_default

define HELP_TEXT
******************************
*                            *
*   Run make with target:    *
*     make bench-in-hosted   *
*     make bench-in-docker   *
*                            *
******************************
endef
export HELP_TEXT
no_default:
	@echo "$$HELP_TEXT"
	@exit 1

docker-run := docker-compose -f docker-compose.yml run -q --rm php
generate-fixtures := php generate_fixtures.php

ifeq ($(DEV),)
	working-dirs := v4.5.x v4.6.x
else
	working-dirs := v4.5.x v4.6.x v4.x-dev
endif

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
vendor-clean-cmd := find . -maxdepth 2 -type d -name "vendor" -exec rm -rf {} + && find . -maxdepth 2 -type f -name "composer.lock" -delete

clean-vendor:
	$(vendor-clean-cmd)

clean-all: clean-var-dir clean-fixtures clean-vendor

.PHONY: composer-prepare
composer-install-cmd := composer i --no-dev -n

# --working-dir
composer-prepare:
	@for dir in "." $(working-dirs); do \
  		$(composer-install-cmd) --working-dir=$$dir; \
	done

.PHONY: bench-in-hosted-only-bench
bench-in-hosted-only-bench:
	@for dir in $(working-dirs); do \
	    php $$dir/src/index.php; \
	done

.PHONY: bench-in-docker
bench-in-docker:
	@$(docker-run) sh -c "$(generate-fixtures)"
	# install composer dependencies and optimization
	@for dir in "." $(working-dirs); do \
		$(composer-install-cmd) --working-dir=$$dir; \
	done

	#run benchmarks
	@for dir in $(working-dirs); do \
		$(docker-run) sh -c "php $$dir/src/index.php"; \
	done

.PHONY: bench-in-hosted
bench-in-hosted: fixtures composer-prepare bench-in-hosted-only-bench

.PHONY: h
h: bench-in-hosted

.PHONY: d
d: bench-in-docker

.PHONY: b
b: bench-in-hosted-only-bench
