# Sudbury Wordpress #
This is the Codebase for the Sudbury Wordpress Website

## What is this repository for? ##

This repository tracks the code that we have added / changed (plugins and themes only) to make the Wordpress Install on the Sudbury Website

## Documentation ##

**Documentation** will be written and placed in the Website Documentation folder on my desk and in confluence: https://confluence.sudbury.ma.us/

**API Documentation** is provided in the source code using the PHPDoc Standard.  I have setup a PHPDoc Site at [https://svrinweb/webdocs/](https://svrinweb/webdocs/)

**Snippets** are provided in the [wiki](https://bitbucket.org/sudbury/sudburywp/wiki/Home) where I have been dumping them from time to time.

**WP Core Documentation** can be found on the [WordPress Codex](http://codex.wordpress.org/).  This is where all the WordPress Core documentation lives.  If you need to look up the usage of `get_posts` or something like that then go to the codex, it is an excellent resource!

## Updating WordPress Core ##

To Update WordPress Core, Install Plugins, and otherwise let the web server perform actions that install code, you need to do the following

* Login to the server(s)
* Change (cd) to the `/sudbury.ma.us` symlink
* Run `rake permissions:unlock`, This will unlock all the plugin and core wordpress folders and make them writable
* Perform your updates, install your plugins, ect.
* Run `rake permissions:lock` to lock down the system again

This documentation is also included in Confluence

## Editing Code ##

For now I recommend using a real text editor or PHP IDE such as PHPStorm (what I use), Sublime Text (What I also use), Notepad++, or Atom.  DO NOT USE NOTEPAD! You will end up making a mistake and not even noticing it and you also won't get any of the highlighting, code completion, and code inspections.

Before Publishing to the production server all changes must be committed / merged to the master branch.  When you are confident that the changes are ready for production create a pull request to go from master -> production.  The pull request must be reviewed and approved by either Eddie Hurtig, Aaron Holbrook, or whoever the Town Wordpress Developer is at the time before the pull request gets merged.  Once you click the merge button on the pull request an automated deploy process executes and deploys the changes to the production server

## How do I get set up? ##

* Clone this repo to your Wordpress install path (E:\wwwroot\sudbury.ma.us\) [Must be empty directory] using `git clone https://bitbucket.org/sudbury/sudburywp .` 
* Download Wordpress (https://wordpress.org/latest.zip) and extract it to the same folder... if there are conflicts try to merge them but there shouldn't be any overwrites, just folder merges
* Install MySQL Community Edition (http://mysql.com).  Use the Windows Installer and select a `Server Only` Install
* Download and extract phpmyadmin to the Wordpress install path
* Go to http://sudbury.ma.us/phpmyadmin and login with the MySQL root account (modify C:\windows\system32\drivers\etc\hosts if necessary but for later on you need to be using the domain sudbury.ma.us even if this is a beta server)
* Create a MySQL database and a user with access to that database
* Go to http://sudbury.ma.us/ and do the Wordpress install


## Contribution guidelines ##

* All Code gets reviewed before being pushed to the production branch
* NEVER modify Wordpress Core!!! That is: Never modify anything in the `wp-includes` or `wp-admin` directories of the site.  If you Modify a Plugin then prepend `(Sudbury Version)` to the beginning of the `Plugin Name: ...` comment and `git add -f wp-content/plugins/the-plugin` so that it is obvious that it has been modified and it's patch it tracked in git
* Follow the [WordPress coding standards](https://codex.wordpress.org/WordPress_Coding_Standards).  There are a few exceptions that we are still struggling to settle on... tabs/spaces is one of them. 

## Who do I talk to? ##

Eddie Hurtig - Lead Developer

* Primary Email: hurtige@sudbury.ma.us
    * Alt Email 1: eddie@hurtigtechnologies.com
    * Alt Email 2: eddie@hurtigs.org
    * Alt Email 3: hurtige@ccs.neu.edu
    * Alt Email 4: hurtig.e@husky.ccs.neu.edu
* Phone: (978) 505 - 5610
* If none of that works you can try searching my site [hurtigtechnologies.com](https://hurtigtechnologies.com/) for a service called EPing, Ping or Emergency Contact.  I have an Idea for a service along those lines that I might launch sometime in the future... anyways if that app exists and it's an emergency then use it!