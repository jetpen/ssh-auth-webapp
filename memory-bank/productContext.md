# Product Context

## Why This Project Exists

Modern web applications increasingly require secure authentication mechanisms beyond traditional passwords. SSH key-based authentication provides strong security through asymmetric cryptography, but integrating SSH keys with web-based authentication flows remains challenging for users.

This webapp provides the server-side of the challenge-response protocol that works in conjunction with the ssh-auth-extension Chromium browser extension for authentication.

## Problems Solved

### Benefits
- User controlled digital identity (decentralized, not government controllable, not controlled by third parties such as Web sites)
- Universal across all participating digital services
- No need to login anywhere (authentication is automatic and invisible)
- No need to remember anything - a self-custodied private key remains secret
- Maximum strength of cryptographic security
- Provides basis for signing digital assets and verifying authenticity and ownership
- Provides basis for end-to-end data encryption and privacy between counterparties

### Current Pain Points
- **Credential Proliferation**: Users must manage separate credentials for SSH access and web authentication
- **Security Complexity**: Web applications struggle to implement robust SSH-based authentication
- **User Experience Gap**: No seamless way to use SSH keys for web login in browsers
- **Key Management Overhead**: Users need to manually handle SSH key operations for web auth

### Target User Problems
- Difficulty integrating SSH keys with web-based authentication systems
- Security risks from password reuse across multiple systems
- Complexity of implementing SSH challenge-response in web contexts
- Lack of browser-native SSH key authentication support

## How It Should Work

### Core User Flow
1. **Sign-up**: User creates account
2. **Authentication**: When user accesses a page requiring authentication, such as the user's account summary page, the user must be authenticated

### Key Interactions
- **Landing Page**: Simple UI with a link to sign up via the Account Creation Interface
- **Account Creation Interface**: Simple UI for creating an account with a unique id, unique name, and providing a unique SSH public key (pre-created by the user)
- **Challenge**: If the user's session cookie is not still valid, present a form with a randomly generated challenge encrypted with the user's SSH public key, expecting the client's ssh-auth-extension to submit a decrypted response; the form must remember the target page originally being visited
- **Challenge-Response Validation**: When a response is submitted for a challenge, the form must contain the encrypted challenge, the decrypted response, and the target page; if the response matches the clear-text challenge, authentication is successful, a valid secure session cookie issued to the user, and the user is redirected to the target page
- **Status Feedback**: Clear indicators of authentication failure by redirecting to an authentication failed page

## User Experience Goals

### Primary Goals
- **Zero-Interaction Authentication**: Once set up, authentication happens automatically
- **Security Transparency**: Users understand when and how SSH keys are being used
- **Error Clarity**: Clear feedback when authentication fails or keys are misconfigured
- **Performance**: Minimal impact on browsing performance

### Secondary Goals
- **Accessibility**: Works across different Chromium browsers (Chrome, Brave, Edge)
- **Privacy**: No external data transmission of sensitive key material
- **Reliability**: Robust error handling and fallback mechanisms

