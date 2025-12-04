<?php
/**
 * Bengali (Bangla) Language File
 * Customer & Real-Time Trading Management System
 * বাংলা ভাষা ফাইল
 */

$lang = [
    // General
    'site_name' => 'ট্রেডিং ম্যানেজমেন্ট সিস্টেম',
    'dashboard' => 'ড্যাশবোর্ড',
    'save' => 'সংরক্ষণ করুন',
    'cancel' => 'বাতিল',
    'edit' => 'সম্পাদনা',
    'delete' => 'মুছুন',
    'view' => 'দেখুন',
    'add' => 'যোগ করুন',
    'search' => 'অনুসন্ধান',
    'filter' => 'ফিল্টার',
    'reset' => 'রিসেট',
    'actions' => 'অ্যাকশন',
    'status' => 'স্ট্যাটাস',
    'date' => 'তারিখ',
    'notes' => 'নোট',
    'active' => 'সক্রিয়',
    'inactive' => 'নিষ্ক্রিয়',
    'all' => 'সব',
    'yes' => 'হ্যাঁ',
    'no' => 'না',
    'confirm_delete' => 'আপনি কি নিশ্চিত যে আপনি এটি মুছে ফেলতে চান?',
    'success' => 'সফল',
    'error' => 'ত্রুটি',
    'warning' => 'সতর্কতা',
    'info' => 'তথ্য',
    'loading' => 'লোড হচ্ছে...',
    'no_data' => 'কোন তথ্য পাওয়া যায়নি',
    'required_field' => 'প্রয়োজনীয় ক্ষেত্র',
    'invalid_request' => 'অবৈধ অনুরোধ। আবার চেষ্টা করুন।',
    
    // Authentication
    'login' => 'লগইন',
    'logout' => 'লগআউট',
    'email' => 'ইমেইল',
    'password' => 'পাসওয়ার্ড',
    'sign_in' => 'সাইন ইন করুন',
    'sign_in_to_account' => 'আপনার অ্যাকাউন্টে সাইন ইন করুন',
    'invalid_credentials' => 'ভুল ইমেইল বা পাসওয়ার্ড।',
    'change_password' => 'পাসওয়ার্ড পরিবর্তন করুন',
    'current_password' => 'বর্তমান পাসওয়ার্ড',
    'new_password' => 'নতুন পাসওয়ার্ড',
    'confirm_password' => 'পাসওয়ার্ড নিশ্চিত করুন',
    'password_changed' => 'পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে!',
    
    // Navigation
    'customers' => 'গ্রাহক',
    'products' => 'পণ্য',
    'deals' => 'লেনদেন',
    'payments' => 'পেমেন্ট',
    'expenses' => 'খরচ',
    'reports' => 'রিপোর্ট',
    'settings' => 'সেটিংস',
    
    // Customer
    'customer' => 'গ্রাহক',
    'add_customer' => 'নতুন গ্রাহক যোগ করুন',
    'edit_customer' => 'গ্রাহক সম্পাদনা করুন',
    'customer_name' => 'গ্রাহকের নাম',
    'customer_phone' => 'ফোন নম্বর',
    'customer_email' => 'ইমেইল',
    'customer_address' => 'ঠিকানা',
    'customer_list' => 'গ্রাহক তালিকা',
    'customer_profile' => 'গ্রাহক প্রোফাইল',
    'customer_added' => 'গ্রাহক সফলভাবে যোগ হয়েছে!',
    'customer_updated' => 'গ্রাহক সফলভাবে আপডেট হয়েছে!',
    'customer_deleted' => 'গ্রাহক সফলভাবে মুছে ফেলা হয়েছে।',
    'customer_not_found' => 'গ্রাহক পাওয়া যায়নি।',
    'total_customers' => 'মোট গ্রাহক',
    
    // Product
    'product' => 'পণ্য',
    'add_product' => 'নতুন পণ্য যোগ করুন',
    'edit_product' => 'পণ্য সম্পাদনা করুন',
    'product_name' => 'পণ্যের নাম',
    'product_category' => 'ক্যাটাগরি',
    'product_list' => 'পণ্য তালিকা',
    'category' => 'ক্যাটাগরি',
    'add_category' => 'ক্যাটাগরি যোগ করুন',
    
    // Deal
    'deal' => 'লেনদেন',
    'deal_number' => 'লেনদেন নম্বর',
    'add_deal' => 'নতুন লেনদেন',
    'edit_deal' => 'লেনদেন সম্পাদনা',
    'view_deal' => 'লেনদেন দেখুন',
    'deal_date' => 'তারিখ',
    'deal_status' => 'স্ট্যাটাস',
    'deal_items' => 'লেনদেন আইটেম',
    'deal_summary' => 'লেনদেন সারাংশ',
    'deal_info' => 'লেনদেন তথ্য',
    'total_deals' => 'মোট লেনদেন',
    'pending' => 'অপেক্ষমান',
    'completed' => 'সম্পন্ন',
    'cancelled' => 'বাতিল',
    'buy_qty' => 'ক্রয় পরিমাণ',
    'buy_price' => 'ক্রয় মূল্য',
    'sell_qty' => 'বিক্রয় পরিমাণ',
    'sell_price' => 'বিক্রয় মূল্য',
    'total_buy' => 'মোট ক্রয়',
    'total_sell' => 'মোট বিক্রয়',
    'add_item' => 'আইটেম যোগ করুন',
    'select_product' => 'পণ্য নির্বাচন করুন',
    'select_customer' => 'গ্রাহক নির্বাচন করুন',
    
    // Payment
    'payment' => 'পেমেন্ট',
    'add_payment' => 'পেমেন্ট যোগ করুন',
    'record_payment' => 'পেমেন্ট রেকর্ড করুন',
    'payment_date' => 'পেমেন্ট তারিখ',
    'payment_method' => 'পেমেন্ট পদ্ধতি',
    'payment_amount' => 'পরিমাণ',
    'payment_history' => 'পেমেন্ট ইতিহাস',
    'cash' => 'নগদ',
    'online' => 'অনলাইন',
    'bank' => 'ব্যাংক ট্রান্সফার',
    'bkash' => 'বিকাশ',
    'nagad' => 'নগদ',
    'rocket' => 'রকেট',
    'other' => 'অন্যান্য',
    'full_payment' => 'সম্পূর্ণ পেমেন্ট',
    'partial_payment' => 'আংশিক পেমেন্ট',
    'payment_recorded' => 'পেমেন্ট সফলভাবে রেকর্ড হয়েছে!',
    'pay_against_customer' => 'গ্রাহকের বাকি পরিশোধ',
    'select_deal_or_customer' => 'লেনদেন বা গ্রাহক নির্বাচন করুন',
    'pay_any_amount' => 'যেকোনো পরিমাণ পরিশোধ করুন',
    
    // Due & Balance
    'due' => 'বাকি',
    'total_due' => 'মোট বাকি',
    'due_amount' => 'বাকি পরিমাণ',
    'paid' => 'পরিশোধিত',
    'total_paid' => 'মোট পরিশোধিত',
    'balance' => 'ব্যালেন্স',
    'outstanding_dues' => 'বকেয়া বাকি',
    'customer_due' => 'গ্রাহকের বাকি',
    'all_transactions_due' => 'সকল লেনদেনের বাকি',
    
    // Profit
    'profit' => 'লাভ',
    'loss' => 'ক্ষতি',
    'gross_profit' => 'মোট লাভ',
    'net_profit' => 'নিট লাভ',
    'total_profit' => 'মোট লাভ',
    
    // Expense
    'expense' => 'খরচ',
    'add_expense' => 'খরচ যোগ করুন',
    'expense_date' => 'খরচের তারিখ',
    'expense_category' => 'খরচের ক্যাটাগরি',
    'total_expense' => 'মোট খরচ',
    'rent' => 'ভাড়া',
    'internet' => 'ইন্টারনেট',
    'transport' => 'পরিবহন',
    'utilities' => 'ইউটিলিটি',
    'office_supplies' => 'অফিস সরঞ্জাম',
    'marketing' => 'মার্কেটিং',
    
    // Reports
    'report' => 'রিপোর্ট',
    'profit_loss_report' => 'লাভ/ক্ষতি রিপোর্ট',
    'customer_report' => 'গ্রাহক রিপোর্ট',
    'due_report' => 'বাকি রিপোর্ট',
    'deal_list' => 'লেনদেন তালিকা',
    'export_csv' => 'CSV ডাউনলোড',
    'print' => 'প্রিন্ট',
    'today' => 'আজ',
    'yesterday' => 'গতকাল',
    'this_week' => 'এই সপ্তাহ',
    'this_month' => 'এই মাস',
    'last_month' => 'গত মাস',
    'this_year' => 'এই বছর',
    'custom_range' => 'কাস্টম তারিখ',
    'from_date' => 'থেকে',
    'to_date' => 'পর্যন্ত',
    
    // Dashboard
    'todays_sell' => 'আজকের বিক্রয়',
    'todays_buy' => 'আজকের ক্রয়',
    'todays_profit' => 'আজকের লাভ',
    'todays_expense' => 'আজকের খরচ',
    'net_profit_today' => 'আজকের নিট লাভ',
    'recent_deals' => 'সাম্প্রতিক লেনদেন',
    'quick_actions' => 'দ্রুত অ্যাকশন',
    'new_deal' => 'নতুন লেনদেন',
    
    // Currency
    'currency_symbol' => '৳',
    'currency_name' => 'টাকা',
    
    // Error messages
    'error_occurred' => 'একটি ত্রুটি হয়েছে',
    'php_errors' => 'PHP ত্রুটি',
    'no_errors' => 'কোন ত্রুটি নেই',
    'debug_mode' => 'ডিবাগ মোড',
];

/**
 * Get translation
 * @param string $key
 * @return string
 */
function __($key) {
    global $lang;
    return $lang[$key] ?? $key;
}

/**
 * Echo translation
 * @param string $key
 */
function _e($key) {
    echo __($key);
}
