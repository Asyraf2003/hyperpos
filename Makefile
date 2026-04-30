include mk/push.mk
include mk/hexagonal.mk

push:
	@$(MAKE) git-push

# >>> seed targets >>>
.PHONY: seed seed-1 seed-2 seed-3 1 2 3

seed:
	@if [ -z "$(LEVEL)" ]; then \
		echo "Usage: make seed LEVEL=1|2|3"; \
		exit 1; \
	fi
	@if [ "$(LEVEL)" = "1" ]; then \
		php artisan db:seed --class=Database\\Seeders\\SeedLevel1Seeder; \
	elif [ "$(LEVEL)" = "2" ]; then \
		php artisan db:seed --class=Database\\Seeders\\SeedLevel2Seeder; \
	elif [ "$(LEVEL)" = "3" ]; then \
		php artisan db:seed --class=Database\\Seeders\\SeedLevel3Seeder; \
	else \
		echo "LEVEL tidak valid. Gunakan 1, 2, atau 3."; \
		exit 1; \
	fi

seed-1:
	php artisan db:seed --class=Database\\Seeders\\SeedLevel1Seeder

seed-2:
	php artisan db:seed --class=Database\\Seeders\\SeedLevel2Seeder

seed-3:
	php artisan db:seed --class=Database\\Seeders\\SeedLevel3Seeder

1: seed-1
2: seed-2
3: seed-3
# <<< seed targets <<<

# >>> docs targets >>>
.PHONY: docs-help

docs-help:
	@cat docs/DOCS_HELP.md
# <<< docs targets <<<
