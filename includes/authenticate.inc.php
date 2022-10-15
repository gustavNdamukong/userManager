<?php
ob_start();

	session_start();

    if (!(isset($_SESSION['authenticated'])))
    {
        header("Location: index.php?$_GET[page]");
    }

	if (isset($_SESSION['authenticated'])) {
        $timelimit = 7200; // 4 hours
        $now = time();

        //prolong session if user chose to be remembered
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
