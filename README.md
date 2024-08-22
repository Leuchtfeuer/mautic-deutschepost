# Print Mailing DPAG Integration by Leuchtfeuer
[![Latest Stable Version](https://poser.pugx.org/leuchtfeuer/mautic-deutschepost/v/stable)](https://packagist.org/packages/leuchtfeuer/mautic-deutschepost)
[![Build Status](https://github.com/Leuchtfeuer/mautic-deutschepost/workflows/Continous%20Integration/badge.svg)](https://github.com/Leuchtfeuer/mautic-deutschepost/actions)
[![Total Downloads](https://poser.pugx.org/leuchtfeuer/mautic-deutschepost/downloads)](https://packagist.org/packages/leuchtfeuer/mautic-deutschepost)
[![Latest Unstable Version](https://poser.pugx.org/leuchtfeuer/mautic-deutschepost/v/unstable)](https://packagist.org/packages/leuchtfeuer/mautic-deutschepost)
[![Code Climate](https://codeclimate.com/github/Leuchtfeuer/mautic-deutschepost/badges/gpa.svg)](https://codeclimate.com/github/Leuchtfeuer/mautic-deutschepost)
[![License](https://poser.pugx.org/leuchtfeuer/mautic-deutschepost/license)](https://packagist.org/packages/leuchtfeuer/mautic-deutschepost)

With our plugin, print mailings can be personalized using Deutsche Post's „Print-Mailing Automation" (a.k.a. "Print Mailing") product, to be integrated directly into your Mautic campaign - and automatically sent when it makes the most sense.


#### What is the plugin for?
Leuchtfeuer has developed the Print Mailing-Plugin in cooperation with Deutsche Post AG to enable the sending of postcards directly from the Marketing Automation Tool, personalized and at the individually perfect time.
Mautic users can use the plugin to integrate the sending of a print mailing as a new action directly into their Mautic campaign - based on the normal logic modules (e.g. "email unopened?" or "booking cancelled?"). The corresponding postcards can, of course, be completely designed and personalized for each recipient, so that he can be addressed directly or, for example, given a special discount code. You can find out more about the benefits and possible applications of the Print Mailing-Plugin here.

## Installation and Basic Configuration
The installation of the plugin requires, in addition to a Mautic account, a customer account for the Print Mailing administration website of Deutsche Post - more information can be found on the website [Print Mailing by Deutschen Post AG](https://www.deutschepost.de/de/t/printmailing.html).

### Updating from Rel. 4.x to Rel. 5.x
We had a change in namings between 4.x and 5.x. This breaks the configuration. To fix that, you need to edit the local.php file:
* Remove the `printmailing_` entries at the end of the file
* Rename all `triggerdialog_` entries to `printmailing_`


### Requirements
*   Mautic Version 3.x // 4.x

*   Command line access to the server

### Installation
*   Download plugin "mautic-deutschepost" from [Github](https://ma.leuchtfeuer.com/asset/6:as051-`printmailing`-mautic-integration) (as ZIP archive) to the Mautic-Server

*   Unpack the file, rename the directory and move it to the plugin directory of the Mautic installation
    `mv mautic-deutschepost-master <path-to-mautic>/plugins/LeuchtfeuerPrintmailingBundle`

*   Clear cache, typically:
    `sudo -u www-data php <path-to-mautic>/bin/console cache:clear`

*   adjust file permissions if needed:
    `chown -R www-data:www-data <path-to-mautic>/plugins/LeuchtfeuerPrintmailingBundle`

*   Go to "Settings" -> "Plugins" in the Mautic-Backend, klick on "Install/Update Plugins"

*   "Dt. Post" is now in the Plugin list, and is already activated


The following configuration is easy: You can make all necessary settings under _"Settings" -> "Configuration" -> "Print Mailing Settings"_.

*   MAS ID (technical name is _"partnerSystemIdExt"_) - identifies the remote system (i.e. Deutsche Post)

*   _"Mandanten-ID"_ - identifies your own System. IMPORTANT: You need to submit this ID to Deutsche Post, or else you will not have access!

*   _"Prod JWT Secret"_ - allows for Single Sign-On from Mautic into Deutsche Post

*   _User_ und password (_"Authentication Secret"_) - required for data transfer from your system to Deutsche Post


![](https://www.leuchtfeuer.com/fileadmin/knowledge/Mautic/td/TD-Mautic-Config.png)

Configuration done, and "Mandanten-ID" (see above) reported to Deutsche Post?

Now it gets exciting: In "_Channels" -> "Print Mailing"_, click on the blue button _"Print Mailing-MANAGER"_ (top right) to switch to the Deutsche Post interface, and thus verify your configuration.

If you arrive there - in the "yellow surface" - and see your name in the top right corner, everything worked!

![](https://www.leuchtfeuer.com/fileadmin/knowledge/Mautic/td/TD-Manager-Button.png)
![](https://www.leuchtfeuer.com/fileadmin/_processed_/0/7/csm_TD-SSO_5e0671e54c.png)

## Usage

### Creating a Mapping Template in Mautic
Everything else now works pretty much like for emails: Before you can integrate the first Print Mailing action into your campaign, you have to create a template.

In _"Channels" -> "Print Mailing",_ click _"+New"_ to create a new Print Mailing mapping template in Mautic.

In the tab _"Data Mapping"_, the desired data can be selected for transfer when a postcard is initiated. One field of type "_Zip code_" is mandatory; and of course that complete address data is required for successful postal delivery. Therefore, a basic set of data is already pre-assigned in every new template (which can of course be changed).

For each mapping template, you will be able to assign design etc. in the next steps, through Deutsche Post's Print Mailing Manager.

![](https://www.leuchtfeuer.com/fileadmin/knowledge/Mautic/td/TD-Template.png)

That's it with the preparations within Mautic. The visual design (e.g. using InDesign or the modern online editor) and other configuration of the postcard are not happending in Mautic, but directly in the Print Mailing Manager.

So press the blue button - see above - and you can continue in Deutsche Post's Print Mailing Manager, where you will see the mapping templates that you created, and can set up design, product properties etc for each of them.

If you are using the online editor, you can also create or edit designs directly in your browser:

![](https://www.leuchtfeuer.com/fileadmin/_processed_/3/1/csm_TD-Manager_383ad2e208.jpg)

### Integration in Mautic campaigns
The plugin comes with a new campaign action: “Send via Print Mailing”.

Again, just like with email, you can now select from your existing templates - i.e. from the Print Mailing mapping templates that you created in Mautic.

![Add campaign action](https://www.leuchtfeuer.com/fileadmin/_processed_/4/5/csm_TD-Beispielkampagne_748801a493.png)
![Configure campaign action](https://www.leuchtfeuer.com/fileadmin/knowledge/Mautic/td/TD-Aktionen.png)

Mautic then uses the plugin to send the data to Deutsche Post for printing and delivery - and shortly afterwards your contact has his individual postcard in his mailbox.

## Contributing
You can contribute by making a **pull request** to the master branch of
this repository. Or just send us some **beers**...

### Author
Leuchtfeuer Digital Marketing GmbH

mautic@Leuchtfeuer.com
