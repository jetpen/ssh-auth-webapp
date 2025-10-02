# Tech Context

## Technology Stack

### Backend
- **Language**: PHP 7.4+ (server-side scripting)
- **Web Server**: Apache HTTPD with mod_php
- **Database**: MySQL 5.7+ (user account storage)
- **Session Handling**: PHP native sessions with secure cookies
- **Cryptography**: OpenSSL extension for SSH key encryption/decryption operations

### Frontend
- **HTML5**: Semantic markup and forms
- **CSS3**: Responsive styling and layouts
- **JavaScript (ES6+)**: AJAX requests and DOM manipulation
- **Browser Extension**: ssh-auth-extension for SSH key-based authentication

### Security
- **Cryptography**: OpenSSL for SSH public key encryption and signature verification
- **HTTPS**: Required for all communications (extension requirement)
- **CSRF Protection**: Token-based form validation
- **XSS Prevention**: Input sanitization and output encoding
- **Key Security**: Public keys only stored server-side, private keys remain client-side

### Development Tools
- **Version Control**: Git
- **Package Management**: Composer (PHP dependencies)
- **IDE**: Visual Studio Code
- **Testing**: Manual testing with browser developer tools
- **Deployment**: Manual file transfer to web server

## Development Setup

### Local Development Environment
- **Operating System**: Linux (Ubuntu/Debian preferred)
- **Web Server**: Apache 2.4 with PHP module
- **Database**: MySQL 5.7 or MariaDB 10.3+
- **PHP Extensions**: Required: mysqli, openssl, session
- **Browser**: Chromium-based (Chrome, Brave, Edge) with ssh-auth-extension

### Directory Structure
```
ssh-auth-webapp/
├── index.php              # Landing page
├── signup.php             # Account creation
├── account.php            # Account summary (protected)
├── auth.php               # Authentication handler
├── setup.php              # Database setup (admin)
├── includes/
│   ├── config.php         # Database configuration
│   ├── functions.php      # Utility functions
│   └── auth.php           # Authentication logic
├── css/
│   └── style.css          # Application styles
├── js/
│   └── app.js             # Client-side JavaScript
└── memory-bank/           # Documentation
```

### Database Schema
```sql
CREATE TABLE user_accounts (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    ssh_public_key TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ssh_key (ssh_public_key(255))
);
```

## Technical Constraints

### Security Requirements
- **HTTPS Only**: All communications must use secure transport
- **Key Security**: Never store or transmit private keys
- **Session Security**: HTTP-only, secure, same-site cookies
- **Input Validation**: Strict validation of all user inputs
- **Output Encoding**: Prevent XSS through proper encoding

### Performance Constraints
- **Database Load**: Minimal queries per request
- **Session Overhead**: Lightweight session data
- **Browser Compatibility**: Chromium browsers only (extension dependency)
- **Server Resources**: Standard PHP hosting requirements

### Deployment Constraints
- **File System**: Must work under any directory context
- **Domain Independence**: No hardcoded domain assumptions
- **Server Configuration**: Standard Apache/PHP/MySQL setup
- **Extension Dependency**: Requires ssh-auth-extension for authentication

## Dependencies

### Runtime Dependencies
- **PHP 7.4+**: Core language runtime
- **MySQL 5.7+**: Database server
- **Apache 2.4+**: Web server with mod_php
- **OpenSSL**: Cryptographic operations
- **ssh-auth-extension**: Browser extension for key operations

### Development Dependencies
- **Git**: Version control
- **Composer**: PHP dependency management (if needed)
- **Visual Studio Code**: Development IDE
- **MySQL Workbench**: Database design and management

### Browser Requirements
- **Chromium-based browsers**: Chrome 88+, Edge 88+, Brave, etc.
- **Extension API**: chrome.runtime, chrome.storage support
- **JavaScript**: ES6+ features support

## Tool Usage Patterns

### Development Workflow
1. **Local Setup**: Configure Apache virtual host pointing to project directory
2. **Database Setup**: Create MySQL database and run schema setup
3. **Extension Installation**: Load ssh-auth-extension in browser developer mode
4. **Testing**: Manual testing with browser developer tools
5. **Deployment**: SCP/rsync files to production server

### Code Organization
- **Separation of Concerns**: PHP for backend logic, JavaScript for client-side interactions
- **Security First**: Input validation and output encoding in all user-facing code
- **Modular Design**: Reusable functions in includes/ directory
- **Configuration Management**: Environment-specific settings in config files

### Testing Approach
- **Manual Testing**: Browser-based testing of authentication flows
- **Security Testing**: Manual verification of security measures
- **Cross-browser Testing**: Testing across supported Chromium browsers
- **Performance Testing**: Basic load testing with multiple concurrent users

### Deployment Strategy
- **File-based Deployment**: Direct file transfer to web server
- **Database Migrations**: Manual schema updates via admin interface
- **Configuration**: Environment-specific config files
- **Monitoring**: Basic error logging and access monitoring

## Build and Deployment

### Build Process
- **No Build Required**: Direct PHP execution
- **Asset Optimization**: Manual minification if needed
- **Dependency Management**: Composer install for any PHP packages

### Deployment Checklist
- [ ] Verify PHP version compatibility
- [ ] Configure Apache virtual host
- [ ] Set up MySQL database and user
- [ ] Upload files to web server
- [ ] Set proper file permissions
- [ ] Configure HTTPS certificates
- [ ] Test database setup page
- [ ] Verify extension installation
- [ ] Test complete authentication flow

## SSH Extension Integration

### Challenge Detection Patterns
The ssh-auth-extension detects authentication challenges through:
- **HTML Attributes**: `data-ssh-challenge`, `data-auth-challenge`, `ssh-challenge`
- **Form Fields**: `ssh_challenge`, `auth_challenge`, `challenge` input fields
- **Content Patterns**: Regex matching for "ssh challenge", "authentication challenge", etc.

### Challenge Format
Challenges should be presented as JSON objects:
```json
{
  "type": "ssh",
  "challenge": "<encrypted-challenge-data>",
  "algorithm": "ssh-rsa|ecdsa-sha2-nistp256|ssh-ed25519",
  "publicKey": "<user-public-key>"
}
```

### Response Format
Extension submits authentication response via:
- **Form Field**: `ssh_auth_response` containing the cryptographic signature
- **Signature**: Base64-encoded signature of the decrypted challenge
- **Verification**: Server must verify signature against original challenge using stored public key

### Implementation Requirements
- **OpenSSL Integration**: Use PHP OpenSSL functions for key operations
- **Challenge Encryption**: Encrypt random challenges with user's SSH public key
- **Signature Verification**: Verify extension-provided signatures against original challenges
- **Error Handling**: Graceful fallback when extension is not available or fails
