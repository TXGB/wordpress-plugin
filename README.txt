=== Plugin Name ===
Contributors: txgb
Donate link: https://www.txgb.co.uk/
Tags: txgb, travel
Requires at least: 5.8.0
Tested up to: 5.8.1
Stable tag: 1.0.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Connect your Wordpress site to the Tourism Exchange Great Britain API. Import and enhance your content, show availabilities, and offer bookings.

== Description ==

Connect your Wordpress site to the Tourism Exchange Great Britain API. Import and enhance your content, show availabilities, and offer bookings.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `txgb` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create `archive-txgb_venue.php` and `single-txgb_venue.php` files in your theme
1. Place `<?php do_action('txgb_show_availability_form'); ?>` in your archive template to display the search form
1. Place `<?php do_action('txgb_show_availability'); ?>` in your single template to display the search form & available product list
1. Add your TXGB Distributor credentials in the TXGB Settings page
1. Import your chosen services as custom posts

== Frequently Asked Questions ==

= How do I disable the default styles? =

We offer some basic styles for your theme as part of the plugin. These were set up with TwentyTwentyOne in mind, and
probably aren't appropriate for a production environment. You can disable this stylesheet by setting a constant in your
`wp-config.php` like so:

```
define('TXGB_DEFAULT_STYLES', false);
```

= Testing with the TXGB UAT environment =

You can override the production settings by defining a constant in your `wp-config.php`. This will set the appropriate URLs and add an "include test entities" flag in your requests.

```
define('TXGB_IN_PRODUCTION', false);
```

== Screenshots ==

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==


== Arbitrary section ==

