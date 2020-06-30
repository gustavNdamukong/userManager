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

            //header("Location: index.php?page=adminController"); //this is where you deny unauthenticated users from accessing this
            header("Location: index.php?$_GET[page]"); //this is where you deny unauthenticated users from accessing this
        }
    }




	//a registered visitor may get to this point, so let's make sure that only authenticated users have their sessions vars modified
	if (isset($_SESSION['authenticated'])) {
        //set a time limit in seconds, so 600 is for 10 minutes
        ///////////////////////we will keep the session long for now to test
        $timelimit = 7200; // 4 hours
        // get the current time
        $now = time();

        //only time session out for logged-in users who did not choose to be remembered
        if (!isset($_COOKIE['rem_me'])) {
            //if ($now > $_SESSION['start'] + $timelimit)
            if ((isset($_SESSION['start'])) && ($now > $_SESSION['start'] + $timelimit)) {
                //1) if timelimit has expired, empty the session variable
                $_SESSION = array();

                //2) invalidate the session cookie if it's set

                if (isset($_COOKIE[session_name()])) {
                    ob_end_clean();
                    setcookie(session_name(), '', time() - 86400, '/');
                }

                //3) destroy (end) the session and redirect (if you want) with a query string
                session_destroy();

                //header("Location: {$redirect}?expired=yes"); //this is where you would redirect if nec
                //exit;
            }
            else {
                //if it's got this far, it's OK, so update start time with the same length of e session time if they are still active on e
                //page (they'll refresh it thus activating this code
                $_SESSION['start'] = time();
            }
        }
    }
