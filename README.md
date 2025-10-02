# SSH Authentication Webapp

A secure PHP web application that provides SSH key-based authentication through a challenge-response protocol. This server-side component works in conjunction with the [ssh-auth-extension](https://github.com/jetpen/ssh-auth-extension) Chromium browser extension to enable passwordless authentication using SSH keys.

## 🎯 Overview

Traditional password-based authentication has significant security and usability challenges. This webapp implements a modern approach using asymmetric cryptography (SSH keys) for user authentication, providing:

- **Decentralized Identity**: Users maintain full control over their private keys
- **Universal Authentication**: Works across participating web services
- **Automatic Login**: Seamless authentication once configured
- **Maximum Security**: Cryptographic strength of SSH key authentication
- **Privacy Protection**: No external transmission of sensitive key material

## ✨ Features

### Core Functionality
- **SSH Key Registration**: Secure account creation with SSH public key storage
- **Challenge-Response Authentication**: Stateless verification using cryptographic signatures
- **Session Management**: Secure HTTP-only cookies with automatic expiration
- **Account Management**: User profile and SSH key management interface

### Security Features
- **XSS Prevention**: Input sanitization and output encoding
- **CSRF Protection**: Token-based form validation
- **HTTPS Enforcement**: Secure transport requirements
- **Key Security**: Public key only storage (private keys remain client-side)

### User Experience
- **Progressive Enhancement**: Graceful degradation without extension
- **Responsive Design**: Mobile-friendly interface
- **Clear Feedback**: Comprehensive error messages and status indicators
- **Accessibility**: Screen reader compatible and keyboard navigable

## 🏗️ Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Browser   │    │   Web Server    │    │   MySQL DB      │
│                 │    │   (Apache/PHP)  │    │                 │
│ ┌─────────────┐ │◄──►│                 │◄──►│ ┌─────────────┐ │
│ │ssh-auth-    │ │    │ ┌─────────────┐ │    │ │user_accounts│ │
│ │extension    │ │    │ │PHP App      │ │    │ │table        │ │
│ └─────────────┘ │    │ └─────────────┘ │    │ └─────────────┘ │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Authentication Flow
1. **Challenge Generation**: Server creates random challenge encrypted with user's SSH public key
2. **Challenge Presentation**: Encrypted challenge sent to client via web form
3. **Extension Processing**: Browser extension decrypts challenge using user's private key
4. **Response Submission**: Extension provides cryptographic signature for verification
5. **Verification**: Server validates signature against original challenge

## 🛠️ Technology Stack

### Backend
- **PHP**: 7.4+ with OpenSSL extension
- **MySQL**: 5.7+ for user account storage
- **Apache**: 2.4+ with mod_php

### Frontend
- **HTML5**: Semantic markup and forms
- **CSS3**: Responsive styling and animations
- **JavaScript (ES6+)**: Client-side validation and extension communication

### Security
- **OpenSSL**: Cryptographic operations
- **PHP Sessions**: Secure session management
- **Content Security Policy**: XSS prevention

## 📋 Requirements

### Server Requirements
- **Operating System**: Linux (Ubuntu/Debian recommended)
- **Web Server**: Apache 2.4+ with mod_php
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **PHP**: 7.4+ with required extensions:
  - `mysqli`
  - `openssl`
  - `session`

### Browser Requirements
- **Chromium-based browsers**: Chrome 88+, Edge 88+, Brave, etc.
- **ssh-auth-extension**: Required for authentication functionality

### Development Requirements
- **Git**: Version control
- **Composer**: PHP dependency management (if needed)
- **SSH Client**: For deployment and testing

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/jetpen/ssh-auth-webapp.git
cd ssh-auth-webapp
```

### 2. Database Setup
Create a MySQL database and user:
```sql
CREATE DATABASE ssh_auth_db;
CREATE USER 'ssh_auth_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON ssh_auth_db.* TO 'ssh_auth_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configure Application
Edit `src/includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ssh_auth_db');
define('DB_USER', 'ssh_auth_user');
define('DB_PASS', 'your_secure_password');
```

### 4. Web Server Configuration
Configure Apache virtual host pointing to `src/` directory:
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/ssh-auth-webapp/src

    <Directory /path/to/ssh-auth-webapp/src>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 5. HTTPS Setup
Configure SSL certificate (Let's Encrypt recommended):
```bash
sudo certbot --apache -d your-domain.com
```

### 6. Database Schema
Access `https://your-domain.com/setup.php` to create database tables.

## 📖 Usage

### User Registration
1. Visit the application homepage
2. Click "Sign Up" to create an account
3. Provide unique username and SSH public key
4. Account created successfully

### Authentication
1. Access protected pages (e.g., account summary)
2. If unauthenticated, redirected to login challenge
3. ssh-auth-extension automatically processes challenge
4. Successful authentication redirects to intended page

### SSH Key Management
- Users generate SSH keys using standard tools (`ssh-keygen`)
- Public keys uploaded during registration
- Private keys remain securely stored client-side
- Extension manages private key operations

## 🧪 Testing

### Local Development Setup
```bash
# Set up test environment
./tests/setup_test_env.sh

# Configure test context
cp tests/test_context.sh.template tests/test_context.sh
# Edit tests/test_context.sh with your values
```

### Manual Testing
1. **Unit Tests**: PHP syntax validation completed
2. **Integration Tests**: Manual browser testing with extension
3. **Security Tests**: Input validation and XSS prevention verified

### Test Scripts
- `tests/setup_test_env.sh`: Remote deployment setup
- `tests/test_context.sh.template`: Test configuration template

## 📁 Project Structure

```
ssh-auth-webapp/
├── src/                    # Web application source
│   ├── index.php          # Landing page
│   ├── signup.php         # Account creation
│   ├── account.php        # Protected account page
│   ├── auth.php           # Authentication handler
│   ├── setup.php          # Database setup
│   ├── logout.php         # Session cleanup
│   ├── includes/          # PHP libraries
│   │   ├── config.php     # Database configuration
│   │   ├── functions.php  # Utility functions
│   │   └── auth.php       # Authentication logic
│   ├── css/               # Stylesheets
│   └── js/                # Client-side scripts
├── tests/                 # Test scripts and configuration
├── memory-bank/           # Project documentation
└── README.md             # This file
```

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create feature branch: `git checkout -b feature/your-feature`
3. Make changes following PHP best practices
4. Test thoroughly with ssh-auth-extension
5. Submit pull request

### Code Standards
- **PHP**: PSR-12 coding standards
- **Security**: OWASP guidelines compliance
- **Documentation**: Comprehensive inline comments
- **Testing**: Manual testing with extension required

### Reporting Issues
- Use GitHub Issues for bug reports
- Include browser version and extension status
- Provide steps to reproduce issues
- Include relevant log entries

## 📄 License

This project is licensed under the Apache License - see the [LICENSE](LICENSE) file for details.

## 🔗 Related Projects

- **[ssh-auth-extension](https://github.com/jetpen/ssh-auth-extension)**: Chromium browser extension for SSH key operations
- **[OpenSSH](https://www.openssh.com/)**: SSH protocol implementation
- **[PHP OpenSSL](https://www.php.net/manual/en/book.openssl.php)**: Cryptographic operations

## ⚠️ Security Considerations

### Private Key Security
- **Never transmit private keys** over any network
- **Client-side storage only** for private keys
- **Extension custody** of cryptographic operations

### Server Security
- **HTTPS required** for all communications
- **Secure session cookies** with proper expiration
- **Input validation** on all user data
- **Regular security updates** for all components

### Best Practices
- **Unique SSH keys** per service recommended
- **Regular key rotation** for enhanced security
- **Backup strategies** for account recovery

## 📞 Support

### Documentation
- [Memory Bank](memory-bank/) - Comprehensive project documentation
- [API Reference](memory-bank/techContext.md) - Technical specifications

### Community
- **Issues**: Bug reports and feature requests
- **Discussions**: General questions and community support

---

**Built with ❤️ using PHP, MySQL, and modern web standards**
