=== Stronger GitHub Widget ===
Contributors: Potsky
Donate link: http://www.potsky.com/donate/
Tags: widget, github, cache, fast, events, Repositories, Subscriptions, Events, Starred, Followers, Following
Requires at least: 3.3
Tested up to: 3.5
Stable tag: 0.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A plugin to display your Github informations.

== Description ==

A plugin to display your Github informations.

You can display :

* Repositories
* Subscriptions
* Events
* Starred
* Followers
* Following

It uses a server cache to be very light and fast on your WordPress installation.

It verifies the username on saving settings.

Don't hesitate to ask me new features or report bugs on [potsky.com](http://www.potsky.com/code/wordpress-plugins/stronger-github-widget/ "Plugin page") !  


I don't code these GitHub events because I did't find any informations about the json structure :

* `GollumEvent`
* `MemberEvent`
* `PublicEvent`
* `TeamAddEvent`

So if you want a better display for these events, send my an API url with these events.
Something like : https://api.github.com/users/MAGICALUSER/events


== Installation ==

**Stronger GitHub Widget** is very easy to install (instructions) :  
* Upload the `/stronger-github-widget` folder to your `/wp-content/plugins/` directory.  
* Activate the plugin through the Plugins menu in WordPress®.  


== Frequently asked questions ==

= It does not work or I have a odd error message when applying settings =

Each time you put a widget, it makes 2 calls to the Github API every hour.
Each time you save a widget, it makes 2 calls to the Github API.
Your are limited to 60 requests per hour. So if you have more than 30 distinct widgets (ouch!), your IP will be blocked until the next hour.

2 solutions :

* Remove widgets
* Edit plugin file `inc/define.php` and change the API url  
from `define( 'PSK_SGW_GITHUB_API_URL' , 'https://api.github.com/users/' );`  
to `define( 'PSK_SGW_GITHUB_API_URL' , 'https://MY_GITHUB_LOGIN:MY_GITHUB_PASSWD@api.github.com/users/' );`

Then you will be limited to 5000 requests per hour. It should be enough!


== Screenshots ==

1. Widget Settings
2. Widget in action for type Events
3. Widget in action for type Repositories
4. Widget in action for type Starred
5. Widget in action for type Followers
6. Widget in action for type Following
7. Widget in action for type Subscriptions

== Changelog ==

= 0.1 =
* First release

== Upgrade Notice ==

