Implement an Expense Management module and update the Net Profit calculation logic in "Helvetica Engine".

Requirements:

1. Database & Schema:
    - Create a migration for `expenses` table with columns: `id`, `expense_number` (unique string, e.g., EXP-YYYYMMDD-XXX), `amount` (decimal 12,2), `category` (string: Operational, Utilities, Salaries, Maintenance, Marketing, Supplies, Supplies/Inventory, Others), `description` (text, nullable), `expense_date` (date/timestamp), `payment_method` (string: CASH, TRANSFER), `created_by` (foreign key to users), `timestamps`.

2. Backend Logic & Services:
    - Create `ExpenseController` and Livewire components for full CRUD operations (List, Add, Edit, Delete).
    - Update `DashboardService` and `SalesReportController` to aggregate total expenses within the active date boundary (Asia/Jakarta timezone).
    - Update `Net Profit` calculation formula across the application:
      Net Profit = Gross Profit (DPP - COGS) - Total Expenses

3. Sidebar Navigation:
    - Add "Expenses" menu item in the primary sidebar under the "Financials / Reports" group.
    - Use a clean icon (e.g., receipt or credit card icon) matching the Swiss-design aesthetic.

4. Dashboard & Reporting UI Updates:
    - Add an `EXPENSES` metric card in the primary financial metrics grid between `GROSS` and `NET`.
    - Card Styling:
        - Label: `font-sans text-xs font-semibold uppercase tracking-wider text-gray-500` -> EXPENSES
        - Value: `font-mono text-2xl font-bold tracking-tight text-red-600` (e.g., Rp50.000)
        - Subtitle: `font-sans text-xs text-gray-400` -> Total pengeluaran operasional
    - Update the `NET` card to display the dynamically computed Net Profit (Gross Profit minus Expenses) and update its subtitle accordingly.

5. Thermal / Sales Summary Report:
    - Include `Total Expense` and `Net Profit` lines in end-of-day sales summary reports or PDF exports.

6. Verification:
    - Ensure `php artisan migrate` runs smoothly.
    - Verify that adding an expense immediately updates the `NET PROFIT` metric on the Dashboard in real-time without affecting `DPP` or `GROSS PROFIT`.

Fix QRIS fee calculation logic and implement Expense Management module in "Helvetica Engine".

1. QRIS Fee Fix:
    - Ensure QRIS Merchant Discount Rate (MDR) or extra fee is NOT added to the customer's total bill or taxable base unless explicitly set as a customer surcharge.
    - Fix the PB1 calculation on mixed-tax QRIS carts so items with PB1 10% (e.g. Matcha Latte) still properly compute DPP and PB1 state taxes regardless of the payment method.

2. Expenses Module Implementation:
    - Create `expenses` table migration: `id`, `expense_number`, `amount` (decimal 12,2), `category` (Supplies, Operational, Utilities, Salaries, Marketing, Others), `description`, `expense_date`, `created_by`, `timestamps`.
    - Add "Expenses" CRUD view in Livewire and add its link under the primary Sidebar navigation.
    - Update `DashboardService` to calculate total expenses for the selected date range.

3. Financial Metrics & Net Profit Logic:
    - Add an `EXPENSES` card in the Dashboard metrics grid.
    - Update `NET PROFIT` formula: Net Profit = Gross Profit (DPP - COGS) - Total Expenses.
    - Update the dashboard alert banner once Expenses can be recorded.
