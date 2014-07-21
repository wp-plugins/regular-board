=== Regular Board ===
Contributors: boyevul
Tags: anonymous, bbs, bulletin board system, forum
Requires at least: 3.8.2
Tested up to: 3.9
Stable tag: 2.00.0.2

Text-based anonymous forum.

== Description ==
An extremely simple, text-based forum to which anyone may post to or share links on.

= More Information = 
Regular Board attempts to take a fresh approach to the WordPress forum by reducing the amount of overhead, features, 
and database additions by reducing every element of the forum itself to its simplest (and, more importantly, most necessary) 
features, while retaining the functionality we have all come to expect from a vanilla forum installation.

IF UPGRADING, READ THIS:
v2 is completely re-written from the ground up to address many problems with the v1 branch of the plugin. It 
is not backwards compatible with the previous versions, and if you have previously installed Regular Board, 
it WILL delete everything from the original installation (to clean up after itself) and upgrade you to the 
newest platform.

= Requirements = 
* WordPress 3.9.1
* cURL
* PHP5+
* "Pretty Permalinks" (not default WordPress permalink structure)

= Features = 
* cleaner code
* DNSBL/SURBL integration
* front-end moderation (no admin panel)
* embedding from imgur, soundcloud, vimeo, youtube, remote images, and gfycat (among others)

== Installation ==
1. Make sure permalinks aren't default ("pretty permalinks").
2. Place the contents of /regular-board/ into your plugins folder.
3. Activate the plugin.
4. Add [regular board] (shortcode) to a page or post.
5. Done.

== Changelog ==
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