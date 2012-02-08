Installation
============

Note: You will want to put the google analytics

Unix Based
----------
Navigate into the directory of your choice and use the git clone command with the read only http address.

I put mine in a utilities directory so I use the following commands in sequence.

`cd /var/www/utilities/`

`git clone git@github.com:kcalmes/Broken-Link-Finder.git`

`mv Broken-Link-Finder/ broken_link_finder/`

Note: The biggest benefit of installing it with git is that you can execute `git pull` to update the script from the repo.

Windows Based
-------------
Download the zip of the content from [the project home page](https://github.com/kcalmes/Broken-Link-Finder).

Unzip and place in the directory of your choice.


Usage
=====
Unix Based
----------
Open the cron scheduler and add a job to run the script

`sudo crontab -e`

Then insert a command with the following syntax

'minute hour day_of_month month day_of_week	php /path/where/dir/was/placed/crawl.php http://homepage.com email@domain.com [another@email [...]]'

For more clarification on cron job options [click here](http://ss64.com/osx/crontab.html).

To run it nightly during the work week so that errors are reported Monday - Friday Morning add the following command

`0	22	*	*	0-5	php /var/www/utilities/broken_link_finder/crawl.php http://ccc.byu.edu email@domain.com`

Windows Based
-------------
Use the task scheduler to execute the crawler.  No other support is available for windows.

Security Concerns
=================
No known security concerns.  When HTML is loaded it is parsed for hyperlinks but it does not execute any code.

Known Issues
============
There is a small error on the side of reporting some links broken that are not, but will not miss broken links.  One reason for this is server load at the precise moment the link is checked.  Another instances is when there are meta redirects in the html.  The physical link on the website itself will work because the page is being redirected but this script will not cover that case either.  If any of these links get too annoying there is a file called exclude.txt in the directory in which you can place urls to be skipped in the crawling.

To Do
=====
*	Revise crawl algorithm for efficiency (that being said, it works fine).
*	Set all broken links to be tried again after a time delay to minimize the erroneous links.