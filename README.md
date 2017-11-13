The Google Tasks Bookmarklet is an easy way to add a task to your Google Tasks list
from any webpage.

Originally I created https://github.com/geoff604/google-tasks-bookmarklet
which was based on Google Apps Script, but I wanted to create a self-hosted
version of the script which was not dependent on Google Apps Script.

This repo contains the self hosted, PHP version of the bookmarklet.

Note: It is recommended to protect the php files via at least
Apache Basic Authentication since it may be a
security risk if you put the files on your site without any password.
Also, I'd recommend using a HTTPS encrypted web server for this script
as well.