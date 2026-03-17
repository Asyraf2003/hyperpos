echo
echo "== VERIFY SYNTAX =="
php -l app/Application/Reporting/DTO/EmployeeDebtSummaryRow.php
php -l app/Application/Reporting/Services/EmployeeDebtSummaryBuilder.php
php -l app/Application/Reporting/Services/EmployeeDebtReportingReconciliationService.php
php -l app/Ports/Out/Reporting/EmployeeDebtReportingSourceReaderPort.php
php -l app/Adapters/Out/Reporting/DatabaseEmployeeDebtReportingSourceReaderAdapter.php
php -l app/Application/Reporting/UseCases/GetEmployeeDebtSummaryHandler.php
php -l app/Providers/HexagonalServiceProvider.php
php -l tests/Feature/Reporting/GetEmployeeDebtSummaryFeatureTest.php

echo
echo "== RUN EMPLOYEE DEBT REPORT TEST =="
php artisan test tests/Feature/Reporting/GetEmployeeDebtSummaryFeatureTest.php

echo
echo "== RUN EXISTING REPORTING TESTS =="
php artisan test tests/Feature/Reporting/GetOperationalExpenseSummaryFeatureTest.php
php artisan test tests/Feature/Reporting/GetTransactionSummaryPerNoteFeatureTest.php
php artisan test tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php
php artisan test tests/Feature/Reporting/ReportingReadModelContractFeatureTest.php

echo
echo "== OPTIONAL BUNDLE CHECK =="
php artisan test tests/Feature/Reporting
