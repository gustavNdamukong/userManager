<?php
ob_start();


	session_start();

    if (!(isset($_SESSION['authenticated'])))
    {
        if (!((isset($_SESSION['_Guest'])) && ($_SESSION['_Guest'] == 'visitor')))
        {
            //set a session which we will use to manage n distinguish visitors from logged in users
            //for example we will not have to redirect them again now we already know they are a visitor
            $_SESSION['_Guest'] = 'visitor';

            header("Location: index.php?$_GET[page]");
        }
    }


	if (isset($_SESSION['authenticated'])) {
        $timelimit = 7200; // 4 hours
        $now = time();

        //only time session out for logged-in users who did not choose to be remembered
        if (!isset($_COOKIE['rem_me'])) {
            if ((isset($_SESSION['start'])) && ($now > $_SESSION['start'] + $timelimit)) {
                $_SESSION = array();

                //2) invalidate the session cookie if it's set
                if (isset($_COOKIE[session_name()])) {
                    ob_end_clean();
                    setcookie(session_name(), '', time() - 86400, '/');
                }

                session_destroy();
            }
            else {
                $_SESSION['start'] = time();
            }
        }
    }
