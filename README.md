rrrrradio
===

A social jukebox built with PHP on the [Rdio](http://rdio.com) API.

#### Installation:

1. Download the code to your own PHP hosting environment.
2. Copy `configuration-dist.php` to `configuration.php`
3. Create your own [Rdio API keys](http://www.rdio.com/developers/create/) and plug the Client ID and Client Secret values into the first lines of your `configuration.php` script
4. Put your Rdio user key in the `$rdio_collection_userkey` variable. This can be found by using the [Rdio API console](http://rdioconsole.appspot.com/#vanityName%3Damsoell%26method%3DfindUser) to query your Rdio username
5. Set up your MySQL database and add the login credentials to the `$db_host`, `$db_username`, `$db_password`, and `$db_database` variables.
6. Import the database structure found in the `database/structure.sql` file
7. Run the script found in `script/searchindex.php` to populate the database with the music in your Rdio collection
8. Set up a cron job to run `script/searchindex.php` daily (to keep your collection up-to-date) and to run `script/monitor.php` every two minutes (to add newly requested tracks to the queue and add new random tracks)
