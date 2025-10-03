# Vanilla Bean Slack Hooker

![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.4%2B-blue)
![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-purple)
![License](https://img.shields.io/badge/License-GPL%20v2%2B-green)
![Version](https://img.shields.io/badge/Version-5.5.2-orange)

Automated notifications from your WordPress site to Slack, Mattermost, or any webhook-compatible service. Monitor your website activity with real-time alerts for posts, comments, WooCommerce sales, plugin changes, and custom events.

## 🚀 Features

- **Real-time WordPress Notifications**: Post status changes, comments, user registrations
- **WooCommerce Integration**: Sales notifications with order details
- **Plugin Management Alerts**: Install, update, and deletion notifications
- **Multiple Webhook Support**: Send to Slack, Mattermost, Discord, or custom endpoints
- **Formatted Data Messages**: Rich attachments with structured name-value pairs *(NEW in 5.5.2)*
- **Smart Queueing**: Non-blocking message delivery with optional immediate sending
- **Email Fallback**: Send notifications via email when webhooks aren't available
- **Shortcode Support**: Easy integration in posts and pages
- **Programmatic API**: Full developer access with PHP functions

## 📦 Installation

### Via WordPress Admin (Recommended)
1. Navigate to **Plugins > Add New**
2. Search for "Vanilla Bean Slack Hooker"
3. Click **Install Now** and then **Activate**

### Manual Installation
1. Download the plugin ZIP file
2. Upload to `/wp-content/plugins/vanilla-bean-slack-hooker/`
3. Activate through the **Plugins** menu in WordPress

### Configuration
1. Go to **Settings > Slack Hooker** in your WordPress admin
2. Add your webhook URLs (Slack, Mattermost, etc.)
3. Configure notification preferences
4. Test your setup with the built-in test message feature

## 🔧 Basic Usage

### Shortcodes

**Simple Message:**
```
[slackhooker text="Hello from WordPress!"]
```

**With Custom Channel and Username:**
```
[slackhooker text="Site update complete" channel="general" username="WordPress Bot"]
```

**Send Immediately (Skip Queue):**
```
[slackhooker text="Critical alert!" sendnow="true"]
```

**Multiple Webhooks:**
```
[slackhooker text="Important notice" alsoto="https://hooks.slack.com/services/YOUR/WEBHOOK/URL"]
```

### Programmatic Usage

**Simple Text Message:**
```php
if (function_exists('\VanillaBeans\SlackHooker\vbean_slackhooker')) {
    \VanillaBeans\SlackHooker\vbean_slackhooker('System backup completed successfully');
}
```

**With Custom Parameters:**
```php
if (function_exists('\VanillaBeans\SlackHooker\vbean_slackhooker')) {
    \VanillaBeans\SlackHooker\vbean_slackhooker(
        'New user registered',
        'general',           // channel
        'WordPress',         // username
        ':wave:',           // icon
        false,              // send now (false = queue)
        null,               // postto URLs
        null                // alsoto URLs
    );
}
```

## 🎨 Formatted Data Messages (NEW in 5.5.2)

Create rich, structured notifications with name-value pairs that display beautifully in Slack and other platforms.

### Basic Data Message
```php
if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
    $data = array(
        'User ID' => '12345',
        'Email' => 'user@example.com',
        'Registration Date' => date('Y-m-d H:i:s'),
        'User Role' => 'Subscriber'
    );
    
    \VanillaBeans\SlackHooker\vbean_send_data('New User Registration', $data);
}
```

### Advanced Formatted Message
```php
if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
    $order_data = array(
        'Order ID' => '#WC-12345',
        'Customer' => 'John Doe',
        'Total Amount' => '$299.99',
        'Payment Method' => 'Credit Card',
        'Shipping Address' => '123 Main St, City, State'
    );
    
    $options = array(
        'color' => '#00ff00',                    // Green for success
        'pretext' => '🛒 New WooCommerce Sale!',
        'text' => 'Payment processed successfully',
        'title_link' => admin_url('edit.php?post_type=shop_order'),
        'author_name' => get_bloginfo('name'),
        'author_link' => home_url()
    );
    
    \VanillaBeans\SlackHooker\vbean_send_data('E-commerce Sale', $order_data, $options);
}
```

### Custom Attachment Building
```php
if (function_exists('\VanillaBeans\SlackHooker\vbean_build_data_message')) {
    $system_data = array(
        'Server' => 'web-01.example.com',
        'CPU Usage' => '85%',
        'Memory Usage' => '3.2GB / 4GB',
        'Disk Usage' => '75% full',
        'Last Backup' => '2025-10-03 02:00:00'
    );
    
    $message = \VanillaBeans\SlackHooker\vbean_build_data_message(
        'System Status Alert',
        $system_data,
        array('color' => '#ff9900') // Orange for warning
    );
    
    // Send using core notification system
    Vanilla_Bean_Slack_Hooker::notification_send($message);
}
```

## 🎯 Use Cases

### E-commerce Monitoring
```php
// WooCommerce order completion
add_action('woocommerce_order_status_completed', function($order_id) {
    $order = wc_get_order($order_id);
    
    $order_data = array(
        'Order' => '#' . $order->get_order_number(),
        'Customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'Total' => $order->get_formatted_order_total(),
        'Items' => $order->get_item_count(),
        'Date' => $order->get_date_created()->format('Y-m-d H:i:s')
    );
    
    if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
        \VanillaBeans\SlackHooker\vbean_send_data(
            '💰 Order Completed',
            $order_data,
            array('color' => '#36a64f')
        );
    }
});
```

### User Activity Tracking
```php
// New user registration
add_action('user_register', function($user_id) {
    $user = get_userdata($user_id);
    
    $user_data = array(
        'Username' => $user->user_login,
        'Email' => $user->user_email,
        'Display Name' => $user->display_name,
        'Registration IP' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    );
    
    if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
        \VanillaBeans\SlackHooker\vbean_send_data(
            '👤 New User Registration',
            $user_data,
            array('color' => '#0073aa')
        );
    }
});
```

### System Monitoring
```php
// Custom system health check
add_action('wp_loaded', function() {
    if (wp_next_scheduled('custom_health_check')) {
        return;
    }
    wp_schedule_event(time(), 'hourly', 'custom_health_check');
});

add_action('custom_health_check', function() {
    $health_data = array(
        'Memory Usage' => size_format(memory_get_peak_usage(true)),
        'Database Size' => size_format(get_db_size()),
        'Active Plugins' => count(get_option('active_plugins')),
        'WordPress Version' => get_bloginfo('version'),
        'PHP Version' => PHP_VERSION
    );
    
    if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
        \VanillaBeans\SlackHooker\vbean_send_data(
            '🔍 System Health Check',
            $health_data,
            array('color' => '#17a2b8')
        );
    }
});
```

## ⚙️ Configuration Options

### Message Timing
- **Queue Messages** (default): Messages sent via WordPress cron for non-blocking delivery
- **Send Immediately**: Override queueing with `sendnow="true"` or `$now=true`
- **Global Setting**: Configure default timing behavior in admin settings

### Endpoint Configuration
- **Multiple Webhooks**: Send to Slack, Mattermost, Discord simultaneously
- **Channel Targeting**: Direct messages to specific channels
- **Alert Mentions**: Add `@here`, `@channel`, or `@everyone` notifications
- **Email Fallback**: Use email addresses as endpoints

### Message Formatting Options
| Option | Description | Example |
|--------|-------------|---------|
| `color` | Attachment border color | `#36a64f`, `good`, `warning`, `danger` |
| `pretext` | Text above the attachment | `"🚨 Critical Alert!"` |
| `title` | Main attachment title | `"System Status"` |
| `title_link` | Make title clickable | `admin_url('dashboard.php')` |
| `text` | Additional descriptive text | `"All systems operational"` |
| `author_name` | Author display name | `get_bloginfo('name')` |
| `author_link` | Author link URL | `home_url()` |
| `footer` | Footer text | `"Powered by WordPress"` |
| `footer_icon` | Footer icon URL | `"https://example.com/icon.png"` |

## 🔌 Integration Examples

### Contact Form 7 Integration
```php
add_action('wpcf7_mail_sent', function($contact_form) {
    $submission = WPCF7_Submission::get_instance();
    $posted_data = $submission->get_posted_data();
    
    $form_data = array(
        'Form' => $contact_form->title(),
        'Name' => $posted_data['your-name'] ?? 'Not provided',
        'Email' => $posted_data['your-email'] ?? 'Not provided',
        'Message' => substr($posted_data['your-message'] ?? '', 0, 100) . '...',
        'Submitted' => current_time('Y-m-d H:i:s')
    );
    
    if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
        \VanillaBeans\SlackHooker\vbean_send_data(
            '📧 New Contact Form Submission',
            $form_data,
            array('color' => '#7e57c2')
        );
    }
});
```

### Security Monitoring
```php
// Failed login attempts
add_action('wp_login_failed', function($username) {
    $security_data = array(
        'Failed Username' => $username,
        'IP Address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'Timestamp' => current_time('Y-m-d H:i:s'),
        'Referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct'
    );
    
    if (function_exists('\VanillaBeans\SlackHooker\vbean_send_data')) {
        \VanillaBeans\SlackHooker\vbean_send_data(
            '🚨 Failed Login Attempt',
            $security_data,
            array('color' => 'danger')
        );
    }
});
```

## 🛠️ API Reference

### Core Functions

#### `vbean_slackhooker($message, $channels, $username, $icon, $now, $postto, $alsoto)`
Send basic text messages with full parameter control.

#### `vbean_send_data($title, $data, $options, $endpoints, $endpointOptions)`
Send formatted data messages with structured fields.

#### `vbean_build_data_message($title, $data, $options)`
Build attachment arrays for custom processing.

#### `Vanilla_Bean_Slack_Hooker::notification_send($message, $endpoints, $endpointOptions)`
Core notification function supporting both text and attachment messages.

### Parameters

- **$message** (string|array): Text message or attachment array
- **$title** (string): Main title for data messages
- **$data** (array): Associative array of name => value pairs
- **$options** (array): Formatting options (color, pretext, etc.)
- **$endpoints** (mixed): Custom webhook URLs
- **$endpointOptions** (string): 'defaults', 'override', or 'add'

## 🤝 Support

- **Documentation**: [Velvary WordPress Plugins](https://www.velvary.com.au/wordpress_plugins/slackhooker)
- **Support Email**: support@velvary.com.au
- **Issues**: Contact via [Velvary Contact Form](http://www.velvary.com.au/contact-us)
- **Twitter**: [@VelvaryVanilla](https://twitter.com/VelvaryVanilla)

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2025 Velvary Pty Ltd

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🔄 Changelog

### 5.5.2 (2025-10-03)
- ✨ **NEW**: Formatted data message support with `vbean_send_data()`
- ✨ **NEW**: Rich attachment builder with `vbean_build_data_message()`
- ✨ **NEW**: Structured name-value pair display in Slack/Mattermost
- 🔧 Enhanced MAIASS configuration support
- 📖 Updated documentation with comprehensive examples

### 5.5.0 (2025-04-21)
- 🐛 Fixed overly zealous escaping issues

### 5.4.6 (2025-04-21)
- 🔒 Security updates and input filtering
- ✅ Tested with WordPress 6.8 and PHP 8.2

---

**Made with ❤️ by [Velvary](https://www.velvary.com.au) - Vanilla Beans for WordPress**
