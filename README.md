# Memeconomy
A creative project completed along with a partner, shoutout to Zhi Shen Yong!

### Project Description
This web app idea is related to recent trends in content creation but also looks to the future by expanding the definition of licensed content. Our website maintains this general theme by delivering **memes** as if they are a truly legitimate 
form of licensed content (the legitimacy of memes as art/licensed content today is up for debate). 

Some websites that inspired us: DeviantArt, ebay

This website was built within the Cloud9 development environment, using the AMP stack (Apache, MySQL, PHP) and the Bootstrap v4.0 front-end HTML/CSS/JS framework. The majority of the project files are PHP scripts, used to query MySQL tables that were set up in Cloud9, using the Terminal. Note that accessing MySQL on C9 is a bit different from accessing MySQL through your own computer's command line tool. 

### Project Walkthrough

Below is the project walkthrough, which includes database setup and features of the website.

1. MySQL Database Details

Our database setup consists of a table for each of the following: users, memes, meme comments, votes, events, event submissions, and mail. The users table, outlined below, is rather simple. 

+-------------+---------------------+------+-----+-------------------+----------------+
| Field       | Type                | Null | Key | Default           | Extra          |
+-------------+---------------------+------+-----+-------------------+----------------+
| id          | int(10) unsigned    | NO   | PRI | NULL              | auto_increment |
| first_name  | varchar(40)         | NO   |     | NULL              |                |
| last_name   | varchar(40)         | NO   |     | NULL              |                |
| username    | varchar(40)         | NO   |     | NULL              |                |
| date_joined | timestamp           | NO   |     | CURRENT_TIMESTAMP |                |
| password    | varchar(255)        | NO   |     | NULL              |                |
| credits     | bigint(20) unsigned | NO   |     | NULL              |                |
| propic      | varchar(100)        | NO   |     | NULL              |                |
| upvotes     | int(10) unsigned    | YES  |     | 0                 |                |
| downvotes   | int(10) unsigned    | YES  |     | 0                 |                |
| comments    | int(10) unsigned    | YES  |     | 0                 |                |
+-------------+---------------------+------+-----+-------------------+----------------+ 





