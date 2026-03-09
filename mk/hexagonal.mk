.PHONY: test test-unit test-feature test-arch audit-hex check

test:
	php artisan test

test-unit:
	php artisan test tests/Unit

test-feature:
	php artisan test tests/Feature

test-arch:
	php artisan test tests/Arch

audit-hex:
	php scripts/audit-hex.php

check: audit-hex test
