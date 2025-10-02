# System Patterns

## Architecture Overview

### High-Level Architecture
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Browser   │    │   Web Server    │    │   MySQL DB      │
│                 │    │   (Apache/PHP)  │    │                 │
│ ┌─────────────┐ │    │                 │    │ ┌─────────────┐ │
│ │ssh-auth-    │ │◄──►│ ┌─────────────┐ │◄──►│ │user_accounts│ │
│ │extension    │ │    │ │PHP App      │ │    │ │table        │ │
│ └─────────────┘ │    │ └─────────────┘ │    │ └─────────────┘ │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Component Relationships
- **Web Browser**: Hosts the ssh-auth-extension for challenge-response protocol
- **Web Server**: Apache HTTPD serving PHP application
- **Database**: MySQL storing user accounts with SSH public keys
- **Extension**: Chromium browser extension handling SSH key operations

## Key Technical Decisions

### Authentication Flow
1. **Challenge Generation**: Server generates random challenge, encrypts with user's SSH public key
2. **Challenge Presentation**: Encrypted challenge sent to client via web form
3. **Response Processing**: Browser extension decrypts challenge, submits decrypted response
4. **Verification**: Server compares response with original challenge for authentication

### Session Management
- Secure HTTP-only session cookies with 1-hour expiration
- Automatic redirect to target page upon successful authentication
- Clear session invalidation on authentication failure

### Data Storage
- User accounts table with unique constraints on id, name, and SSH public key
- No storage of private keys (client-side only)
- Secure storage practices for public keys

## Design Patterns

### MVC Pattern
- **Model**: Database interactions for user accounts
- **View**: HTML templates with embedded PHP for dynamic content
- **Controller**: PHP scripts handling requests and business logic

### Challenge-Response Protocol
- Asymmetric cryptography using SSH keys
- Server generates challenge, client proves key possession
- Stateless verification process

### Single Page Application Elements
- AJAX for dynamic challenge loading
- Form state preservation during authentication redirects
- Progressive enhancement for extension-dependent features

## Critical Implementation Paths

### Authentication Sequence
```
User accesses protected page
    ↓
Check session cookie validity
    ↓ (invalid)
Generate random challenge string
    ↓
Encrypt challenge with user's SSH public key
    ↓
Present challenge form with:
  - data-ssh-challenge attribute containing encrypted challenge
  - ssh_challenge form field with challenge data
  - target URL for post-auth redirect
    ↓
Extension detects challenge via DOM observation
    ↓
Extension decrypts challenge using user's private key
    ↓
Extension injects ssh_auth_response form field with signature
    ↓
Form submits with both challenge and response
    ↓
Server verifies signature matches original challenge
    ↓ (success)
Issue session cookie + redirect to target page
```

### Challenge Format Requirements
- **Encrypted Challenge**: Base64-encoded challenge encrypted with user's SSH public key
- **Challenge Metadata**: JSON object containing challenge data, algorithm, and public key reference
- **Detection Patterns**: Extension looks for `data-ssh-challenge`, `ssh_challenge` form fields, and content patterns
- **Response Field**: Extension adds `ssh_auth_response` hidden field with cryptographic signature

### Account Creation
```
User provides: id, name, SSH public key
    ↓
Validate uniqueness constraints
    ↓
Store in database
    ↓
Redirect to success page
```

### Database Schema Setup
```
Admin accesses setup page
    ↓
Check if schema exists
    ↓ (not exists)
Execute schema creation SQL
    ↓
Display setup confirmation
```

## Security Patterns

### XSS Prevention
- Input sanitization on all user inputs
- Output encoding for dynamic content
- Content Security Policy headers

### CSRF Protection
- CSRF tokens in forms
- Same-site cookie attributes
- Origin validation

### Secure Key Handling
- Public key storage only (never private keys)
- Key format validation
- Secure transmission over HTTPS only

## Error Handling Patterns

### Authentication Failures
- Clear error messages without information leakage
- Automatic cleanup of failed session attempts
- Logging for security monitoring

### Database Errors
- Graceful degradation with user-friendly messages
- Transaction rollback on failures
- Connection pooling and timeout handling

## Performance Considerations

### Database Optimization
- Indexed unique constraints on user fields
- Connection pooling for MySQL
- Prepared statements for query security

### Session Management
- Efficient cookie-based sessions
- Automatic cleanup of expired sessions
- Minimal session data storage
