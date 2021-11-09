=== Plugin Name ===
Contributors: txgb
Donate link: https://www.txgb.co.uk/
Tags: txgb, travel
Requires at least: 5.8.0
Tested up to: 5.8.1
Stable tag: 0.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.

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

You can override the production settings by passing in the URLs as constants in your `wp-config.php`.

```
define('TXGB_WSDL_ENTITY', 'https://uatbook.txgb.co.uk/v4/Services/EntityService.asmx?WSDL');
define('TXGB_WSDL_SEARCH', 'https://uatapis.txgb.co.uk/CABS.WebServices/SearchService.asmx?WSDL');
define('TXGB_PAYMENT_URL', 'https://uatbook.txgb.co.uk/v4/Services/Injection.aspx');
```

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`
