include mk/push.mk
include mk/hexagonal.mk

push:
	@$(MAKE) git-push

pushc: push
	@clear

# >>> docs targets >>>
.PHONY: docs-help

docs-help:
	@cat docs/DOCS_HELP.md
# <<< docs targets <<<
