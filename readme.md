# Password Cracker Application

This is a web-based password cracking application built with PHP, JavaScript, and MySQL/SQLite. It categorizes cracked passwords into "Easy," "Medium," and "Hard" based on predefined patterns and displays the results in a user-friendly interface. The application includes features like a loader, rate limiting, export functionality, and filtering options.

## Features
- **Password Cracking**: Cracks passwords from a database based on numeric, uppercase, lowercase, and mixed-case patterns.
- **Categories**:
  - **Easy**: 5-digit numeric passwords (e.g., 12345).
  - **Medium**: 3 uppercase letters + 1 digit (e.g., ABC1) or 6-character lowercase dictionary words (e.g., london).
  - **Hard**: 6-character mixed-case passwords with numbers (e.g., AbC12z).
- **User Interface**: Includes a loader during processing, category selection, user ID filtering, and result export as CSV.
- **Security**: Implements rate limiting (5 requests per minute) and security headers (e.g., X-Frame-Options, Content-Security-Policy).
- **Performance**: AJAX request timeout (30 seconds) and optimized cracking logic.
- **Error Handling**: Detailed error messages for database issues, timeouts, and general errors.

## Prerequisites
- **PHP** (8.1 or higher)
- **MySQL** (for storing user data)
- **SQLite** (for caching cracked passwords)
- **Composer** (for dependency management)
- **Web Server** (e.g., Apache or Nginx)
- **Node.js** (optional, for frontend testing)
- **Docker** (latest version, for containerized deployment)
- **Docker Compose** (latest version)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/password-cracker.git
cd password-cracker
```

### 2. Install Dependencies
Install the required PHP dependency (`vlucas/phpdotenv`) using Composer:

```bash
composer require vlucas/phpdotenv
```

### 3. Configure Environment
Create a `.env` file in the root directory with the following content:

```env
DB_HOST=localhost
DB_NAME=cracker
DB_USER=root
DB_PASS=
SQLITE_DB=hash_cache.db
SALT=ThisIs-A-Salt123
```

- Update `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` with your MySQL credentials.
- Ensure `SQLITE_DB` points to a writable location for the SQLite database file.

### 4. Set Up the Database
- Create a MySQL database named `cracker`.
- Create a table `not_so_smart_users` with the following SQL:

```sql
CREATE TABLE not_so_smart_users (
    user_id VARCHAR(10) PRIMARY KEY,
    password VARCHAR(255) NOT NULL
);
```

- Insert sample data (e.g., hashed passwords using the salter function from the code):

```sql
INSERT INTO not_so_smart_users (user_id, password) VALUES
('2615', 'e7d8f2a5b8e4c9d1f3a2b5c8e7d9f0a1'), -- Hash of "87411"
('2562', 'd1c2e3f4a5b6c7d8e9f0a1b2c3d4e5f6'); -- Hash of "11223"
```

- The `password` column should contain MD5 hashes of passwords concatenated with the `SALT` value.

### 5. Prepare Dictionary File
Create a `dictionary.txt` file in the root directory with lowercase words (max 6 characters), one per line:

```
monkey
london
paris
```

Ensure the file is readable by the web server.

### 6. Configure Web Server
- Place the `password-cracker` directory in your web server’s document root (e.g., `/var/www/html` for Apache).
- Ensure PHP is configured to handle `.php` files.

### 7. Deploy with Docker
#### 7.1 Build and Run with Docker Compose
Run the following command to build and start the Docker containers:
```bash
docker-compose up --build -d
```
This will:
- Build the PHP application image.
- Start a MySQL container with the `cracker` database.
- Map port `8080` on your host to port `80` in the container.

#### 7.2 Set Up the Database Inside the Container
Access the MySQL container to create the `not_so_smart_users` table:
```bash
docker exec -it password-cracker_db_1 mysql -uroot -p cracker
```
Run the SQL commands as mentioned in step 4.

### 8. Test the Application
- Open your browser and navigate to `http://localhost:8080/index.html`.
- Click **"Start Cracking"** to initiate the process.

## Usage
- **Start Cracking**: Click the "Start Cracking" button to begin the password cracking process.
- **Select Category**: Use the dropdown to filter results by "Easy," "Medium," or "Hard."
- **Filter by User ID**: Enter a `user_id` in the filter input to show only matching results.
- **Clear Results**: Click "Clear Results" to reset the table and category selection.
- **Export Results**: Click "Export Results" to download the cracked passwords as a `cracked_passwords.csv` file.

## Configuration
- **Rate Limiting**: Adjustable in `cracker.php` (default: 5 requests per minute).
- **Timeout**: Adjustable in `index.html` AJAX call (default: 30 seconds).
- **Category Limits**: Defined in `src/PasswordCategory.php` (Easy: 4, Medium: 6, Hard: 1).

## Security Considerations
- **Rate Limiting**: Prevents abuse with a 5-request-per-minute limit.
- **Headers**: Includes `X-Content-Type-Options`, `X-Frame-Options`, and `Content-Security-Policy` for protection.
- **Credentials**: Store sensitive data (e.g., database credentials) in the `.env` file; consider using a secrets manager in production.
- **HTTPS**: Use HTTPS in production to encrypt data in transit.

## Troubleshooting
- **Error: "Too many requests"**: Wait 1 minute or adjust `$rateLimit` and `$timeWindow` in `cracker.php`.
- **Error: "Database error"**: Verify MySQL credentials and table existence.
- **Error: "Dictionary file not found"**: Ensure `dictionary.txt` exists and is readable.
- **No Results**: Check the `not_so_smart_users` table for data and ensure passwords match the cracking patterns.

## Contributing
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature-name`).
3. Commit changes (`git commit -m "Add feature-name"`).
4. Push to the branch (`git push origin feature-name`).
5. Open a pull request.

## Acknowledgements
- **Bootstrap** for the UI components.
- **jQuery** for AJAX handling.
- **PHP and MySQL/SQLite** for backend processing.

---

### Notes
- **Customization**: Replace `your-username` with your GitHub username and add a `LICENSE` file if desired.
- **GitHub Integration**: If hosted on GitHub, add a `.gitignore` file to exclude `.env`, `vendor/`, and `hash_cache.db`.
- **Documentation**: The README assumes the code is functional as provided. If you encounter issues, update the "Troubleshooting" section accordingly.
