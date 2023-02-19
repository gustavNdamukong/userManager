# USERS

    -The system has two types of users
        'admin' user and a

        'member' user

    -An admin user can view other users, and perform CRUD operations on their details (create, view, modify, and delete)

    -A member user can only login and view users but cannot perform any CRUD operations.

    -The database queries use mysqli's prepared statements which is secure against SQL injection.

    -Visitors can register themselves

    -Upon registration, users are sent an email link to veryfy their email. Only upon successful email verification will 
        the new user be able to login.

    -Currently, there are two users already registered in the system, and they are both admins. Log in as either of the users and change the
        type of the other user to 'member' then log out and log back in as them to see the limitation in their privileges.
        Here are their log in details (passwords are encrypted).

        admin - username: admin123 password: 1234567

        admin - username: fritz password: 1234567

    -The mail verification sent to users after signing up would look something like this 
        (will differ depending on your application directory structure):
        http://localhost/userManager/classes/adminController.php?verifyEmail=1&em=05889cc1928ac7a8acaa9f3f6bd4f2a5
        
        
# Here is what the UI looks like

![userManager - simple & secure user credentials lookup app in PHP/MySQL](https://github.com/gustavNdamukong/userManager/blob/master/userManager.png?raw=true)


# DATABASE SETUP

    -DB type used is a MySQL DB using mysqli queries
    -Create a database called 'user_manager'
    -The sql file to populate the DB is found in the userManager.sql file
    -The user credentials to use in setting up the DB are found in includes/settings.php
