# Security Policy

## Supported Versions

ExploreXR is actively maintained and security updates are provided for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

We take security seriously at ExploreXR. If you believe you've found a security vulnerability, please follow these steps:

### Do Not Disclose Publicly

Please do not disclose the vulnerability publicly until we've had a chance to address it.

### Where to Report

Please send security vulnerability reports directly to security@expoxr.com with the subject line "ExploreXR Security Vulnerability".

### What to Include

To help us understand and reproduce the issue, please include:

1. A clear description of the vulnerability
2. Steps to reproduce the issue
3. Potential impact of the vulnerability
4. Any suggestions for mitigation if you have them
5. Your name/handle if you would like to be credited for the discovery

### Response Time

We aim to acknowledge receipt of vulnerability reports within 48 hours and will provide a more detailed response within 7 days indicating the next steps in handling the submission.

### Disclosure Policy

Once we've addressed the vulnerability, we'll notify you and coordinate the public disclosure of the vulnerability. We typically credit individuals who report security vulnerabilities unless they wish to remain anonymous.

## Security Measures in ExploreXR

ExploreXR implements the following security measures:

1. **Input Validation**: All user inputs are properly sanitized using WordPress sanitization functions
2. **Output Escaping**: All outputs are properly escaped using WordPress escaping functions
3. **Nonce Verification**: All forms and AJAX requests use nonces to prevent CSRF attacks
4. **Capability Checks**: All actions verify the user has appropriate capabilities
5. **File Validation**: Strict file type validation for model uploads
6. **Secure Storage**: Uploaded models are stored securely with access controls
7. **Error Handling**: Errors are logged properly without exposing sensitive information
8. **Regular Updates**: Regular security patches and updates

## Best Practices for Users

To ensure the highest level of security when using ExploreXR, we recommend:

1. Keep WordPress, plugins (including ExploreXR), and themes updated to the latest versions
2. Use strong passwords and enable two-factor authentication
3. Apply the principle of least privilege for user roles
4. Only upload models from trusted sources
5. Regularly back up your website
6. Monitor your site for unusual activity
7. Use a security plugin to enhance WordPress security

Thank you for helping keep ExploreXR and its users safe!

---

Last updated: July 11, 2025
