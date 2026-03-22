.PHONY: git-push
git-push:
	@echo "Checking status..."
	@git status
	@echo "Adding all changes..."
	@git add .
	$(eval NEXT_COUNT=$(shell echo $$(($$(git rev-list --count HEAD 2>/dev/null || echo 0) + 1))))
	@echo "Attempting auto-commit as: commit $(NEXT_COUNT)"
	@# Menambahkan '|| true' agar Makefile tidak berhenti jika tidak ada yang perlu di-commit
	@git commit -m "commit $(NEXT_COUNT)" || echo "Nothing new to commit, moving to push..."
	@echo "Pushing to origin main..."
	@git push origin main
	@echo "Done!"
