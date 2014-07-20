=== Regular Board ===
Contributors: boyevul
Tags: anonymous, bbs, bulletin board system, forum
Requires at least: 3.8.2
Tested up to: 3.9
Stable tag: 2.00.0.1

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
* 2.00.0.1  Vine.co and Funnyordie.com added to sites that can be embedded.
* 2.00.0.1  Colors altered to be more dependent on the theme that is currently being used.
* 2.00.0.1  get_permalink() used in place of get_site_url() for all/return links.
* 2.00.0.0  Something went horribly wrong with previous versions; this will fix that.