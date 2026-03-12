# Troubleshooting

Common errors and fixes.

## Bot Not Sending Messages

**Issue**: No messages appear in Telegram despite successful connection.

**Solutions**:
1. Verify bot is added to channel as admin
2. Check channel ID format (`@channelname` or `-1001234567890`)
3. Review logs for error messages

## Template Not Rendering

**Issue**: Tokens show as `{token_name}` instead of values.

**Solutions**:
1. Check token syntax (use `{token_name}` format)
2. Verify parse mode settings
3. Test with preview function

## Connection Failed

**Issue**: Cannot connect to Telegram API.

**Solutions**:
1. Verify bot token with @BotFather
2. Check server can reach Telegram API
3. Ensure bot is active and not banned
