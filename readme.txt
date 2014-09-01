=== Regular Board ===
Contributors: boyevul
Tags: anonymous, bbs, bulletin board system, forum
Requires at least: 3.9.1
Tested up to: 4.0
Stable tag: 2.00.1

Text-based anonymous forum.

== Description ==
An extremely simple, text-based forum to which anyone may post to or share links on.

= More Information = 
Regular Board attempts to take a fresh approach to the WordPress forum by reducing the amount of overhead, features, 
and database additions by reducing every element of the forum itself to its simplest (and, more importantly, most necessary) 
features, while retaining the functionality we have all come to expect from a vanilla forum installation.

= Requirements = 
* WordPress 3.9.1
* cURL
* PHP5+
* "Pretty Permalinks" (not default WordPress permalink structure)

= Features = 
* cleaner code
* DNSBL/SURBL integration
* front-end moderation (no admin panel)
* embedding enabled for Imgur, Youtube, LiveLeak, Soundcloud, gfycat, Funny or Die, and Vine.

== Installation ==
1. Make sure permalinks aren't default ("pretty permalinks").
2. Place the contents of /regular-board/ into your plugins folder.
3. Activate the plugin.
4. Add [regular board] (shortcode) to a page or post.
5. You'll find settings under Dashboard->Settings->Regular Board
6. Done.

== Changelog ==
* 2.00.1    Options page added.
* 2.00.1     - Set whether or not users must be logged in to participate.
* 2.00.1     - Set whether or not to ignore the DNSBL blocking functionality.
* 2.00.0.9  Time-positioned embeds for Youtube (&t=#m#s) enabled.
* 2.00.0.8  Individual board functionality added, allowing users to specify where they would like to post.
* 2.00.0.8  Remove old upgrade procedures.
* 2.00.0.7  Security Update: ?b URL paramter not being utilized properly (missing quotes).
* 2.00.0.6  Fitvids (//fitvidsjs.com/) added for media embeds.
* 2.00.0.6  oEmbeds ditched. Previous method reinstated: imgur, youtube, liveleak, soundcloud, gfycat, funnyordie, vine supported.
* 2.00.0.6  Reply mode toggle form altered; script.js added to handle all board-related scripts
* 2.00.0.5  Closed an open form on the bans area (affected admin only)
* 2.00.0.5  CSS moved to include (instead of being output into header)
* 2.00.0.4  error involving got while browsing page numbers fixed
* 2.00.0.3  oEmbeds now favoured over previous embed code. http://codex.wordpress.org/Embeds (because I'm the idiot who tried to reinvent the wheel when the wheel was already round enough)
* 2.00.0.2  Better embed determination based on preg_match rulesets.
* 2.00.0.2  Text-posts should now be classified correctly when being submitted.
* 2.00.0.2  Media embeds use the div container "mediaEmbed" for those of you using a plugin like Fitvids that specifically targets media containers to manipulate content. (.mediaEmbed)
* 2.00.0.2  Load the CSS only when we need it (instead of on every page).
* 2.00.0.2  Post type links hidden if no post types of that value exist on the board. Display post count in link.
* 2.00.0.2  Reply form hidden by default (with javascript enabled in browser) and expanded upon click.
* 2.00.0.2  ?u=anonymous now correctly brings up posts submitted by users who neglected to enter a name (instead of returning nothing).
* 2.00.0.1  Vine.co and Funnyordie.com added to sites that can be embedded.
* 2.00.0.1  Colors altered to be more dependent on the theme that is currently being used.
* 2.00.0.1  get_permalink() used in place of get_site_url() for all/return links.
* 2.00.0.0  Something went horribly wrong with previous versions; this will fix that.