# Customer & Real-Time Trading (Buy-Sell) Management System

A complete web-based management system for tracking customers, deals, payments, expenses, and profit/loss. Built with **Core PHP** (no frameworks) and **MySQL**.

## Features

### 🔐 Authentication & Security
- Session-based login/logout
- Password change functionality
- CSRF protection
- Prepared statements for SQL injection prevention
- Input sanitization and validation

### 👥 Customer Management
- Add, edit, view, and delete customers
- Search by name or phone number
- Filter by active/inactive status
- Customer profile with deal history
- Track total sales, payments, and dues per customer

### 📦 Product/Item Reference
- Simple product catalog (no stock management)
- Category management
- Same product can have different prices in different deals

### 💼 Deal/Transaction Management
- Create deals with multiple product lines
- Track buying and selling prices separately
- Automatic profit calculation
- Deal status (Pending, Completed, Cancelled)
- Support for real-time price changes

### 💳 Payment & Due Management
- Record partial or full payments
- Multiple payment methods (Cash, Online, Bank, Other)
- Payment history per deal and customer
- Automatic due calculation

### 💰 Expense Tracking
- Track business expenses by category
- Date-based filtering
- Expense categories (Rent, Internet, Transport, etc.)

### 📊 Dashboard & Reports
- **Dashboard**: Today's sell, buy, profit, expense, net profit, total dues
- **Profit/Loss Report**: Date range filtering, gross/net profit calculation
- **Customer-wise Report**: Sales, payments, dues, profit per customer
- **Due Report**: Outstanding dues summary
- **Deal List Report**: Filterable and exportable to CSV

## Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server

### Setup Steps

1. **Clone or upload files to your web server**

2. **Create the database**
   - Create a MySQL database named `fortestt_freelance`
   - Create a user with credentials:
     - Username: `fortestt_freelance2`
     - Password: `fortestt_freelance2`
   - Grant all privileges on the database to this user

3. **Import the database schema**
   ```bash
   mysql -u fortestt_freelance2 -p fortestt_freelance < database/schema.sql
   ```
   Or import via phpMyAdmin.

4. **Update database configuration (if needed)**
   Edit `config/database.php` if your database credentials differ.

5. **Access the application**
   Navigate to your web server URL (e.g., `http://localhost/`)

### Default Login
- **Email**: admin@example.com
- **Password**: admin123

## Directory Structure

```
├── assets/
│   └── css/
│       └── style.css          # Custom styles
├── config/
│   └── database.php           # Database configuration
├── database/
│   └── schema.sql             # Database schema with sample data
├── includes/
│   ├── footer.php             # Page footer template
│   ├── functions.php          # Common helper functions
│   └── header.php             # Page header template
├── models/
│   ├── CustomerModel.php      # Customer database operations
│   ├── DealModel.php          # Deal database operations
│   ├── ExpenseModel.php       # Expense database operations
│   ├── PaymentModel.php       # Payment database operations
│   ├── ProductModel.php       # Product database operations
│   └── UserModel.php          # User database operations
├── index.php                  # Dashboard
├── login.php                  # Login page
├── logout.php                 # Logout handler
├── customers.php              # Customer list
├── customer-*.php             # Customer CRUD pages
├── products.php               # Product list
├── product-*.php              # Product CRUD pages
├── deals.php                  # Deal list
├── deal-*.php                 # Deal CRUD pages
├── payments.php               # Payment list
├── payment-*.php              # Payment CRUD pages
├── expenses.php               # Expense list
├── expense-*.php              # Expense CRUD pages
├── reports.php                # Reports page
├── settings.php               # Settings page
├── export-deals.php           # CSV export
└── .htaccess                  # Apache security config
```

## Business Logic

### How Deals Work
1. When a customer orders, you buy the product from the market
2. Enter the buying price and selling price
3. System calculates profit automatically (Sell Price - Buy Price)
4. Same product can have different prices in different deals

### Due Calculation
- **Deal Due** = Total Sell Amount - Total Payments
- **Customer Due** = Sum of all unpaid deal amounts

### Profit Calculation
- **Gross Profit** = Total Sell - Total Buy
- **Net Profit** = Gross Profit - Total Expenses

## Technologies Used
- **Backend**: Core PHP (no frameworks)
- **Database**: MySQL with PDO
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Icons**: Bootstrap Icons
- **Security**: CSRF tokens, Prepared statements, Session management

## License
This project is provided as-is for personal/commercial use.

## Support
For issues or feature requests, please create an issue in the repository.
