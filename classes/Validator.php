<?php
namespace classes;

class Validator
{
    public function __construct()
    {
    }

    public function validate_firstname($field)
    {
        if ($field == "") {
            return "No Firstname was entered<br />";
        }
        return "";
    }


    public function validate_surname($field)
    {
        if ($field == "") {
            return "No Surname was entered<br />";
        }
        return "";
    }


    public function validate_username($field)
    {
        if ($field == "") {
            return 'No username entered<br />';
        }
        /*if (strlen($field) < 5)
                {
                        return 'Username must be at least 5 characters<br />';
                }
        if (preg_match("/[^a-zA-Z0-9_-]/", $field))
                {
                        return 'Only letters, numbers, -, and _ allowed for usernames<br />';
                }
        return "";	*/
    }

    public function validate_password($field)
    {
        if ($field == "") {
            return 'No password was entered<br />';
        } else if (strlen($field) < 6) {
            return 'Password must be at least 6 characters<br />';
        }
        //WE CAN MAKE THE PW EVEN STRONGER AND ACCEPT IT ONLY IF IT CONTAINS AT LEAST ONE SMALL LETTER, ONE UPPERCASE LETTER, AND ONE NUMBER, BUT IN THIS CASE WE WILL NOT BECAUSE OUR PHP CLASS CODE WILL STILL CARRY OUT A THIRD STAGE VALIDATION SPECIFICALLY FOR THE PW. HENCE WE COMMENT OUT THE FOLLOWING 4 LINES
        //else if (!preg_match("/[a-z]/", $field) ||
        //		 !preg_match("/[A-Z]/", $field) ||
        //		 !preg_match("/[0-9]/", $field))
        //	return "Passwords require 1 each of a-z, A-Z and 0-9<br />";
        return "";
    }


    public function validate_age($field)
    {
        if ($field == "") return "No Age was entered<br />";
        else if ($field < 18 || $field > 110)
            return "Age must be between 18 and 110<br />";
        return "";
    }


    public function validate_phonenumber($field)
    {
        if ($field == "") {
            return "We need to have your phone number<br />";
        } else if (!is_numeric($field)) {
            return "Please enter a correct phone number<br />";
        }
        if (preg_match("/[^0-9- ]/", $field)) {
            return "Only numbers, - and spaces allowed for phone numbers<br />";
        }
        return "";
    }


    public function validate_email($field)
    {
        if ($field == "") {
            return "No Email was entered<br />";
        } elseif (!((strpos($field, ".") > 0) &&
                (strpos($field, "@") > 0)) ||
            preg_match("/[^a-zA-Z0-9.@_-]/", $field)) {
            return "The Email address is invalid<br />";
        } else {
            @list($nl_userName, $nl_mailDomain) = explode("@", $field);

            if ($nl_mailDomain) {
                /*if (!checkdnsrr($nl_mailDomain, "MX"))
                {
                    // this email domain is not valid
                    return 'Invalid email domain<br />';
                }
                 *
                 */
            }

            // Next we'll make sure the email address appears to be valid (i.e. it has an @ symbol, no invalid characters etc
            if (!preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $field)) {
                // This email address doesn't seem valid
                return 'Your email address doesn\'t appear to be valid - please check and try again<br />';
            }
        }
        return "";
    }


    public function fix_string($string)
    {
        $string = stripslashes($string);
        trim($string);

        return htmlentities($string);
    }
}
?>




