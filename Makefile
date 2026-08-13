# ═══════════════════════════════════════════════════════════════════════
# UTILIDADES
# ═══════════════════════════════════════════════════════════════════════

# Corregir permisos de archivos creados por Docker
fix-permissions:
	@echo "Corrigiendo permisos del proyecto..."
	sudo chown -R $$(whoami):$$(whoami) backend
	find backend -type d -not -path "backend/storage*" -not -path "backend/bootstrap/cache*" -exec chmod 775 {} +
	find backend -type f -exec chmod 664 {} +
	find backend -name "*.sh" -exec chmod +x {} +
	sudo chown -R www-data:www-data backend/storage backend/bootstrap/cache
	sudo chmod -R 775 backend/storage backend/bootstrap/cache

migrate:
	php artisan migrate

# Comando base de Docker
ARTISAN = docker compose exec app php artisan

# Ejecutar cualquier comando de artisan
# Uso: make artisan cmd="make:class Services/Finanzas/FinancialMovementService"
artisan:
	@$(ARTISAN) $(cmd)
