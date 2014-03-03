=== Regular Board ===
Contributors: boyevul
Tags: anonymous, bbs, bulletin board system, forum
Requires at least: 3.8
Tested up to: 3.8
Stable tag: 1.06

== Description ==
Regular Board is a text-based anonymous message board to which anyone may post without the need for account registration.  

= More Information = 
Regular Board is a re-write of the original code found on My Optional Modules (WP Plugin).  (The two are 
non-compatible).  This standalone version seeks to remedy a few problems found in the original code, while 
expanding on its functionality and offering more than the original installation did.

As this is an anonymous bbs, when a user is automatically registered (an account is created for their 
IP), the IP should be stored in a hashed format in the database.  Under no circumstances should any one
be able to glance at the database and see where the posts are originating from.  When comparing IP addresses 
to those in the database, again, the comparison is made with a hashed version of the current IP.  In the 
same respect, passwords are also stored in their hashed format.

All Javascript should degrade gracefully, and the board itself should fit into the width of any container, 
and not be offset by the current theme's style.

= Features = 
* DNSBL and Akismet integration
* Thread and reply edit capabilities
* Appoint WordPress users as janitors and moderators
* Appoint Regular Board users as janitors and moderators
* Wipe (any) board on the installation on a regular basis with a timed interval
* Open-graph and meta integration for board and threads
* Upload-to-Imgur capabilities
* Easy-to-use admin interface 
* Easily deployed via shortcode ([regular_board])
* User IDs used cross-board
* User IPs are stored in a single location and hashed (not stored as plain text or long).
* Templating allows the admin to copy certain files to a separate directory and alter the way Regular Board can be interacted with.

== Installation ==
1. Place the contents of /regular-board/ into your plugins folder.
2. Activate the plugin.
3. Navigate to Regular Board under Settings.
4. Read the agreement/information/warning.  Click Proceed with installation.
5. Create a new page, and place the shortcode on it: [regular_board].
6. Tweak whatever settings you wish to change.
7. Done.

== Changelog ==
* 1.06 edit/delete checks username as well as password, as opposed to password.
* 1.06 heaven function improvement.
* 1.05 wp_hash also sanitized.
* 1.04 Ability to add completely useless ASCII art to header via meta tags (allowing for more than just a stupid doge).
* 1.03 _post_form added to children theming; countdown clock updated; CSS update; lazy load inclusion fixed.
* 1.02 Github->WordPress repo FOLDER change
* 1.01 Hotfix for multiple board installation activity loops.
* 1.00 Initial release.