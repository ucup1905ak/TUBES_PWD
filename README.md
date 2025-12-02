

# 🐾 PawHaven - Pet Boarding Management System

A comprehensive web-based pet boarding management system built with PHP, MySQL, and vanilla JavaScript.

## 📋 Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [API Endpoints](#api-endpoints)
- [Project Structure](#project-structure)
- [Documentation](#documentation)

## ✨ Features

### User Authentication
- **Register** with email, username, password, and profile photo
- **Login** with either email or username
- **Session Management** with secure token-based authentication
- **Auto-redirect** for authenticated users
- **Profile Picture** upload and display (BLOB storage)

### Dashboard
- Modern UI with sidebar navigation
- User profile display with all details
- Session-based authentication check
- Logout functionality
- Responsive design

### Security
- SHA-256 password hashing on client-side
- Secure session tokens (64-character hex)
- SQL injection prevention with prepared statements
- File upload validation (size, type, extension)
- Session expiration (24 hours)

## 🛠 Tech Stack

### Backend
- **PHP** 7.4+ (with MySQLi)
- **MySQL** Database
- Custom routing system
- RESTful API architecture

### Frontend
- **HTML5** (XHTML strict)
- **CSS3** (with modern features)
- **JavaScript** (ES6+, Vanilla JS)
- localStorage for session management

### Database
- MySQL with InnoDB engine
- Prepared statements for security
- BLOB storage for images
- Foreign key constraints

## 📥 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/ucup1905ak/TUBES_PWD.git
   cd TUBES_PWD
   ```

2. **Database Configuration**
   - Update database credentials in `index.php`:
   ```php
   $apiBackend->connectDB('localhost', 3306, 'root', '123', 'pwd');
   ```

3. **Start the server**
   ```bash
   # Windows (PowerShell with admin rights)
   sudo php -S localhost:80

   # Linux/Mac
   php -S localhost:8000
   ```

4. **Access the application**
   - Open browser: `http://localhost/` (or `http://localhost:8000`)

## 🔌 API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register new user with optional profile photo |
| POST | `/api/auth/login` | Login with email/username and password |
| GET | `/api/auth/me` | Get current user info from session token |

### User Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user/{id}` | Get user information by ID |

### Animals (Hewan)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/hewan` | Get list of animals |
| POST | `/api/hewan/tambah` | Add new animal |

### Boarding (Penitipan)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/penitipan/jumlah` | Get total boarding count |
| POST | `/api/penitipan/tambah` | Add new boarding transaction |

## 📁 Project Structure

```
TUBES_PWD/
├── agent_task/              # AI agent documentation
│   └── README.md           # Detailed change log
├── public/                  # Frontend files
│   ├── css/                # Stylesheets
│   ├── img/                # Images
│   ├── js/                 # JavaScript files
│   │   ├── login.js       # Login logic
│   │   ├── register.js    # Registration logic
│   │   └── session.js     # Session management utilities
│   ├── pages/             # XHTML pages
│   │   ├── landing.xhtml  # Home page
│   │   ├── login.xhtml    # Login page
│   │   └── register.xhtml # Registration page
│   └── dashboard.php      # User dashboard
├── src/                    # Backend source
│   ├── api/               # API controllers
│   │   ├── auth/         # Authentication endpoints
│   │   │   ├── post_login.php
│   │   │   ├── post_register.php
│   │   │   └── get_me.php
│   │   ├── user/         # User endpoints
│   │   ├── hewan/        # Animal endpoints
│   │   └── penitipan/    # Boarding endpoints
│   ├── config/           # Configuration
│   │   └── database_setup.php
│   └── routing/          # Custom router
│       └── router.php
├── index.php              # Application entry point
├── API_DOCUMENTATION.md   # Complete API documentation
└── README.md             # This file
```

## 📚 Documentation

For detailed documentation, see:
- [API Documentation](API_DOCUMENTATION.md) - Complete API reference
- [Agent Task Log](agent_task/README.md) - Detailed development changelog
- [tugas.md](tugas.md) - Task list and improvements

## 🔑 Key Routes

| Route | Description |
|-------|-------------|
| `/` | Landing page |
| `/login` | Login page |
| `/register` | Registration page |
| `/my` | User dashboard (requires authentication) |
| `/logout` | Logout (clears session) |

## 🔐 Authentication Flow

1. **Registration**: User fills form → Password hashed (SHA-256) → Data stored in DB
2. **Login**: User submits credentials → Password hashed → Validated against DB → Session token generated → Token stored in localStorage
3. **Authorization**: Session token sent in `Authorization: Bearer {token}` header
4. **Validation**: Backend checks token validity and expiration
5. **Logout**: Token removed from localStorage → Redirect to home

## 🎨 Features Highlights

### Modern Dashboard
- Sidebar navigation with menu items
- Profile picture display
- User information cards
- Welcome message with date
- Logout confirmation dialog

### Session Management
- Automatic session validation
- Token expiration check
- Redirect logic for auth states
- Persistent login (24 hours)

### File Uploads
- Profile picture support
- Image validation (JPG, PNG, GIF)
- Size limit (16MB)
- BLOB storage in database
- Base64 encoding for display

## 🐛 Known Issues & Limitations

1. Client-side session storage (should be server-side validation)
2. No password reset functionality
3. No email verification
4. Limited file type detection fallback
5. Dashboard sub-pages not yet implemented

## 🚀 Future Enhancements

- [ ] Server-side session validation
- [ ] Profile editing functionality
- [ ] Password reset via email
- [ ] Email verification on registration
- [ ] Admin dashboard
- [ ] Booking management system
- [ ] Pet management interface
- [ ] Payment integration
- [ ] Notification system
- [ ] Search and filter features

## 👥 Contributing

This is an academic project. For contributions or suggestions, please contact the repository owner.

## 📄 License

See [LICENSE](LICENSE) file for details.

## 📧 Contact

For questions or support, please open an issue on GitHub.

---

## Database Schema

![Database ERD](erd.png)

### Main Tables
- **User** - User accounts with profile information
- **User_Session** - Active user sessions
- **Pet** - Pet information
- **Paket_Kamar** - Room packages
- **Layanan** - Additional services
- **Penitipan** - Boarding transactions
- **Penitipan_Layanan** - Services per boarding

---

**Last Updated**: December 2, 2025
**Version**: 1.0.0


