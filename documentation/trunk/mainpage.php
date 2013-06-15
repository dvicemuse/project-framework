<?php
/**
 * @mainpage I-Tul PHP Application Framework Documentation
 * 
 * @section frmwrk_introduction Introduction
 * 
 * <p>This documentation is intended to serve as a default guide for new developers using the I-Tul PHP Application framework to design websites.</p>
 * <p>The latest source can be checked out from Google Code at <a href="http://code.google.com/p/project-framework/">http://code.google.com/p/project-framework/</a></p>
 * <p>The latest demo can be viewed from the development server at <a href="http://framework.ituldev.com/demo/">http://framework.ituldev.com/demo/</a></p>
 * <p>The latest Wordpress integration demo can be viewed from the development server at <a href="http://framework.ituldev.com/wpdemo/">http://framework.ituldev.com/wpdemo/</a>. The framework is in a subdirectory called portal.</p> 
 * <p>The latest docs can be viewed from the development server at <a href="http://framework.ituldev.com/">http://framework.ituldev.com/</a></p>
 * 
 * @section frmwrk_installation Installation
 * 
 * <p>To install the framework you'll need access to a development/production server with PHP 5.3 and MySQL. The framework runs best on Linux platforms but can be used on Windows based systems.</p>
 * <p>To checkout the latest source you need to be able to use SVN or a SVN client.</p>
 * <p>You should also have shell access to the server you are working on since the command line interface is the quickest way to generate controllers and models.</p>
 * <p>Lastly, you should have access to a Control Panel of some sort such as CPanel or an equivalent to setup domains and users if needed</p>
 * 
 * @subsection frmwrk_installation_development_setup Step 1: Setting Up the Development Domain
 * 
 * <p>Use the control panel to setup a domain on the ituldev server and setup a user for the project. In CPanel/WHM this is accomplished through the Account Functions menu.</p>
 * 
 * @subsection frmwrk_installation_database_setup Step 2: Setting Up the Framework Database 
 * 
 * <p>Once you've created the domain and user for the account, access that accounts CPanel and create a database and admin user for framework to use.</p>
 *  
 * @subsection frmwrk_installation_checkout Step 3: Checking Out the Latest Version of the Framework from Source Control
 * 
 * <p>You can watch either <a href="http://cdn.podcastrocket.com/cache/4fr/FrameworkSetup.mp4">Dan's video</a> or continue reading at this point. Typically the following commands are all executed from the /home/username/www/ directory, where username is the domain user you created above and www is the public accessible web directory, unless otherwise noted below.</p>
 * <p>If you are installing just the framework then use a shell or SVN tool such as TortoiseSVN or Eclipse/Subversive to checkout the latest version of the framework from the code stored in the Google code repository. From the public_html/www folder for your domain execute the following command from the shell:</p>
 * <pre>
 * svn checkout --force https://project-framework.googlecode.com/svn/framework/trunk/ .
 * </pre>
 * <p>The period at the end is significant so please include it, basically you're forcing SVN to checkout the latest version into the current directory.</p>
 * <p>If you are going to install Worpress or some other app is in the root folder then you need to create a directory for the framework using the normal shell commands for the OS, in Linux this is mkdir. Or you can use SVN to create/checkout the code into that directory for you. Again from the public_html/www folder execute the following command from the shell:</p>
 * <pre>
 * svn checkout https://project-framework.googlecode.com/svn/framework/trunk/ YourDirectoryNameHere
 * </pre>
 * <p>From this point forward you can benefit from any updates to the core simply by doing an svn update to get the latest version.</p>
 * <p>For many projects you may want to change the above commands to "svn export" and then create/import the code into a project repository in ProjectLocker.</p>
 * 
 * @subsection frmwrk_installation_configure Step 4: Configure the settings for the Framework
 * 
 * <p>Navigating to the domain at this point will get you a nice little warning screen about checking out into Mordor. This means you haven't setup the framework completly yet. We'll do that now.</p>
 * <p>If you created a subdirectory above, navigate to that directory, otherwise stay in the www/public_html directory. Then in the shell run the following command:</p>
 * <pre>
 * ./cli.php build
 * </pre>
 * <p>or if that doesn't work...</p>
 * <pre>
 * php -f cli.php build
 * </pre>
 * <p>Follow along with the prompts and enter in the data needed for each step.</p>
 * <p>Once you've built the initial database tables and configured the framework, navigating to the domain should get you a blank homepage with a login link. If you did everthing correctly you'll be able to login as the test user.</p>
 * 
 * @subsection frmwrk_installation_build Step 5: Create tables then use the Framework Commandline Interface to generate code
 * 
 * <p>Watch <a href="http://cdn.podcastrocket.com/cache/4fr/FrameworkGenerate.mp4">Dan's video</a> for more details.</p>
 */