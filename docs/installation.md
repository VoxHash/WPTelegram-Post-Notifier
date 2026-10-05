# 🚀 Installation Guide

This guide will walk you through installing WP Telegram Post Notifier on your WordPress site.

## 📋 Prerequisites

Before installing the plugin, ensure your WordPress site meets these requirements:

- **WordPress**: 6.3 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher (or MariaDB 10.1+)
- **Memory**: 128MB PHP memory limit (256MB recommended)
- **Disk Space**: 50MB available space

## 🔧 Installation Methods

### Method 1: WordPress Admin (Recommended)

1. **Download the Plugin**
   - Go to [GitHub Releases](https://github.com/VoxHash/WPTelegram-Post-Notifier/releases)
   - Download the latest `wp-telegram-post-notifier.zip` file

2. **Upload to WordPress**
   - Log in to your WordPress admin
   - Go to **Plugins > Add New**
   - Click **Upload Plugin**
   - Choose the downloaded zip file
   - Click **Install Now**

3. **Activate the Plugin**
   - Click **Activate Plugin** after installation
   - The plugin will be ready to use

### Method 2: FTP Upload

1. **Download and Extract**
   - Download the plugin zip file
   - Extract it to your local computer

2. **Upload via FTP**
   - Connect to your site via FTP
   - Navigate to `/wp-content/plugins/`
   - Upload the `wp-telegram-post-notifier` folder

3. **Activate in WordPress**
   - Go to **Plugins** in WordPress admin
   - Find "WP Telegram Post Notifier"
   - Click **Activate**

### Method 3: WP-CLI

```bash
# Download and install
wp plugin install https://github.com/VoxHash/WPTelegram-Post-Notifier/releases/latest/download/wp-telegram-post-notifier.zip

# Activate
wp plugin activate wp-telegram-post-notifier
```

## ⚙️ Initial Setup

After activation, you'll need to configure the plugin:

### 1. Access Settings
- Go to **Settings > Telegram Notifier** in WordPress admin
- You'll see the plugin's admin interface

### 2. Configure Bot Token
- Get a bot token from [@BotFather](https://t.me/BotFather) on Telegram
- Enter the token in the **Connection** tab
- Test the connection

### 3. Add Destinations
- Go to the **Destinations** tab
- Add your Telegram channels or chats
- Configure routing rules if needed

### 4. Customize Templates
- Go to the **Template** tab
- Customize your message templates
- Preview how they'll look

## 🔍 Verification

After installation, verify everything is working:

1. **Check Plugin Status**
   - Go to **Plugins** in WordPress admin
   - Ensure "WP Telegram Post Notifier" is active

2. **Test Connection**
   - Go to **Settings > Telegram Notifier**
   - Click **Test Connection** in the Connection tab
   - You should see a success message

3. **Send Test Message**
   - Go to the **Template** tab
   - Click **Send Test Message**
   - Check your Telegram channel/chat

## 🚨 Troubleshooting

### Common Installation Issues

#### Plugin Won't Activate
- **Check PHP version**: Ensure PHP 7.4+
- **Check memory limit**: Increase to 256MB
- **Check file permissions**: Ensure proper permissions
- **Check for conflicts**: Deactivate other plugins temporarily

#### Settings Page Not Loading
- **Check WordPress version**: Ensure 6.3+
- **Check for JavaScript errors**: Open browser console
- **Check file permissions**: Ensure proper permissions
- **Clear cache**: Clear any caching plugins

#### Bot Token Not Working
- **Verify token**: Check token with @BotFather
- **Check bot permissions**: Ensure bot is added to channel
- **Check network**: Ensure server can reach Telegram API
- **Check logs**: Look for error messages in logs

### Getting Help

If you encounter issues:
1. Check the [Troubleshooting Guide](troubleshooting.md)
2. Search [GitHub Issues](https://github.com/VoxHash/WPTelegram-Post-Notifier/issues)
3. Ask in [GitHub Discussions](https://github.com/VoxHash/WPTelegram-Post-Notifier/discussions)
4. Contact [support@voxhash.dev](mailto:support@voxhash.dev)

## 🔄 Updates

The plugin will notify you when updates are available:

1. **Automatic Updates** (if enabled)
   - WordPress will automatically update the plugin
   - No action required

2. **Manual Updates**
   - Download the latest version
   - Upload and replace the old version
   - The plugin will handle database updates automatically

## 🗑️ Uninstallation

To remove the plugin:

1. **Deactivate**
   - Go to **Plugins** in WordPress admin
   - Click **Deactivate** for WP Telegram Post Notifier

2. **Delete**
   - Click **Delete** to remove the plugin files
   - This will also remove all plugin data

### Data Cleanup

The plugin will automatically clean up:
- Plugin options and settings
- Custom database tables
- Scheduled actions
- Log files

## 📚 Next Steps

After successful installation:

1. **Read the [Quick Start Guide](quick-start.md)**
2. **Explore [Basic Usage](basic-usage.md)**
3. **Check [Templates & Tokens](templates-tokens.md)**
4. **Configure [Routing Rules](routing-rules.md)**

## 🎉 Congratulations!

You've successfully installed WP Telegram Post Notifier! The plugin is now ready to send WordPress notifications to your Telegram channels and chats.

---

**Need help?** Check our [Support Guide](getting-help.md) or [Contact Us](contact-information.md)

**Made with ❤️ by VoxHash**

*WP Telegram Post Notifier - Professional WordPress to Telegram integration!* 📱✨
