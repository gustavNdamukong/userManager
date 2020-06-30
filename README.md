
## USERS

    -The system has two types of users
        'admin' user and a

        'member' user

    -An admin user can view other users, and perform CRUD operations on their details (create them, view them, modify them, and delete tem)

    -A member user can only login and view users but cannot perform any CRUD operations.

    -Currently, there are two users already registered in the system, and they are both admins. Log in as either of the users and change the
        type of the other user to 'member' then log out and log back in as them to see the limitation in their privileges.
        Here are their log in details (passwords are encrypted).

        admin - username: admin123 password: 06111007

        admin - username: fritz password: 06111007


## DATABASE SETUP

    -DB type used is a MySQL DB
    -Create a database called 'user_manager'
    -The sql file to populate the DB is found in the userManager.sql file
    -The user credentials to use in setting up the DB are found in includes/settings.php