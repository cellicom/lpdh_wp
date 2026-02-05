# Email Template System - Setup Instructions

## Overview
The email template system has been successfully implemented with theme-based styling and logo integration.

## Files Created

### Email Templates Directory: `email-templates/`
- `class-email-template.php` - Base template class with theme detection
- `functions-email.php` - Helper functions for email operations
- `new-user-welcome.php` - Welcome email for new users
- `admin-new-user-notification.php` - Admin notification for new registrations

### Page Template
- `page-templates/page-email-test.php` - Admin test page for email preview and sending

### Modified Files
- `page-templates/page-register.php` - Updated to use new email template system
- `functions.php` - Added includes and AJAX handlers

---

## Setup Steps

### 1. Create Email Test Page

1. Go to **Pages > Add New** in WordPress admin
2. Set page title: `Email Test` (or any name you prefer)
3. Select template: **Email Test Page** from the Template dropdown
4. Publish the page
5. Note the page URL for easy access

> **Important:** This page is admin-only. Non-administrators will see a permission error.

### 2. Configure Site Logo

For emails to display your logo:

1. Go to **Appearance > Customize**
2. Navigate to **Site Identity > Logo**
3. Upload your site logo
4. Save changes

If no logo is set, emails will display the site name as text.

### 3. Test Email Templates

1. Visit the Email Test page you created
2. You'll see:
   - **Template Selector**: Choose which email to preview
   - **Theme Override**: Test different theme styles
   - **Preview**: Live preview of the email
   - **Send Test Email**: Send a real test email

### 4. Available Email Templates

- **New User Welcome**: Sent to users when they register
- **Admin New User Notification**: Sent to site admin when a user registers

---

## How It Works

### Theme-Based Styling

Emails automatically adapt their colors and design based on the active theme selected in **Theme Settings**:

- **Bootscore (Default)**: Blue gradient header, clean design
- **Vaporwave**: Neon pink/purple gradient with glow effects
- **Vaporwave Green**: Neon green with dark background
- **Lost Wood**: Forest green/brown earthy theme

### Registration Flow

When a new user registers:

1. User submits registration form
2. System generates random password
3. Welcome email sent to user (with credentials)
4. Admin notification sent to site administrator
5. Both emails use theme-based templates

### Email Customization

To modify email content, edit the template files in `email-templates/`:

- `new-user-welcome.php` - User welcome email content
- `admin-new-user-notification.php` - Admin notification content

---

## Testing

### Test Email Sending

1. Go to Email Test page
2. Select a template
3. Choose a theme (optional override)
4. Enter your email address
5. Click "Send Test Email"
6. Check your inbox

### Verify Theme Styling

1. Change active theme in **Theme Settings**
2. Register a new test user
3. Check email styling matches theme

### Test with WP Mail SMTP

The system works with WP Mail SMTP plugin:

1. Ensure WP Mail SMTP is configured
2. Test emails will use your SMTP settings
3. Check WP Mail SMTP logs for delivery status

---

## Troubleshooting

### Emails Not Sending

1. Check WP Mail SMTP configuration
2. Verify Gmail SMTP settings
3. Check spam folder
4. Review WP Mail SMTP logs

### Logo Not Displaying

1. Ensure logo is uploaded in **Appearance > Customize**
2. Check logo file is accessible
3. Try re-uploading logo

### Theme Styling Not Working

1. Verify active theme is set in **Theme Settings**
2. Clear browser cache
3. Test with different theme override

### Permission Errors

Email test page requires administrator access. Ensure you're logged in as admin.

---

## Future Enhancements

Planned but not yet implemented:

- [ ] Password reset email template (override WordPress default)
- [ ] Email logging system
- [ ] Email template editor in admin
- [ ] Additional email types (achievement unlocks, etc.)

---

## Technical Notes

### Email Headers

All emails use:
- `Content-Type: text/html; charset=UTF-8`
- `From: {Site Name} <noreply@{domain}>`

### Sample Data

Test emails use data from User ID: 1 (admin user) with:
- Username, email, display name
- Sample password: `SamplePass123!`
- Current timestamp

### AJAX Endpoints

- `lpdh_preview_email` - Preview email in iframe
- `lpdh_send_test_email` - Send test email to specified address

---

## Support

For issues or questions:
1. Check error logs in WordPress
2. Review WP Mail SMTP logs
3. Test with default theme
4. Verify email template files exist
