# Password Cracker Tool

This is a password-cracking tool designed to attempt cracking hashed passwords from a database using various password combinations. The project uses PHP for the backend and jQuery/HTML for the frontend.

## Features

- **Password Cracking:** Attempts to crack passwords stored in a database using precomputed combinations and dictionary-based checks.
- **Categories of Cracked Passwords:** Classifies cracked passwords into three categories: Easy, Medium, and Hard.
- **Caching:** Stores cracked passwords in a cache to prevent redundant operations.
- **Frontend Interface:** Provides an interface for viewing cracked passwords.

## Technologies Used

- **Backend:** PHP 8.0.30
- **Frontend:** HTML, jQuery
- **Database:** MySQL (Database name: `cracker`)
- **Server:** Apache/2.4.59 (Win64)

## Prerequisites

Before running the project, ensure you have the following installed:

- **Apache** with PHP support
- **MySQL** database
- **PHP** (version 8.0.30 or above)
- **jQuery** (used in the frontend)

## Setup Instructions

### 1. Database Setup

1. Create a MySQL database named `cracker` using the following command:

   ```sql
   CREATE DATABASE cracker;
   ```

2. Create the necessary table `not_so_smart_users` to store the user data. Here’s a sample schema:

   ```sql
   CREATE TABLE not_so_smart_users (
       user_id INT AUTO_INCREMENT PRIMARY KEY,
       password VARCHAR(255) NOT NULL
   );
   ```

3. Import the hashed passwords into the `not_so_smart_users` table as needed.

### 2. Backend Setup

Ensure the `cracker.php` file is placed in your Apache's `htdocs` directory (or the equivalent directory for your local server).

Modify the `config/db.php` file to reflect your database credentials:

#### Database Connection Example

Ensure your MySQL database is set up before running the script.

```php
<?php

$host = 'localhost'; 
$dbname = 'cracker';
$username = 'root';
$password = ''; // Adjust if necessary

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

?>
```

Make sure the `cracker.php` file has the correct file permissions to execute.

### 3. Frontend Setup

The frontend `index.html` file should be placed in the same directory as `cracker.php` or in the `public` directory where you host your files.

Open `index.html` in a browser to interact with the tool.

### 4. Running the Application

1. Start the Apache server with PHP support.
2. Access `index.html` via a browser (e.g., `http://localhost/index.html`).

### 5. Testing the Functionality

Once the setup is complete, the application will attempt to crack passwords based on precomputed combinations and dictionary checks. It will classify the cracked passwords into categories such as:

- **Easy:** Numeric or simple combinations.
- **Medium:** Mixed-case words (such as "AbC12z").
- **Hard:** Complex passwords with upper, lower, numeric, and special characters.

The cracked passwords will be stored in the cache and categorized as shown in the frontend interface.

#### Example Response

```json
{
    "Easy": [
        {"id": 1, "password": "12345"},
        {"id": 2, "password": "23456"}
    ],
    "Medium": [
        {"id": 3, "password": "AbC12z"},
        {"id": 4, "password": "abcde"}
    ],
    "Hard": [
        {"id": 5, "password": "aBcD123"},
        {"id": 6, "password": "L0ndon!5"}
    ]
}
```

---

This guide ensures you can set up, run, and test the Password Cracker Tool efficiently. If you encounter any issues, ensure that your Apache, PHP, and MySQL configurations are correctly set up.
