# Task Management System

A department-based task management web application built in PHP and MySQL, developed during an internship at Pravas Digital Vision Systems Pvt. Ltd. It supports role-based task assignment, multi-level approvals, notifications, and reporting across departments.

## Features

- **User & Department Management** — admin can manage users, departments, and role-based access control (RBAC)
- **Task Management** — create, assign, and track tasks with support for subtasks and status updates
- **Approval Workflow** — tasks require department-head approval before being marked complete
- **Activity Logs** — full audit trail of actions taken on tasks and users
- **Notifications** — in-app notifications with configurable preferences per user
- **Email Integration** — automated email alerts (via PHPMailer) for task assignments, reminders, and approvals
- **Task Reminders** — scheduled reminders for upcoming or overdue tasks
- **Reviews & Ratings** — review and rate completed tasks
- **Reports** — reporting module for tracking task and department performance

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Email:** PHPMailer
- **Frontend:** HTML, CSS, JavaScript (server-rendered PHP views)

## Project Structure

```
├── index.php                  Entry point
├── login.php                  Authentication
├── home.php                   Dashboard
├── manage_task.php            Task management
├── task_list.php               Task listing
├── view_task.php               Task detail view
├── task_approvals.php          Approval workflow
├── review_task.php             Task review/rating
├── manage_user.php             User management
├── manage_department.php       Department management
├── department_list.php         Department listing
├── user_list.php                User listing
├── activity_logs.php           Audit log
├── view_notifications.php      Notifications
├── notification_preferences.php Notification settings
├── email_settings.php          Email configuration
├── save_task_reminder.php      Reminder scheduling
├── reports.php                 Reporting module
├── db_connect.php              Database connection
├── database/                   SQL schema and migrations
└── vendor/                     Composer dependencies (PHPMailer)
```

## Database

The `database/` folder contains the base schema (`tms_db.sql`) plus incremental migrations for:
- Start dates on task lists
- Department-head approval workflow
- Subtasks support
- Feature enhancements (notifications, reminders, etc.)

## Setup & Running

1. Install a local PHP + MySQL environment (e.g., XAMPP or WAMP).
2. Place the project folder in your server's `htdocs` (or equivalent) directory.
3. Create a MySQL database named `tms_db` and import `database/tms_db.sql`, followed by the migration files in `database/` in order.
4. Update `db_connect.php` with your MySQL credentials if different from the defaults.
5. Configure `email_settings.php` with SMTP credentials to enable email notifications.
6. Start your local server and open the project in a browser (e.g., `http://localhost/Task-Management-System`).
7. Log in via `login.php` with an admin account created in the database.

## Notes

This project was built as part of an internship to manage departmental task workflows, from assignment through multi-level approval and reporting.
