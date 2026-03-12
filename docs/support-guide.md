# 🆘 Support Guide

This guide will help you get the support you need for WP Telegram Post Notifier.

## 📋 Table of Contents

- [Getting Help](#getting-help)
- [Troubleshooting](#troubleshooting)
- [Common Issues](#common-issues)
- [FAQ](#faq)
- [Contact Information](#contact-information)

## 🆘 Getting Help

### 1. Check the Documentation First
Before asking for help, please check:
- [Installation Guide](installation.md)
- [Quick Start Guide](quick-start.md)
- [Troubleshooting Guide](troubleshooting.md)
- [FAQ](faq.md)

### 2. Search Existing Issues
- Check [GitHub Issues](https://github.com/VoxHash/WPTelegram-Post-Notifier/issues)
- Search for similar problems
- Check if your issue has already been reported

### 3. Ask in GitHub Discussions
- Use [GitHub Discussions](https://github.com/VoxHash/WPTelegram-Post-Notifier/discussions)
- Ask questions and share ideas
- Get help from the community

### 4. Report Bugs
- Use our [bug report template](.github/ISSUE_TEMPLATE/bug_report.md)
- Provide detailed information
- Include steps to reproduce

### 5. Request Features
- Use our [feature request template](.github/ISSUE_TEMPLATE/feature_request.md)
- Describe the feature clearly
- Explain the use case

## 🔧 Troubleshooting

### Step 1: Check Plugin Status
1. Go to **Plugins** in WordPress admin
2. Ensure "WP Telegram Post Notifier" is active
3. Check for any error messages

### Step 2: Verify Requirements
- **WordPress**: 6.3 or higher
- **PHP**: 7.4 or higher
- **Memory**: 128MB PHP memory limit
- **Disk Space**: 50MB available

### Step 3: Check Configuration
1. Go to **Settings > Telegram Notifier**
2. Verify bot token is correct
3. Test connection
4. Check destinations are configured

### Step 4: Check Logs
1. Go to **Settings > Telegram Notifier > Logs**
2. Look for error messages
3. Check notification status
4. Look for patterns in failures

### Step 5: Enable Debug Mode
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 🚨 Common Issues

### 1. Bot Not Sending Messages

**Symptoms:**
- No messages appear in Telegram
- Plugin shows "Connection successful" but no notifications

**Solutions:**
1. **Check Bot Permissions**
   - Ensure bot is added to the channel/group
   - Verify bot has admin permissions
   - Check if bot can send messages

2. **Verify Bot Token**
   - Test connection in plugin settings
   - Ensure token is correct and active
   - Check for typos in the token

3. **Check Channel ID**
   - Use correct format: `@channelname` or `-1001234567890`
   - For private channels, use numeric ID
   - Ensure channel exists and is accessible

### 2. 403 Forbidden Error

**Symptoms:**
- Error message: "Forbidden: bot is not a member of the group chat"
- Messages fail to send

**Solutions:**
1. **Add Bot to Channel**
   - Add bot to the channel/group
   - Make bot an admin
   - Ensure bot has "Send Messages" permission

2. **Check Channel Privacy**
   - For private channels, ensure bot is added
   - Verify channel settings allow bot messages

### 3. 429 Rate Limit Error

**Symptoms:**
- Error message: "Too Many Requests"
- Messages delayed or not sent

**Solutions:**
1. **Wait and Retry**
   - Plugin handles this automatically
   - Wait for the rate limit to reset
   - Reduce notification frequency

2. **Adjust Settings**
   - Increase retry delay in settings
   - Reduce number of destinations
   - Space out notifications

### 4. Template Not Rendering

**Symptoms:**
- Tokens not replaced in messages
- Messages show `{token_name}` instead of values

**Solutions:**
1. **Check Token Syntax**
   - Use correct format: `{token_name}`
   - Ensure tokens are spelled correctly
   - Check for typos

2. **Verify Parse Mode**
   - Check parse mode settings
   - Ensure special characters are escaped
   - Test with different parse modes

### 5. Plugin Not Activating

**Symptoms:**
- Plugin fails to activate
- Error messages during activation

**Solutions:**
1. **Check Requirements**
   - WordPress 6.3 or higher
   - PHP 7.4 or higher
   - Sufficient memory and disk space

2. **Check for Conflicts**
   - Deactivate other plugins temporarily
   - Switch to default theme
   - Check error logs

### 6. Settings Not Saving

**Symptoms:**
- Settings revert after saving
- Changes not applied

**Solutions:**
1. **Check Permissions**
   - Ensure user has `manage_wptpn` capability
   - Check WordPress user roles
   - Verify admin access

2. **Check for JavaScript Errors**
   - Open browser console
   - Look for JavaScript errors
   - Refresh page and try again

## ❓ FAQ

### Q: Can I send to multiple channels?
A: Yes! You can add multiple destinations in the plugin settings. Each destination can be a different channel, group, or private chat.

### Q: Can I customize the message format?
A: Absolutely! The plugin includes a powerful template system with dynamic tokens. You can customize the message format in the Template tab.

### Q: Does it work with WooCommerce?
A: Yes! The plugin has special support for WooCommerce products, including price change notifications and product-specific tokens.

### Q: Can I send notifications manually?
A: Yes! You can send notifications manually using the "Send to Telegram" action in post lists or the Gutenberg sidebar panel.

### Q: Is it safe to use?
A: Yes! The plugin follows WordPress security best practices, including input sanitization, capability checks, and secure token storage.

### Q: Can I use it on a multisite network?
A: Yes! The plugin works on both single sites and multisite networks. Each site can have its own configuration.

### Q: Does it support different languages?
A: Yes! The plugin is fully internationalized and supports multiple languages. You can contribute translations on our GitHub page.

### Q: Can I integrate it with other plugins?
A: Yes! The plugin provides extensive hooks and filters for integration with other plugins and custom code.

## 📞 Contact Information

### 🐛 Bug Reports
- **GitHub Issues**: [Report a bug](https://github.com/VoxHash/WPTelegram-Post-Notifier/issues)
- **Email**: bugs@voxhash.dev

### ✨ Feature Requests
- **GitHub Issues**: [Request a feature](https://github.com/VoxHash/WPTelegram-Post-Notifier/issues)
- **Email**: features@voxhash.dev

### 💬 General Support
- **GitHub Discussions**: [Ask questions](https://github.com/VoxHash/WPTelegram-Post-Notifier/discussions)
- **Email**: support@voxhash.dev

### 🔒 Security Issues
- **Email**: security@voxhash.dev
- **PGP Key**: Available upon request

### 💼 Business Inquiries
- **Email**: business@voxhash.dev
- **Website**: https://www.voxhash.dev/

## ⏰ Response Times

- **Bug Reports**: 2-3 business days
- **Feature Requests**: 1-2 weeks
- **Security Issues**: 24-48 hours
- **General Support**: 3-5 business days
- **Business Inquiries**: 1-2 business days

## 🎯 How to Get Better Support

### 1. Provide Detailed Information
- WordPress version
- PHP version
- Plugin version
- Browser and OS
- Error messages
- Steps to reproduce

### 2. Include Screenshots
- Error messages
- Plugin settings
- Browser console errors
- Any relevant screenshots

### 3. Test Before Reporting
- Test with default theme
- Test with other plugins deactivated
- Check error logs
- Try different browsers

### 4. Be Patient
- We respond to all inquiries
- Some issues take time to investigate
- We prioritize security issues
- We appreciate your patience

## 🤝 Community Support

### GitHub Discussions
- Ask questions and share ideas
- Get help from other users
- Share tips and tricks
- Discuss feature requests

### Contributing
- Help improve the plugin
- Contribute to documentation
- Report bugs and issues
- Suggest new features

### Recognition
- Contributors are recognized
- Special thanks for significant contributions
- Community members are valued
- Your input helps improve the plugin

## 📚 Additional Resources

- [Installation Guide](installation.md)
- [Quick Start Guide](quick-start.md)
- [Troubleshooting Guide](troubleshooting.md)
- [Hooks Reference](hooks-reference.md)
- [Security Guide](security-guide.md)

---

**Need help?** We're here to help! Check our [Contact Information](#contact-information) or [GitHub Discussions](https://github.com/VoxHash/WPTelegram-Post-Notifier/discussions)

**Made with ❤️ by VoxHash**

*WP Telegram Post Notifier - Professional WordPress to Telegram integration!* 📱✨
