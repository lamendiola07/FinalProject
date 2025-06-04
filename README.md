# FinalProject
# PUP Login System with AJAX/JSON and XML Export

A complete PHP-based login system for PUP (Polytechnic University of the Philippines) with modern AJAX functionality, user management dashboard, and XML export capabilities.

## Features

- **Modern Login/Register Interface**: Animated sliding forms with responsive design
- **AJAX-powered Authentication**: No page reloads during login/registration
- **User Dashboard**: View all registered users in a searchable table
- **XML Export**: Download user data as XML file for offline storage
- **Secure Password Handling**: BCrypt password hashing
- **Input Validation**: Both client-side and server-side validation
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Session Management**: Secure session handling with proper logout

## File Structure

```
project/
├── index.html          # Main login/register page
├── style.css           # Styling for the interface
├── script.js           # Enhanced JavaScript with AJAX
├── config.php          # Database configuration
├── auth_handler.php    # Authentication API endpoint
├── dashboard.php       # User dashboard with table view
├── users_api.php       # API for fetching user data
├── export_xml.php      # XML export functionality
├── database_setup.sql  # Database schema
└── README.md          # This file
```

## Setup Instructions

### 1. Database Setup

1. Create a MySQL database:
   ```sql
   CREATE DATABASE pup_login_system;
   ```

2. Import the database schema:
   ```bash
   mysql -u your_username -p pup_login_system < database_setup.sql
   ```

   Or run the SQL commands from `database_setup.sql` in your MySQL client.

### 2. Configuration

1. Update `config.php` with your database credentials:
   ```php
   $host = 'localhost';
   $dbname = 'pup_login_system';
   $username = 'your_db_username';
   $password = 'your_db_password';
   ```

### 3. File Permissions

Ensure your web server has read/write permissions to the project directory.

### 4. Web Server Setup

1. Place all files in your web server's document root (e.g., `htdocs`, `www`, or `public_html`)
2. Ensure PHP is enabled with MySQL extension
3. Access the application via your web browser: `http://localhost/your-project-folder/`

## How It Works

### AJAX/JSON Communication

The system uses AJAX for seamless communication between the frontend and backend:

1. **Registration Process**:
   - User fills the registration form
   - JavaScript captures form submission
   - Data is sent as JSON to `auth_handler.php`
   - Server validates and processes the request
   - JSON response is returned and handled by JavaScript
   - Success/error messages are displayed without page reload

2. **Login Process**:
   - Similar AJAX flow for authentication
   - Upon successful login, user is redirected to dashboard
   - Session is established server-side

3. **Dashboard Data Loading**:
   - `users_api.php` provides user data as JSON
   - JavaScript fetches and displays data in the table
   - Real-time search functionality filters the displayed data

### XML Export Feature

- Users can export all user data as XML
- `export_xml.php` generates properly formatted XML with CDATA sections
- File is automatically downloaded with timestamp in filename
- XML structure includes user ID, name, email, faculty ID, registration date, and last login

## API Endpoints

### POST `/auth_handler.php`

**Register User:**
```json
{
  "action": "register",
  "fullName": "John Doe",
  "email": "john@pup.edu.ph",
  "facultyId": "FAC001",
  "password": "securepassword",
  "confirmPassword": "securepassword"
}
```

**Login User:**
```json
{
  "action": "login",
  "username": "john@pup.edu.ph",
  "password": "securepassword"
}
```

**Logout User:**
```json
{
  "action": "logout"
}
```

### GET `/users_api.php`

Returns all users (requires authentication):
```json
{
  "success": true,
  "users": [...],
  "total": 5
}
```

### GET `/export_xml.php`

Downloads XML file with all user data (requires authentication).

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with BCrypt
- **SQL Injection Prevention**: Prepared statements with PDO
- **Session Security**: Proper session management
- **Input Validation**: Server-side validation for all inputs
- **Authentication Required**: Dashboard and API endpoints require login
- **XSS Prevention**: HTML escaping for output

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Common Issues:

1. **Database Connection Error**:
   - Check database credentials in `config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **AJAX Requests Failing**:
   - Check browser console for JavaScript errors
   - Ensure PHP files are in the correct location
   - Verify web server is running

3. **Session Issues**:
   - Check PHP session configuration
   - Ensure cookies are enabled in browser

4. **File Download Issues**:
   - Check file permissions
   - Ensure PHP has write access to temporary directories

## Customization

You can customize the system by:

- Modifying the CSS in `style.css` for different styling
- Adding more user fields in the database and forms
- Implementing role-based access control
- Adding email verification for registration
- Implementing password reset functionality

## License

This project is open source and available under the MIT License.