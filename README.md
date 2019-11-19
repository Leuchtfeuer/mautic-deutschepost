Deutsche Post TRIGGERDIALOG Bundle for Mautic
=============================================

[![Latest Stable Version](https://poser.pugx.org/bitmotion/mautic-deutschepost/v/stable)](https://packagist.org/packages/bitmotion/mautic-deutschepost)
[![Total Downloads](https://poser.pugx.org/bitmotion/mautic-deutschepost/downloads)](https://packagist.org/packages/bitmotion/mautic-deutschepost)
[![Latest Unstable Version](https://poser.pugx.org/bitmotion/mautic-deutschepost/v/unstable)](https://packagist.org/packages/bitmotion/mautic-deutschepost)
[![Code Climate](https://codeclimate.com/github/bitmotion/mautic-deutschepost/badges/gpa.svg)](https://codeclimate.com/github/bitmotion/mautic-deutschepost)
[![License](https://poser.pugx.org/bitmotion/mautic-deutschepost/license)](https://packagist.org/packages/bitmotion/mautic-deutschepost)

## About

Send postcards or letters with Mautic via Deutsche Post TRIGGERDIALOG.

### Requirements

The installation of the plugin requires, in addition to a Mautic 
account, a customer account for the TRIGGERDIALOG administration 
website of Deutsche Post - more information can be found on the website 
[TRIGGERDIALOG by Deutsche Post AG](https://www.deutschepost.de/de/t/triggerdialog.html).

To register, you need a valid [TRIGGERDIALOG Client ID](#before-start), 
which is provided in the Mautic settings after installing this bundle.

## Installation

To install the plugin for Mautic, the following steps are necessary 
(requires command line access):

* Download of this plugin from [GitHub](https://ma.leuchtfeuer.com/asset/6:as051-triggerdialog-mautic-integration)
(as ZIP archive) to the Mautic server.
* Unpack the file, rename the directory and move it to the plugin.
directory of the mautic installation: `mv mautic-deutschepost-master <path-to-mautic>/plugins/MauticTriggerdialogBundle`.
* Clear cache, e.g. in the Mautic backend, or also directly via command line: `rm -rf <path-to-mautic>/app/cache/prod/*`.
* Open the Mautic backend and go to "Settings" -> "Plugins", click on 
"Install/Update Plugins".

Now you are all set. "Dt. Post" appears now in the plugin list and is 
already activated.

## Configuration

You can configure the bundle within the configuration section of your
Mautic Backend. All relevant configuration can be found underneath the
tab "TRIGGERDIALOG Settings".

![Backend view of TRIGGERDIALOG settings](https://www.bitmotion.de/fileadmin/github/mautic-deutschepost/configure-bundle.png "Backend view of Deutsche Post TRIGGERDIALOG for Mautic settings.")

### Before Start

The "Marketing Autmoation System ID" and the "Client ID" cannot be
changed. The value of the system identifier must be set to "8". The 
client identifier is your specific "TRIGGERDIALOG Client ID". You need 
this ID for becoming a [Deutsche Post TRIGGERDIALOG]((https://www.deutschepost.de/de/t/triggerdialog.html)) user.

### Plugin Configuration

All other fields are self-explanatory - you will find the required 
access data on your TRIGGERDIALOG adminstration website of Deutsche Post.

## Usage

Everything else now works exactly like for e-mails: Before you can 
integrate the first TRIGGERDIALOG action into your campaign, you have to
create a template. You can find the "Deutsche Post" Plugin within the 
Channels section in the main navigation (close to "Emails" or 
"Focus Items").

From here you also have the possibility to open the TRIGGERDIALOG 
Manager.

### Creating Templates in Mautic

You can create a new TRIGGERDIALOG template in Maugic by clicking the 
"+ NEW" button.

Under the tab "Data Mapping", the desired links are made in order to 
assign the contact data in Mautic to the corresponding fields in 
TRIGGERDIALOG - ZIP-Code is a mandatory field. It goes without saying 
that complete address data is required for successful postcard dispatch.

![Backend view of creating a TRIGGERDIALOG template](https://www.bitmotion.de/fileadmin/github/mautic-deutschepost/create-triggerdialog-template.png "Backend view of creating a TRIGGERDIALOG template in Mautic.")

Please note: The optical design (InDesign, ...) and other configuration
of the postcard are not stored in Mautic, but directly on the
TRIGGERDIALOG administration website of Deutsche Post.

### Integration in Mautic Campaigns

Via the plugin, a new campaign action is now available: 
"Send via Deutsche Post". In this action you can select the desired 
postcard template from the TRIGGERDIALOG templates stored in Mautic.

![Add campaign action](https://www.bitmotion.de/fileadmin/github/mautic-deutschepost/add-campaign-action.png "Add campaign action.")

![Configure campaign action](https://www.bitmotion.de/fileadmin/github/mautic-deutschepost/configure-campaign-action.png "Configure campaign action.")

Mautic then uses the plugin to send the data to Deutsche Post for
printing and delivery - and shortly afterwards your contact has his
individual postcard in his mailbox.

## Contributing

You can contribute by making a **pull request** to the master branch of 
this repository. Or just send us some **beers**...
