.PHONY: git-push
git-push:
	@echo "Checking status..."
	@git status
	@echo "Adding all changes..."
	@git add .
	@# Menghitung jumlah commit saat ini + 1
	$(eval NEXT_COUNT=$(shell echo $$(($$(git rev-list --count HEAD 2>/dev/null || echo 0) + 1))))
	@echo "Auto-committing as: commit $(NEXT_COUNT)"
	@git commit -m "commit $(NEXT_COUNT)"
	@echo "Pushing to origin main..."
	@git push origin main
	@echo "Done!"
