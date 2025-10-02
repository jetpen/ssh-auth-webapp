/**
 * SSH Authentication Webapp - Client-side JavaScript
 *
 * Handles client-side interactions and extension communication.
 */

// App namespace
const SSHAuthApp = {
    // Initialize the application
    init: function() {
        this.bindEvents();
        this.checkExtensionSupport();
        console.log('SSH Authentication Webapp initialized');
    },

    // Bind event listeners
    bindEvents: function() {
        // Form validation
        this.bindFormValidation();

        // Copy to clipboard functionality
        this.bindCopyToClipboard();

        // Extension detection
        this.bindExtensionDetection();
    },

    // Form validation
    bindFormValidation: function() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    this.validateField(input);
                });
            });
        });
    },

    // Validate entire form
    validateForm: function(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });

        return isValid;
    },

    // Validate individual field
    validateField: function(field) {
        const value = field.value.trim();
        const fieldName = field.name || field.id;

        // Remove existing error messages
        this.removeFieldError(field);

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }

        // SSH key validation
        if (fieldName === 'ssh_public_key' && value) {
            if (!this.isValidSSHPublicKey(value)) {
                this.showFieldError(field, 'Invalid SSH public key format');
                return false;
            }
        }

        // Email validation
        if (field.type === 'email' && value) {
            if (!this.isValidEmail(value)) {
                this.showFieldError(field, 'Invalid email format');
                return false;
            }
        }

        // User ID validation
        if (fieldName === 'user_id' && value) {
            if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
                this.showFieldError(field, 'User ID must contain only letters, numbers, underscores, and hyphens');
                return false;
            }
        }

        return true;
    },

    // Show field error
    showFieldError: function(field, message) {
        field.classList.add('error');

        let errorElement = field.parentNode.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            errorElement.style.color = '#dc3545';
            errorElement.style.fontSize = '0.9em';
            errorElement.style.marginTop = '5px';
            field.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;
    },

    // Remove field error
    removeFieldError: function(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    },

    // Validate SSH public key format
    isValidSSHPublicKey: function(key) {
        if (!key || typeof key !== 'string') {
            return false;
        }

        const trimmed = key.trim();

        // Check for common SSH key prefixes
        const patterns = [
            /^ssh-rsa\s+[A-Za-z0-9+\/=]+\s*/,
            /^ssh-ed25519\s+[A-Za-z0-9+\/=]+\s*/,
            /^ecdsa-sha2-nistp256\s+[A-Za-z0-9+\/=]+\s*/,
            /^ssh-dss\s+[A-Za-z0-9+\/=]+\s*/
        ];

        return patterns.some(pattern => pattern.test(trimmed));
    },

    // Validate email format
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Copy to clipboard functionality
    bindCopyToClipboard: function() {
        document.addEventListener('click', (e) => {
            const target = e.target;

            if (target.classList.contains('copy-btn') || target.closest('.copy-btn')) {
                const copyBtn = target.classList.contains('copy-btn') ? target : target.closest('.copy-btn');
                const textToCopy = copyBtn.dataset.clipboardText ||
                                 copyBtn.previousElementSibling?.textContent ||
                                 copyBtn.parentNode.querySelector('code')?.textContent;

                if (textToCopy) {
                    this.copyToClipboard(textToCopy).then(() => {
                        this.showToast('Copied to clipboard!', 'success');
                    }).catch(() => {
                        this.showToast('Failed to copy', 'error');
                    });
                }
            }
        });
    },

    // Copy text to clipboard
    copyToClipboard: async function(text) {
        try {
            await navigator.clipboard.writeText(text);
        } catch (err) {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
    },

    // Extension support detection
    bindExtensionDetection: function() {
        // Check if extension is available
        this.checkExtensionAvailability();

        // Listen for extension messages
        if (window.chrome && window.chrome.runtime) {
            window.chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
                this.handleExtensionMessage(message, sender, sendResponse);
            });
        }
    },

    // Check if extension is available
    checkExtensionAvailability: function() {
        if (window.chrome && window.chrome.runtime && window.chrome.runtime.sendMessage) {
            window.chrome.runtime.sendMessage({ type: 'PING' }, (response) => {
                if (response && response.success) {
                    this.showExtensionStatus(true);
                } else {
                    this.showExtensionStatus(false);
                }
            });
        } else {
            this.showExtensionStatus(false);
        }
    },

    // Show extension status
    showExtensionStatus: function(available) {
        const statusElements = document.querySelectorAll('.extension-status-indicator');

        statusElements.forEach(element => {
            element.textContent = available ? '✅ Available' : '❌ Not detected';
            element.className = available ? 'extension-available' : 'extension-unavailable';
        });
    },

    // Handle messages from extension
    handleExtensionMessage: function(message, sender, sendResponse) {
        console.log('Received message from extension:', message);

        switch (message.type) {
            case 'AUTH_SUCCESS':
                this.showToast('Authentication successful!', 'success');
                // Redirect if specified
                if (message.redirect) {
                    window.location.href = message.redirect;
                }
                break;

            case 'AUTH_FAILED':
                this.showToast('Authentication failed', 'error');
                break;

            case 'CHALLENGE_READY':
                this.showToast('Challenge ready for extension', 'info');
                break;

            default:
                console.log('Unknown extension message type:', message.type);
        }
    },

    // Check extension support
    checkExtensionSupport: function() {
        const extensionSupported = !!(
            window.chrome &&
            window.chrome.runtime &&
            window.chrome.runtime.sendMessage
        );

        document.body.classList.toggle('extension-supported', extensionSupported);
        document.body.classList.toggle('extension-unsupported', !extensionSupported);

        if (!extensionSupported) {
            console.warn('SSH Auth Extension not detected. Some features may not work.');
        }
    },

    // Show toast notification
    showToast: function(message, type = 'info') {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.toast');
        existingToasts.forEach(toast => toast.remove());

        // Create new toast
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        // Style the toast
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8',
            color: 'white',
            padding: '12px 20px',
            borderRadius: '5px',
            boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
            zIndex: '10000',
            fontWeight: '500',
            maxWidth: '300px',
            wordWrap: 'break-word'
        });

        document.body.appendChild(toast);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    },

    // Utility function to get URL parameters
    getUrlParameter: function(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    // Debounce function for input handlers
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    SSHAuthApp.init();
});

// Export for potential use in other scripts
window.SSHAuthApp = SSHAuthApp;
