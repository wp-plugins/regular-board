=== Regular Board ===
Contributors: boyevul
Tags: anonymous, bbs, bulletin board system, forum
Requires at least: 3.8
Tested up to: 3.8
Stable tag: 1.13.3

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
* 1.13.3 SEVERAL queries have been removed or made more efficient.  
* 1.13.2 Some minor tweaks involving the options panel, as well as making posting ajaxified and some other misc. tweaks.
* 1.13.1 Some minor posting fixes, CSS changes, and fixes to user profiles/options saving.
* 1.13.0 Additional fixes and includes added; final public version.
* 1.12.0 #tagging added.
* 1.11.0 Threaded comments.
* 1.11.0 Users who have never made a post before will need their first post approved by a board moderator.  After this, they are allowed to post freely.
* 1.11.0 Post paging for anywhere that used post paging.
* 1.11.0 If a board is locked or logged only, posting should be restricted to the appropriate people.
* 1.11.0 Likewise, if a post's last reply was made by the poster, then quick reply will no longer post a new reply.
* 1.11.0 Wipe mechanisms.
* 1.11.0 Some minor CSS issues.
* 1.11.0 The ability to delete messages you had sent, and the message's read status will no longer be counted as such unless the recipient has actually read the message.
* 1.11.0 Board list (in the main options) will only return the fetched boards for the front page, which not only allows the board admin to restrict content on the front page to what they deem front page appropriate, but also actually puts this variable back into play as it was stripped out of circulation (for whatever reason) at some point in time and just forgotten about.
* 1.11.0 Show source on a comment shows the correct source ( whatever formatting was used instead of the HTML output of the comment itself, which was confusing some users as to why they couldn't use HTML tags when the comment source clearly showed HTML tags). 
* 1.10.1 Fix to wipe logic for full board wipe mechanism.
* 1.10.0 Minor bug fixes.
* 1.10.0 Board-wide wipe counter added (no need to set individual board counters if you don't want to)
* 1.10.0 User levels, total post count (overall, not only active)
* 1.10.0 Event tracking for hidden form fields bans, automutes, and user bans.
* 1.10.0 Banner linking.
* 1.09.0 Minor errors cleaned up.
* 1.08.0 timecircles replaces countdown to next wipe (per board)
* 1.07.0 profile options added
* 1.07.0 post actions re-worked
* 1.06.0 edit/delete checks username as well as password, as opposed to password.
* 1.06.0 heaven function improvement.
* 1.05.0 wp_hash also sanitized.
* 1.04.0 Ability to add completely useless ASCII art to header via meta tags (allowing for more than just a stupid doge).
* 1.03.0 _post_form added to children theming; countdown clock updated; CSS update; lazy load inclusion fixed.
* 1.02.0 Github->WordPress repo FOLDER change
* 1.01.0 Hotfix for multiple board installation activity loops.
* 1.00.0 Initial release.