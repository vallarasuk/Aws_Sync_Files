=== AWS Image Sync ===
Contributors: [Vallarasu K]
Donate link: https://vallarasuk.com/
Tags: backup, AWS, S3, images, sync, cloud storage
Requires at least: 5.0
Tested up to: 6.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Effortlessly sync and backup your WordPress images to an AWS S3 bucket, organized by month and year.

== Description ==

AWS Image Sync is a powerful plugin that allows you to sync your WordPress media library images to an Amazon S3 bucket. The images are organized in a structured format, with separate folders for each month and year, ensuring easy management and retrieval.

### Features:
* Automatic syncing of all WordPress images to an S3 bucket.
* Organized backup with month-wise and year-wise folders.
* Easy setup and configuration.
* Secure storage solution leveraging AWS S3.

== Installation ==

1. Upload the `aws_image_sync` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your AWS S3 settings in the plugin settings page.
4. Sync your images manually or set up automatic syncing.

== Frequently Asked Questions ==

= How do I configure my AWS S3 settings? =

Go to the plugin settings page in your WordPress dashboard, and enter your AWS S3 credentials including the bucket name, access key, and secret key.

= Can I sync existing images? =

Yes, the plugin allows you to sync all existing images in your media library.

= How often are the images synced? =

You can configure the syncing frequency in the plugin settings, including options for manual syncing and automatic syncing at regular intervals.

== Screenshots ==

1. **Plugin Settings Page:** Configure your AWS S3 credentials and other settings.
2. **Sync Dashboard:** View sync status and logs.

== Changelog ==

= 1.0.0 =
* Initial release of AWS Image Sync.
* Sync WordPress images to S3 with month-wise and year-wise backup.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Install to start syncing your images to AWS S3.

== Arbitrary section ==

### Future Plans:
We plan to add more features such as:
* Selective folder syncing.
* Advanced logging and reporting.
* Support for other cloud storage providers.

== A brief Markdown Example ==

Ordered list:

1. Install the plugin.
2. Configure your settings.
3. Start syncing your images.

Unordered list:

* Easy setup
* Secure backup
* Organized storage

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up for **strong**.

`<?php code(); // goes in backticks ?>`
