=== External Links to New Window ===
Contributors: etalented
Plugin URI: https://etalented.co.uk/wordpress-plugin-external-links-to-new-window/
Tags: links, external, seo, optimized, new window, new tab, nofollow
Requires at least: 3.2.0
Tested up to: 4.9.6
Stable tag: 2.0.4
Requires PHP: 5.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Open all external links in your blog posts and pages automatically in a new tab or new window when clicked or tapped.

== Description ==

This plugin will solve the problem of manually changing all your external links to open in a new window by doing it automatically for you...for all external links in all posts and pages!

This plugin uses standard HTML and doesn't introduce any more JavaScript into your already JavaScript heavy WordPress site. It also uses very minimal styling (CSS) to display a new window icon, which is embedded and not externally linked.

The display of the new window icon can be changed in the settings page and you can also add the `rel="nofollow"` attribute. [Read more about `rel="nofollow"` on Wikipedia](https://en.wikipedia.org/wiki/Nofollow).

Updates to the plugin will be posted on the [Etalented website](https://etalented.co.uk/wordpress-plugin-external-links-to-new-window) as well as to the [WordPress.org Plugin Directory](https://en-gb.wordpress.org/plugins/external-links-to-new-window/).

Originally created by [Christopher Ross](http://thisismyurl.com) and now authored by [Etalented](https://etalented.co.uk).


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->External Links screen to configure the plugin

== Frequently Asked Questions ==

= Do the external links have any CSS classes? =

Yes. There are 2: `thisismyurl_external` to support sites that use the old version of the plugin; and `external-links-new-window` for new installations and for those that wish to upgrade.

= Will it work for mobile? =

Yes. The plugin uses HTML standards to that it works on mobile and desktop the same.

= Will it open a new window or new tab? =

That depends. It is down to the users settings in their browser whether a new window or new tab is opened. The default behaviour is for a new tab to open.

== Screenshots ==

1. Plugin settings
1. An example blog post with external link

== Changelog ==

= 2.0.4 =

* code refactor
* admin settings page re-design
* new readme and contributing docs

= 2.0.3 =

* code refactor
* unit testing
* admin settings page re-design
* new `external-link-new-window` class for external links (whilst still supporting legacy class)
* changing new window icon to CSS encoded for better performance

= 2.0.2 =

* under new ownership

= 2.0.0 =
 
* added new admin menu for easier management
* removed footer comment
* combined settings into single option
* removed common file
* added screenshot

= 1.1.1 =

* lowered bandwidth of icon
* CSS now only appearing when image included

= 1.1 =

* added new WP menu

== Upgrade Notice ==

= 2.0.4 =

Upgrade to get the latest support information and features.