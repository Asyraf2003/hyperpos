.PHONY: git-push
git-push:
	@echo "Checking status..."
	@git status
	@echo "Adding all changes..."
	@git add .
	@read -p "Commit message: " msg; \
	git commit -m "$$msg"
	@echo "Pushing to origin main..."
	@git push origin main
	@echo "Done!"
