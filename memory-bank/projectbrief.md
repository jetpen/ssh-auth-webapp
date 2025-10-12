# SSH Authentication Webapp - Project Brief

## Overview
This project develops a PHP web application that authenticates users logging into the web site using a SSH key.

## Core Requirements
- **Site Content**: Provide a public welcome page with a link to sign up, and a account summary page for an authenticated user
- **SSH Key Management**: Provide single page interface for user creating for an account with a SSH public key
- **Account Management**: Store user accounts with their SSH public key in a MySQL database dedicated to this application
- **Session Management**: Use a secure session cookie expiring after 1 hour that can be validated
- **Authentication**: Provide interface for logging in with SSH authentication by issuing a challenge, and expecting the decrypted response to be submitted for verification, the successful verifcation of which would result in a valid session cookie to indicate an authenticated user
- **Security**: Implement secure key storage and authentication practices
- **User Experience**: Intuitive UI for a user to self manage their account, name, and SSH public key
- **Setup**: Provide single page administrative interface for one-time setup of the application's database schema, if not already setup

## Success Criteria
- Web app deployable to a web server
- Supports key-based SSH authentication methods
- Provides clear feedback and error handling for authentication issues

## Scope
- Focus on user self-managed account creation and authentication
- Target Apache HTTPD web server with PHP and MySQL database on Linux

## Constraints
- Must follow PHP development best practices
- Must follow HTML/JavaScript/CSS and single-page application best practices
- Must follow Web application security best practices (always HTTPS)
- Must protect against XSS, CSRF, and other Web site attack vectors
- A user account must have a unique user id, unique name, and unique SSH public key
- Cannot store sensitive data insecurely
- Must handle authentication securely without exposing credentials
- Must never generate or access any user's SSH private key
- Users must securely self-custody their SSH private key on their Web client and never expose that private key
- Source code file structure should mirror the Web server
- Must be deployable under any directory as the root context
- Must not assume the Web server's domain
- Must authenticate by working in conjunction with the client-side browser extension in the ssh-auth-extension project
- Must comprehend the challenge-response protocol implemented by the ssh-auth-extension source code
