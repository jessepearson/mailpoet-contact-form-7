=== Mailpoet - Contact Form 7 Integration ===
Contributors: jessepearson, bftrick, wysija
Donate link: https://jessepearson.net/donate/
Tags: form, forms, contact form, mailpoet, wysija, contact form 7, newsletters, email
Requires at least: 3.7.1
Tested up to: 4.4.2
Stable tag: 1.0.7.6

License: GPLv2 or later


== Description ==

MailPoet is a free newsletter and post notification plugin for WordPress that makes it really simple to send out email newsletters to your subscription lists. This plugin integrates Contact Form 7 with MailPoet by providing an option for your customers to signup for your newsletter lists while submitting a form.

Please see the extensive installation / setup instructions to set up your form correctly.

Feel free to add feature requests or bugs to [our GitHub page](https://github.com/jessepearson/mailpoet-contact-form-7). Support requests should go in the support forum.

= Features =

* Allow your users to sign up for a Mailpoet newsletter list using a Contact Form 7 form
* You can capture first name, last name, and (of course) email
* You can signup users to as many lists as you like
* You can set up the form to opt in or opt out


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `mailpoet-contact-form-7` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

= Form Setup =

After installing & activating the plugin it's time to set up your form.

1. Click on Contact in the WordPress admin
1. Edit an existing form or create a new one by clicking on Add New in the WordPress admin menu
1. Add your fields
1. Add a text field named `your-name`
1. Add an email field named `your-email`
1. Add a MailPoet Signup field named `mailpoetsignup`
1. When you're adding the MailPoet Signup field you can select any number of lists you want the user to be assigned to
1. You can also choose to make the user opt in or opt out


== Screenshots ==

1. A sample Contact Form 7 form all ready to go.
1. A view of the MailPoet Signup Tag Generator


== Changelog ==
= 1.0.7.5 =
* Added checkbox to allow user to move signup checkbox label into the wrapping span

= 1.0.7.4 =

= 1.0.7.3 =
* Fixed so user can set their signup field name to anything they choose
* Began removing legacy CF7 support ( versions < 3.9 )

= 1.0.7.2 =
* Fixed number field conflict. 
* Added code notes.
* Updated screenshot-2.png

= 1.0.7.1 =
* Fixed directory errors.

= 1.0.7 =
* Added class checking to make sure fatal errors are not thrown if CF7 classes do not exist.

= 1.0.6 =
* Updated tag generator form to new CF7 standard
* Fixed css id output for signup checkbox

= 1.0.5 =
* Display list names in admin notifications

= 1.0.4 =
* Fix users are always forced to subscribe to Mailpoet

= 1.0.3 =
* Now compatible with the latest version of CF7 3.9

= 1.0.2 =
* Fixed subscribers not being subscribed in the latest version 3.8.1 of CF7

= 1.0.1 =
* Tweak - Adding screenshots

= 1.0.0 =
* Initial release