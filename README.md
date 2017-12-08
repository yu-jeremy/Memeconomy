![alt text](https://github.com/yu-jeremy/Memeconomy/blob/master/assets/logo.png)

# Memeconomy 

A creative project completed along with a partner, shoutout to Zhi Shen Yong!

### Project Description
This web app idea is related to recent trends in content creation but also looks to the future by expanding the definition of licensed content. Our website maintains this general theme by delivering **memes** as if they are a truly legitimate 
form of licensed content (the legitimacy of memes as art/licensed content today is up for debate). 

Some websites that inspired us: DeviantArt, ebay

This website was built within the Cloud9 development environment, using the AMP stack (Apache, MySQL, PHP) and the Bootstrap v4.0 front-end HTML/CSS/JS framework. The majority of the project files are PHP scripts, used to query MySQL tables that were set up in Cloud9, using the Terminal. Note that accessing MySQL on C9 is a bit different from accessing MySQL through your own computer's command line tool. 

### General User Experience Cycle 

Bootstrap is an easy-to-use HTML/CSS/JavaScript front-end framework that can streamline and organize your web app quickly and cleanly through a useful grid system. The entire framework, in its current state (latest version 4, still in beta), is built to be responsive. 

For our website, a user starts off by registering with their first name, last name, username, and password. Logging in with the latter two fields welcomes the user into the site. 

### Project Walkthrough

Below is the project walkthrough, which includes database setup and features of the website.

1. MySQL Database Details

Our database setup consists of a table for each of the following: users, memes, meme comments, votes, events, event submissions, and mail. The users table is rather simple. Every user registers and logs in with the usual information, a first name, last name, and username. We store a default profile picture for that user using the file system in C9, but track that file by storing the file path in our database. Additionally, we log when users register with a "date joined" field that defaults to the current timestamp. 

Next, we move to the memes table (seen below), which helps store the bulk of our content (e.g. the content our website revolved around). 


| Field       | Type             | Null | Key | Default           | Extra          |
|-------------|------------------|------|-----|-------------------|----------------|
| id          | int(10) unsigned | NO   | PRI | NULL              | auto_increment |
| title       | varchar(30)      | NO   |     | NULL              |                |
| description | text             | NO   |     | NULL              |                |
| authorid    | int(10) unsigned | NO   | MUL | NULL              |                |
| licensedto  | text             | YES  |     | NULL              |                |
| datemade    | timestamp        | NO   |     | CURRENT_TIMESTAMP |                |
| price       | int(10) unsigned | NO   |     | NULL              |                |
| filepath    | varchar(100)     | NO   |     | NULL              |                |
| upvotes     | int(10) unsigned | YES  |     | 0                 |                |
| downvotes   | int(10) unsigned | YES  |     | 0                 |                |
| forsale     | tinyint(1)       | NO   |     | NULL              |                |
| keywords    | varchar(255)     | YES  |     | NULL              |                |






