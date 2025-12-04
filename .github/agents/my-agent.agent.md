db name fortestt_freelance
db user pass fortestt_freelance2


I need a full Customer & Real-Time Trading (Buy-Sell) Management Website using **Core PHP only (no framework like Laravel, CodeIgniter, etc.)**.

This system must track everything: customers, product deals, buying price, selling price, profit/loss, payments, dues, expenses, and summary reports.

📌 Business Model:
- I do not keep stock.
- When a customer orders, I buy the product from the market and sell it immediately to the customer.
- The same product may be purchased at different prices at different times (real-time price changes).
- I earn profit from each deal.

--------------------------------
🎯 Main Goals:
--------------------------------
- Customer management
- Buy & Sell deal management
- Profit/Loss tracking
- Expense & income tracking
- Due & payment tracking
- Summary & reports (daily, monthly, yearly, custom)
- Full CRUD for all data (Create, Read, Update, Delete)

--------------------------------
🧩 MODULE & FEATURE DETAILS:
--------------------------------

1️⃣ Authentication & User System
- Login / Logout
- Email + Password authentication (Core PHP session-based login)
- Password reset (simple “change password” after login is enough)
- (Optional) Role system: Admin / Staff

2️⃣ Customer Management
- Add new customer:
  - Name
  - Phone number
  - Email (optional)
  - Address
  - Notes
- Customer list:
  - Search by name / phone
  - Filter active / inactive
- Customer profile page:
  - All deals of that customer
  - Total sell, total paid, total due
  - Total profit from this customer
- Edit customer info
- Soft delete customer (record stays in database but marked as deleted)

3️⃣ Product / Item Reference (No stock management)
- Simple product list:
  - Product name
  - Category (optional)
  - Notes (brand/model etc.)
- Same product can appear in multiple deals with different buy/sell prices.

4️⃣ Deal / Transaction Management
Every deal contains buying + selling + payment information.

Deal entry:
- Auto Deal ID
- Deal date
- Customer (select from dropdown)
- Deal status: Pending / Completed / Cancelled
- Multiple product lines per deal:
  - Product (select/type)
  - Buying quantity
  - Buy price per unit
  - Total buy amount (auto: qty * buy price)
  - Selling quantity
  - Sell price per unit
  - Total sell amount (auto: qty * sell price)
- Total Buy Amount (auto sum)
- Total Sell Amount (auto sum)
- Profit (auto) = Total Sell – Total Buy

5️⃣ Payment & Due Management
- For each deal:
  - Add payment entry:
    - Paid amount
    - Payment method (cash / online / bank / other)
    - Payment date
  - Allow multiple partial payments for one deal.
- Due Calculation:
  - Due = Total Sell – Total Paid
- Payment history per deal and per customer.
- Customer-wise due summary (who owes how much).

6️⃣ Expenses & Other Income
- Expense entry:
  - Date
  - Category (rent, internet, transport, etc.)
  - Amount
  - Note
- (Optional) Other income entry (if I have income not related to deals).

7️⃣ Dashboard & Reports
A. Dashboard (Home):
- Today’s total sell
- Today’s total buy
- Today’s gross profit (sell – buy)
- Today’s total expense
- Today’s net profit (gross profit – expenses)
- Total due from all customers
- Total customers
- Total deals
- Simple charts if possible (but not mandatory).

B. Reports (with date range filters: today / yesterday / this week / this month / custom):
1) Profit / Loss Report:
   - Total Buy
   - Total Sell
   - Gross Profit
   - Total Expense
   - Net Profit
2) Customer-wise Report:
   - Total deals
   - Total sell amount
   - Total paid
   - Total due
   - Profit from that customer
3) Due Report:
   - List of customers with outstanding dues
4) Deal List:
   - Filters: customer, date range, status
   - Export to CSV or simple printable HTML (PDF export optional).

8️⃣ CRUD & Data Protection
- Full CRUD for:
  - Customers
  - Deals
  - Deal products
  - Payments
  - Expenses
- Use soft delete wherever possible (mark as deleted instead of hard delete).
- Confirm dialog before deleting important data.
- Store created_at and updated_at timestamps.

--------------------------------
💻 TECHNOLOGY REQUIREMENTS (VERY IMPORTANT):
--------------------------------
- **Use Core PHP only** (no frameworks like Laravel, CodeIgniter, Symfony, etc.).
- Use **MySQL** as the database.
- Use **PDO or MySQLi** with prepared statements for database queries.
- Use **PHP sessions** for login and authentication.
- Basic MVC-style folder structure is preferred, for example:
  - `/config` → database config
  - `/includes` → common functions, header, footer
  - `/models` → PHP files for database queries
  - `/views` → HTML/PHP templates
  - `/public` or root → main entry PHP files (index.php, login.php, etc.)

- Frontend:
  - Use HTML + CSS + JavaScript.
  - Bootstrap is allowed for layout and design.
  - Must be mobile responsive.

--------------------------------
🔐 SECURITY & BACKUP
--------------------------------
- Proper input validation & sanitization.
- Use prepared statements (PDO/MySQLi) to prevent SQL injection.
- Protect all internal pages so only logged-in users can access them.
- Simple manual database backup option:
  - For example, a page/link that explains how to export DB, or a button that triggers SQL backup (if you implement it).

--------------------------------
🧠 FINAL REQUIREMENT:
--------------------------------
Build the complete system using **Core PHP + MySQL**, including:
- Database schema (SQL file)
- All PHP pages for frontend + backend logic
- Clean and well-commented code
- Sample data so I can immediately test the system

The purpose of this project is to track everything related to:
- customers
- deals (with real-time buying and selling prices)
- payments
- dues
- expenses
- profit/loss

Especially important: the same product can be bought and sold at different prices at different times, and the system must correctly calculate profit and due for each deal.

