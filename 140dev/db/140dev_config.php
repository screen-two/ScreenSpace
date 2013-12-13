<?php
/**
* 140dev_config.php
* Constants for the entire 140dev Twitter framework
* You MUST modify these to match your server setup when installing the framework
* 
* Latest copy of this code: http://140dev.com/free-twitter-api-source-code-library/
* @author Adam Green <140dev@gmail.com>
* @license GNU Public License
* @version BETA 0.20
*/

// Directory for db_config.php
define('DB_CONFIG_DIR', '/var/www/html/140dev/db/');

// Server path for scripts within the framework to reference each other
define('CODE_DIR', '/var/www/html/140dev/');

// External URL for Javascript code in browsers to call the framework with Ajax
define('AJAX_URL', 'http://yoursite.com/140dev/');

// OAuth settings for connecting to the Twitter streaming API
// Fill in the values for a valid Twitter app
define('TWITTER_CONSUMER_KEY','******');
define('TWITTER_CONSUMER_SECRET','******');
define('OAUTH_TOKEN','******');
define('OAUTH_SECRET','******');

// MySQL time zone setting to normalize dates
define('TIME_ZONE','America/New_York');

// Settings for monitor_tweets.php
define('TWEET_ERROR_INTERVAL',10);
// Fill in the email address for error messages
define('TWEET_ERROR_ADDRESS','*****');
?>