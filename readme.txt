=== Regular Board ===
Contributors: boyevul
Tags: anonymous, bbs, bulletin board system, forum
Requires at least: 3.8.2
Tested up to: 3.8.2
Stable tag: 1.13.8.0

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

= One Form To Rule Them All = 
Regular Board uses a non-standard approach to the traditional submission form in that accepts specialized 
formatting commands to set things like link embedding, titles, and so-forth.

* :: ++URL++ embeds a URL (if URLs are activated on your installation)
* :: !sage and !heaven will reply without bumping and post anonymously
* :: ^#^ will allow you to reply to a particular comment without opening the branch
* :: [[board_name]] will post your new thread to that particular board
* :: [[title: your title]] will name your thread or reply
* :: #tag will create a hyperlink to a tag page with results related to that tag
* :: ||, |||| are a new line or a new paragraph (carriage returns are converted to ||s upon submission)
* :: &#42;&#42;bold&#42;&#42;, &#42;italic&#42;, &#42;&#42;&#42;bold and italic&#42;&#42;&#42;, ~~strikethrough~~, &#96;code&#96;, [spoiler]spoiler![/spoiler]
* :: ---- (horizontal line)

= Embeds from = 
* Automatically converts links from these sources into embeds:
* :: Imgur (and image links)
* :: Soundcloud
* :: Vimeo
* :: Youtube
* :: gfycat

= Features = 
* Out-of-the-box and running with:
* :: integrated with DNSBL and Akismet
* :: editing capabilities for users with passwords/usernames
* :: (optional) ability to appoint janitors and moderators
* :: (optional) board wiper that deletes threads/boards on a timed response system
* :: open-graph integration for threads and boards for social shares
* :: (optional) imgur uploader form
* :: (optional) user IDs
* :: wp_hashed (anything sensitive) so user data is a tad more secure
* :: all replies can be branched into their own threads, allowing for deeper conversation

== Installation ==
1. Place the contents of /regular-board/ into your plugins folder.
2. Activate the plugin.
3. Navigate to Regular Board under Settings.
4. Read the agreement/information/warning.  Click Proceed with installation.
5. Create a new page, and place the shortcode on it: [regular_board].
6. Tweak whatever settings you wish to change.
7. Done.

== Changelog ==
* 1.13.8.0  CSS cleanup.  Function wrappers.  gfycat embedding.  guest posts counted for unique posters.
* 1.13.8.0  akismet class removed as latest akismet update interfered with it.
* 1.13.7.0  Board Width is a little more generous, with a maximum width of 800px that scales down appropriately.
* 1.13.7.0  Regular Board Widget added to allow the easy inclusion of elements in the sidebar via widget.
* 1.13.7.0  No longer takes control of the body background color of the page that it's on.
* 1.13.7.0  Canonical linking wasn't behaving like it should have; this has been fixed.
* 1.13.7.0  Floating the reply form was doing more harm than good for users with smaller screens.
* 1.13.6.0  Thread branching/threaded comments (minor fix) Soundcloud embedding via URL (addition)
* 1.13.6.0  No index, no follow can now be applied to only specific boards
* 1.13.6.0  Form, if posting to a board, will retain the default board [[tag]] for ease of usability
* 1.13.6.0  Carriage returns (/r,/n) will convert to || (Regular Board's default for display a line-break)
* 1.13.5.0  Double-output post expansion (fixed) Thread titles being replaces by reply titles (fixed)
* 1.13.5.0  Double time stamps for replies (fixed) Multiple instances of certain reply elements resulted in unintended output (fixed)
* 1.13.5.0  replies section, history and user profiles: comments expanded by default
* 1.13.4.1  Content submission form condensed to a single element.  (See http://www.dailyprune.com/?a=news&post=127#formatting for information on formatting).  
* 1.13.4.0  Upgrade procedure fixed.  New options added.  Two CSS styles (nightmode/daymode).
* 1.13.3.0  SEVERAL queries have been removed or made more efficient.  
* 1.13.2.0  Some minor tweaks involving the options panel, as well as making posting ajaxified and some other misc. tweaks.
* 1.13.1.0  Some minor posting fixes, CSS changes, and fixes to user profiles/options saving.
* 1.13.0.0  Additional fixes and includes added; final public version.
* 1.12.0.0  #tagging added.
* 1.11.0.0  Threaded comments.
* 1.11.0.0  Users who have never made a post before will need their first post approved by a board moderator.  After this, they are allowed to post freely.
* 1.11.0.0  Post paging for anywhere that used post paging.
* 1.11.0.0  If a board is locked or logged only, posting should be restricted to the appropriate people.
* 1.11.0.0  Likewise, if a post's last reply was made by the poster, then quick reply will no longer post a new reply.
* 1.11.0.0  Wipe mechanisms.
* 1.11.0.0  Some minor CSS issues.
* 1.11.0.0  The ability to delete messages you had sent, and the message's read status will no longer be counted as such unless the recipient has actually read the message.
* 1.11.0.0  Board list (in the main options) will only return the fetched boards for the front page, which not only allows the board admin to restrict content on the front page to what they deem front page appropriate, but also actually puts this variable back into play as it was stripped out of circulation (for whatever reason) at some point in time and just forgotten about.
* 1.11.0.0  Show source on a comment shows the correct source ( whatever formatting was used instead of the HTML output of the comment itself, which was confusing some users as to why they couldn't use HTML tags when the comment source clearly showed HTML tags). 
* 1.10.1.0  Fix to wipe logic for full board wipe mechanism.
* 1.10.0.0  Minor bug fixes.
* 1.10.0.0  Board-wide wipe counter added (no need to set individual board counters if you don't want to)
* 1.10.0.0  User levels, total post count (overall, not only active)
* 1.10.0.0  Event tracking for hidden form fields bans, automutes, and user bans.
* 1.10.0.0  Banner linking.
* 1.09.0.0  Minor errors cleaned up.
* 1.08.0.0  timecircles replaces countdown to next wipe (per board)
* 1.07.0.0  profile options added
* 1.07.0.0  post actions re-worked
* 1.06.0.0  edit/delete checks username as well as password, as opposed to password.
* 1.06.0.0  heaven function improvement.
* 1.05.0.0  wp_hash also sanitized.
* 1.04.0.0  Ability to add completely useless ASCII art to header via meta tags (allowing for more than just a stupid doge).
* 1.03.0.0  _post_form added to children theming; countdown clock updated; CSS update; lazy load inclusion fixed.
* 1.02.0.0  Github->WordPress repo FOLDER change
* 1.01.0.0  Hotfix for multiple board installation activity loops.
* 1.00.0.0  Initial release.