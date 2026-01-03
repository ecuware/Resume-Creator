# Resume CV Management System

A simple, elegant, and secure PHP-based resume/CV management system with an admin panel for easy content editing.

<img width="1904" height="907" alt="{7C9BD244-3D34-4AE4-8C85-8FCBE10DCB28}" src="https://github.com/user-attachments/assets/d98619df-a4c8-4b28-9b57-dd7e3018643c" />


## Features

- ✅ **Clean & Modern Design** - Professional resume template
- ✅ **Admin Panel** - Easy content management without touching code
- ✅ **JSON-based** - Simple data structure, easy to backup
- ✅ **Secure** - Password protection, CSRF tokens, rate limiting
- ✅ **Print-friendly** - Optimized for PDF export
- ✅ **Responsive** - Works on all devices
- ✅ **No Dependencies** - Pure PHP, works on shared hosting

## Quick Start

### Requirements

- PHP 7.0 or higher
- Web server (Apache/Nginx)
- Write permissions for `src/resume.json`

### Installation

1. Upload all files to your web server
2. Set write permissions on `src/resume.json` (chmod 644 or 666)
3. Access `index.php` in your browser to view the resume
4. Access `/admin` or `/login.php` to manage content

## Default Admin Credentials

**Username:** Not required (password only)  
**Password:** `admin123`

⚠️ **IMPORTANT:** Change the password immediately after installation via the admin panel!

### How to Change Password

1. Login to admin panel: `/admin` or `/login.php`
2. Click **"Change Password"** in the left sidebar
3. Enter current password: `admin123`
4. Enter your new password (minimum 8 characters)
5. Confirm and save

The system will automatically hash and save your new password.

## Usage

### Viewing Resume

Simply open `index.php` in your browser. The resume will be displayed in a clean, print-friendly format.

### Editing Content

1. Go to `/admin` or `/login.php`
2. Enter password: `admin123` (or your custom password)
3. Use **"Content Management"** tab for form-based editing
4. Or use **"JSON Editor"** tab for direct JSON editing
5. Click **"Preview"** to see changes in real-time

### Exporting to PDF

1. Open the resume in Chrome browser
2. Press `Ctrl+P` (or `Cmd+P` on Mac)
3. Select "Save as PDF"
4. Print with best quality settings

## File Structure

```
/
├── index.php              # Main resume page
├── admin.php              # Admin panel
├── login.php              # Admin login
├── admin_config.php       # Admin password & settings
├── api.php                # JSON save API
├── api_content.php        # Content management API
├── api_password.php       # Password change API
├── security_functions.php # Security helpers
├── admin_content.php      # Content management UI
├── admin_password.php     # Password change UI
├── assets/
│   ├── css/
│   │   ├── style.css      # Main styles
│   │   └── admin.css      # Admin panel styles
│   └── js/
│       ├── admin.js       # JSON editor JS
│       └── admin_content.js # Content management JS
└── src/
    └── resume.json        # Resume data (JSON)
```

## Where to Run

### Shared Hosting

✅ **Perfect for shared hosting!** Just upload files via FTP/cPanel and it works.

- No Composer required
- No npm/node_modules needed
- All dependencies loaded via CDN
- Works with PHP 7.0+

### Local Development

Works with:
- XAMPP (Windows/Mac/Linux)
- WAMP (Windows)
- MAMP (Mac)
- LAMP (Linux)
- Any PHP development server

### Production Server

- Works on any PHP-enabled web server
- Apache recommended (works with Nginx too)
- No special configuration needed

## Customization

### Changing Resume Content

1. Login to admin panel
2. Use **"Content Management"** for easy editing
3. Or edit `src/resume.json` directly (requires JSON knowledge)

### Changing Design

Edit `assets/css/style.css` to customize colors, fonts, and layout.

## Support

For issues or questions, check the code comments or modify as needed. This is a simple, self-contained system.

## License

Free to use and modify for personal or commercial projects.

---

**Default Admin Password:** `admin123` - **Change it immediately!**

