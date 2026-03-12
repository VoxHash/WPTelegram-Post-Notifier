# ⚡ Quick Start Guide

Get up and running with WP Telegram Post Notifier in just a few minutes!

## 🎯 What You'll Learn

By the end of this guide, you'll have:
- ✅ A working Telegram bot connected to your WordPress site
- ✅ Notifications automatically sent when you publish posts
- ✅ Custom message templates with dynamic content
- ✅ Basic routing rules for different content types

## 🚀 Step 1: Get a Telegram Bot

### 1.1 Create a Bot
1. Open Telegram and search for [@BotFather](https://t.me/BotFather)
2. Send `/newbot` command
3. Follow the instructions to create your bot
4. **Save the bot token** - you'll need it later

### 1.2 Add Bot to Your Channel
1. Create a Telegram channel or use an existing one
2. Add your bot to the channel as an admin
3. Give it permission to send messages
4. **Note the channel ID** (e.g., `@mychannel` or `-1001234567890`)

## ⚙️ Step 2: Configure the Plugin

### 2.1 Access Settings
1. Go to **Settings > Telegram Notifier** in WordPress admin
2. You'll see the plugin's modern admin interface

### 2.2 Set Up Connection
1. Go to the **Connection** tab
2. Enter your bot token from Step 1
3. Click **Test Connection**
4. You should see "Connection successful!" message

### 2.3 Add Destinations
1. Go to the **Destinations** tab
2. Click **Add Destination**
3. Enter:
   - **Name**: "My Channel" (or any name you prefer)
   - **Chat ID**: Your channel ID from Step 1
4. Click **Add Destination**

## 🎨 Step 3: Customize Your Messages

### 3.1 Create a Template
1. Go to the **Template** tab
2. You'll see a default template like:
   ```
   New post: {post_title}
   
   {post_excerpt}
   
   Read more: {post_url}
   ```

### 3.2 Customize the Template
Replace the template with your own:
```
🚀 New post on {site_name}!

📝 {post_title}
📄 {post_excerpt}

👤 By {post_author}
📅 {post_date}

🔗 Read more: {post_url}
```

### 3.3 Preview Your Template
1. Click **Preview** to see how it looks
2. The preview will show your template with sample data
3. Adjust as needed

## 🧪 Step 4: Test Everything

### 4.1 Send a Test Message
1. Go to the **Template** tab
2. Click **Send Test Message**
3. Check your Telegram channel - you should see the message!

### 4.2 Test with a Real Post
1. Go to **Posts > Add New** in WordPress
2. Create a new post with a title and content
3. Publish the post
4. Check your Telegram channel - the notification should appear!

## 🎯 Step 5: Advanced Configuration (Optional)

### 5.1 Set Up Routing Rules
1. Go to the **Routing Rules** tab
2. Create rules to send different content to different channels
3. Example: Send news posts to @news_channel, tech posts to @tech_channel

### 5.2 Configure Delivery Options
1. Go to the **Delivery Options** tab
2. Choose your preferred parse mode (MarkdownV2, HTML, or None)
3. Enable/disable features like silent notifications

### 5.3 Monitor with Logs
1. Go to the **Logs** tab
2. See all notification attempts and their status
3. Use this to troubleshoot any issues

## 🎉 Congratulations!

You've successfully set up WP Telegram Post Notifier! Your WordPress site will now automatically send notifications to your Telegram channel whenever you publish new content.

## 🔄 What Happens Next?

### Automatic Notifications
- **New posts** → Telegram notification
- **Updated posts** → Telegram notification (if enabled)
- **Scheduled posts** → Telegram notification when published

### Manual Notifications
- Use **Bulk Actions** to send notifications to multiple posts
- Use **Gutenberg sidebar** for post-level controls
- Use **Send Test Message** to test anytime

## 🛠️ Troubleshooting

### Common Issues

#### No Notifications Appearing
- Check bot token is correct
- Ensure bot is added to channel as admin
- Check channel ID format
- Look at logs for error messages

#### Template Not Rendering
- Check token syntax (use `{token_name}`)
- Verify parse mode settings
- Test with preview function

#### Connection Failed
- Verify bot token with @BotFather
- Check server can reach Telegram API
- Ensure bot is active and not banned

### Getting Help
- Check the [Troubleshooting Guide](troubleshooting.md)
- Search [GitHub Issues](https://github.com/VoxHash/WPTelegram-Post-Notifier/issues)
- Ask in [GitHub Discussions](https://github.com/VoxHash/WPTelegram-Post-Notifier/discussions)

## 📚 Next Steps

Now that you're up and running:

1. **Explore [Basic Usage](basic-usage.md)** - Learn about all the features
2. **Check [Templates & Tokens](templates-tokens.md)** - Master the templating system
3. **Set up [Routing Rules](routing-rules.md)** - Send different content to different channels
4. **Configure [Bulk Operations](bulk-operations.md)** - Manage multiple posts efficiently

## 🎯 Pro Tips

### 💡 Template Tips
- Use emojis to make messages more engaging
- Include relevant tokens for dynamic content
- Test with preview before saving
- Keep messages concise but informative

### 🔧 Configuration Tips
- Set up routing rules for different content types
- Use silent notifications for less important updates
- Monitor logs to track notification success
- Test regularly to ensure everything works

### 📱 Telegram Tips
- Use channel descriptions to explain the purpose
- Pin important messages in your channel
- Consider using multiple channels for different content types
- Engage with your audience in the channel

---

**Need help?** Check our [Support Guide](getting-help.md) or [Contact Us](contact-information.md)

**Made with ❤️ by VoxHash**

*WP Telegram Post Notifier - Professional WordPress to Telegram integration!* 📱✨
