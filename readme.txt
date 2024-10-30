=== BetterAntiSpamBot ===
Contributors: noneevr2
Donate link: http://noneevr2.com/
Tags: antispam, anti-spam, antispambot, bot, bots, crawler, editor, e-mail, email, encrypt, encryption, harvester, harvesting, mail, obfuscate, obfuscation, obfuscator, protect, protection, robots, shortcode spam, spambot, spammer, TinyMCE
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Trick the spambots. Real dynamic JavaScript encryption of your email addresses.

== Description ==

BetterAntiSpamBot encrypts your email addresses with a dynamically generated JavaScript function.
<i>We combine simplicity with obfuscation</i>: Simplicity for you and complexity for bots.

<h4>Simply integrated</h4>

Protect yourself in just three steps:
1. First, select the text to link with your email address.
2. Then click the "@" button in the editor's toolbar.
3. Finally, type your email address.
Alternatively, you may use the shortcode <code>[bspam email="nospam@protected.com"]Email me[/bspam]</code>.

<h4>Dynamically generated</h4>

When encrypted email addresses are present on a page, BetterAntiSpamBot automatically adds a single, uniquely generated JavaScript function to the footer.
At first, it generates a key and splits it into variables. Secondly, it randomly generates all variable-, function- and parameter names using similar UTF-8 characters. The code finally gets compressed and reduced to one line.
That's why BetterAntiSpamBot makes automated email harvesting *very* hard for spammers.

<h4>Developer friendly</h4>

BetterAntiSpamBot is a standalone php class with a WordPress framework.
In php, use the global variable <code>$betterantispambot</code> like this:
<code>[...]href="&lt;?php echo $betterantispambot->setmail('email@addr.com'); ?&gt;"[...]</code>

== Installation ==


You can download and install BetterAntiSpamBot using the built in WordPress plugin installer. If you download BetterAntiSpamBot manually, make sure it is uploaded to "/wp-content/plugins/betterantispam/".

Activate BetterAntiSpamBot in the "Plugins" admin panel using the "Activate" link.

== Frequently Asked Questions ==

= Spammers can't reverse the decryption. Really? =

No. No encryption is 100% safe, everything is crackable.
But the randomness, especially in variable names and order, makes it almost impossible to create automation for it.

= I made a script to crack your encryption! =

PM me the source. I'll fix that.

= What is compatibility mode for? =

It removes certain characters from the list, so that the obfuscated variables get accepted by very old JS engines.

= I have a suggestion! / I found a bug! / Can I help?  =

PM me about it, I greatly appreciate all constructive feedback and input.

== Screenshots ==

1. Usage in TinyMCE Editor
2. Usage in PHP code

== Changelog ==

= 1.0.1 =
* Fixed "dirty" code (thanks pragmas for the hint)
= 1.0.0 =
* Initial release

== Upgrade Notice ==

