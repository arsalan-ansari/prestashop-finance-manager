# PrestaShop Finance Manager Pro (v1.0.3)

> **Enterprise Cashbook, Offline Sales & Expense Tracking Module for PrestaShop 8.x & 9.x**  
> Engineered by **Arsalan Ansari** (Senior PrestaShop & Full-Stack Architect)

[![PrestaShop](https://img.shields.io/badge/PrestaShop-8.x%20%7C%209.x-251b5b?style=for-the-badge&logo=prestashop)](https://www.prestashop.com/)
[![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2%20%7C%208.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-6.4%20LTS-000000?style=for-the-badge&logo=symfony)](https://symfony.com/)
[![Architecture](https://img.shields.io/badge/Architecture-Modular%20Admin%20Controllers-success?style=for-the-badge)](#technical-architecture)

---

## 📌 Overview

**Finance Manager Pro** is a comprehensive internal accounting and financial control module designed specifically for hybrid brick-and-mortar / e-commerce merchants running **PrestaShop 8 and PrestaShop 9**.

While standard PrestaShop natively tracks online payment gateways, store owners frequently struggle with offline point-of-sale (POS) revenue, cash deposits, store operational expenses, and manual order reconciliation. This module integrates a dedicated double-entry cashbook ledger, categorization matrices, order-linking AJAX search, and printable audit reports directly into the PrestaShop Back Office.

---

## 🏗️ Technical Architecture & Key Highlights

This module was engineered adhering strictly to modern PrestaShop standards, emphasizing clean OOP separation, maintainability, and seamless upgrade paths.

### 1. Modern Symfony & Translation Compliance
* Built using modern Symfony-based translation domains (`$this->trans('...', [], 'Modules.Financemanager.Admin')`) rather than legacy legacy `l()` helpers.
* Fully prepared and verified on **PrestaShop 9** with Symfony 6.4 LTS service containers and strict typing.

### 2. Multi-Controller Back-Office Routing
Separated across dedicated `ModuleAdminController` classes to adhere to single-responsibility principles:
* `AdminFinanceDashboardController`: Real-time financial KPI cards (Income, Expense, Net Balance).
* `AdminFinanceCashbookController`: Primary transaction ledger with real-time balance calculations.
* `AdminFinanceCategoriesController`: Dynamic hierarchy for expense and income classifications.
* `AdminFinanceIncomeController` & `AdminFinanceExpenseController`: Granular operational entry logging.
* `AdminFinanceReportsController`: Range-based reporting engine with printable print views.
* `AdminFinanceAjaxController`: Asynchronous endpoint handling order queries and real-time state mutations.

### 3. Asynchronous Order Search & Linkage
* Integrates an AJAX-powered lookup (`order-search.js` + `AdminFinanceAjaxController`) allowing store managers to link any expense or cash deposit directly to existing PrestaShop customer orders in real time.

### 4. Database Schema Versioning & Automated Upgrades
Includes automated database migration scripts ensuring zero-downtime database transformations across releases:
* `upgrade-1.0.1.php`: Introduced transaction history audit tables.
* `upgrade-1.0.2.php`: Index optimization for reporting queries.
* `upgrade-1.0.3.php`: Print transaction views and schema enhancements.

---

## 📂 Module Directory Structure

```text
financemanager/
├── classes/
│   ├── FinanceCashbook.php          # ObjectModel for main cashbook ledger
│   ├── FinanceCashbookHistory.php   # Audit trail & mutation tracking
│   ├── FinanceCategory.php          # Classification taxonomy
│   └── FinanceOrder.php             # Order association abstraction
├── controllers/
│   └── admin/
│       ├── AdminFinanceAjaxController.php
│       ├── AdminFinanceCashbookController.php
│       ├── AdminFinanceCategoriesController.php
│       ├── AdminFinanceDashboardController.php
│       ├── AdminFinanceExpenseController.php
│       ├── AdminFinanceHistoryController.php
│       ├── AdminFinanceIncomeController.php
│       └── AdminFinanceReportsController.php
├── sql/
│   ├── install.php                  # Schema initialization
│   └── uninstall.php                # Safe cleanup
├── upgrade/
│   ├── upgrade-1.0.1.php
│   ├── upgrade-1.0.2.php
│   └── upgrade-1.0.3.php
├── views/
│   ├── css/
│   │   ├── admin.css
│   │   └── order-search.css
│   ├── js/
│   │   ├── admin.js
│   │   └── order-search.js
│   └── templates/admin/
│       ├── cashbook.tpl
│       ├── categories.tpl
│       ├── dashboard.tpl
│       ├── history.tpl
│       ├── print_transactions.tpl
│       └── reports.tpl
├── config.xml
└── financemanager.php               # Core module bootstrap
```

---

## 🚀 Installation & Compatibility

* **PrestaShop Compatibility:** `8.0.x`, `8.1.x`, `8.2.x`, and `9.0.x`
* **PHP Compatibility:** `PHP 8.1`, `PHP 8.2`, `PHP 8.3`, `PHP 8.4`

### Quick Install
1. Clone or download this repository into your PrestaShop modules directory:
   ```bash
   cd /path-to-prestashop/modules/
   git clone https://github.com/arsalan-ansari/prestashop-finance-manager.git financemanager
   ```
2. Navigate to your PrestaShop Back Office > **Modules** > **Module Manager**.
3. Search for **Finance Manager** and click **Install**.
4. The **Finance Manager** menu tab will immediately register on your admin navigation sidebar.

---

## 👨‍💻 Author & Professional Inquiries

**Arsalan Ansari**  
*Senior PrestaShop & Full-Stack PHP Architect*  
* Specialized in bespoke module development, PrestaShop 8/9 upgrades, and performance tuning for global brands and agencies.
* **LinkedIn:** [linkedin.com/in/arsalan-ansari-prestashop](https://www.linkedin.com/in/arsalan-ansari-prestashop/)
* **GitHub:** [github.com/arsalan-ansari](https://github.com/arsalan-ansari)
