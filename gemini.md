# gemini.md — Money Tracker

## 1. Project Overview

- Name: Money Tracker
- Description: A personal finance management application for tracking income, expenses, account balances, transfers, budgets, and savings goals.
- Goal: Help users monitor their financial condition, track where their money is stored, analyze spending habits, and achieve financial goals.
- Target Users: Personal Use
- Version: v1.0.0
- Status: Active Development

---

## 2. Tech Stack

### Backend

- PHP 8.4+
- Laravel 12
- PostgreSQL
- Eloquent ORM
- Laravel Breeze

### Frontend

- Vue 3
- Inertia.js
- Tailwind CSS
- Axios

### Development Tools

- Vite
- Composer
- NPM

---

## 3. AI Assistant Rules

Always think and generate code in English.

Use English for:

- Class names
- Method names
- Variable names
- Database tables
- Database columns
- Enum values
- API responses
- Validation messages
- Comments
- Commit messages
- Documentation

Use Laravel best practices whenever possible.

Before implementing a feature:

1. Analyze existing code structure.
2. Reuse existing patterns.
3. Avoid introducing new architecture without approval.
4. Follow Service Layer architecture.
5. Keep code simple and maintainable.

If requirements are unclear, ask for clarification before coding.

---

## 4. Core Features

### Phase 1 (MVP)

- Authentication
- Dashboard
- Account Management
- Category Management
- Sub Category Management
- Transaction Management
- Transfer Management

### Phase 2

- Budget Management
- Savings Goals
- Monthly Reports
- Expense Analytics

### Phase 3

- Recurring Transactions
- Export Excel
- Export PDF
- Financial Insights

---

## 5. Business Rules

### Account

An account represents a place where money is stored.

Supported account types:

- cash
- bank
- e_wallet

Examples:

- Wallet
- BCA
- SeaBank
- DANA
- OVO

---

### Transaction

All income and expense records are stored in the transactions table.

Transaction types:

- income
- expense

Rules:

- Income increases account balance.
- Expense decreases account balance.
- Amount must be greater than zero.

---

### Transfer

A transfer represents money movement between accounts.

Example:

Bank Account -> E-Wallet

Rules:

- Transfer is not income.
- Transfer is not expense.
- Source account balance decreases.
- Destination account balance increases.

---

### Category

Income categories:

- Salary
- Bonus
- Freelance
- Investment

Expense categories:

- Food
- Transportation
- Entertainment
- Shopping
- Bills

---

### Sub Category

Examples:

Food:

- Breakfast
- Lunch
- Dinner

Transportation:

- Fuel
- Parking
- Toll

---

### Spending Classification

Every expense transaction must have one classification:

- need
- want
- investment

Used for spending analysis.

---

### Budget

Users can define monthly budgets per category.

Example:

Food: 1,500,000 IDR
Transportation: 500,000 IDR

---

### Savings Goal

Example:

Name: New Laptop
Target Amount: 15,000,000 IDR
Target Date: 2027-01-01

---

## 6. Database Schema

### users

- id
- name
- email
- password

### accounts

- id
- user_id
- name
- type
- initial_balance
- is_active

### categories

- id
- user_id
- name
- type

### sub_categories

- id
- category_id
- name

### transactions

- id
- account_id
- category_id
- sub_category_id
- type
- amount
- description
- transaction_date

### transfers

- id
- from_account_id
- to_account_id
- amount
- transfer_date
- note

### budgets

- id
- category_id
- month
- year
- amount

### savings_goals

- id
- user_id
- name
- target_amount
- current_amount
- target_date

## 7. Balance Calculation

Account balance must be calculated using:

balance =
initial_balance

- total_income

* total_expense

- transfer_in

* transfer_out

Avoid storing calculated balances unless necessary for performance optimization.

---

## 8. Dashboard Requirements

The dashboard must display:

### Summary

- Total Assets
- Total Income This Month
- Total Expenses This Month
- Net Cash Flow

### Account Distribution

- Balance per account

### Expense Analytics

- Expenses by category
- Need vs Want vs Investment

### Cash Flow

- Monthly income and expense trends

### Recent Transactions

- Latest 10 transactions

---

## 9. Laravel Architecture Rules

### Controllers

Controllers should only:

- Receive requests
- Call services
- Return responses

Do not place business logic inside controllers.

### Services

All business logic must be placed inside:

app/Services

Examples:

- AccountService
- TransactionService
- TransferService
- BudgetService
- DashboardService

### Requests

Use Form Request validation for all validation logic.

Do not validate directly inside controllers.

### Models

Use Eloquent relationships.

Always use eager loading when necessary to avoid N+1 queries.

---

## 10. Do Not

- Do not create new folders without approval.
- Do not install packages without approval.
- Do not delete files without approval.
- Do not hardcode credentials.
- Do not hardcode URLs.
- Do not place business logic inside controllers.
- Do not modify database structure without approval.
- Do not generate migrations without approval.
- Do not expose sensitive environment variables.
- Do not assume requirements when unclear.
