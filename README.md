# reddit-migrate
Migrate subscriptions from one Reddit account to an other.

1. Export the old account multireddit URL, from there https://www.reddit.com/subreddits/mine
   Paste it in a file, subreddits.txt for example, and save

2. Create a MySQL database with this table :
DROP TABLE IF EXISTS `subreddit`;
CREATE TABLE IF NOT EXISTS `subreddit` (
  `sr_rawtitle` text NOT NULL,
  `sr_title` text NOT NULL,
  `sr_nsfw` tinyint(4) DEFAULT 0,
  `sr_url` varchar(64) NOT NULL,
  `sr_g_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`sr_url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

3. Configure and use multireddit_db_import.php to fill the table with all informations relative to the listed subreddits from your multireddit URL

4. Configure and use subscribe.php to launch a batch, that will use the Reddit API to subscribe to subreddits

5. Profit
