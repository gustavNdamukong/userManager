<?php

namespace classes;

use config\Config;

if(file_exists("../autoloader.php")) {
    include_once "../autoloader.php";
}
else if (file_exists("autoloader.php"))
{
    include_once "autoloader.php";
}

class Messenger
{
    protected $_config;

    protected $_appEmail;

    protected $_headerFrom;

    protected $_headerReplyTo;

    protected $_appURL;

    protected $_appName;



    public function __construct()
    {
        $this->_config = new Config();

        $this->_appEmail = $this->_config->getConfig()['appEmail'];

        $this->_headerFrom = $this->_config->getConfig()['headerFrom'];

        $this->_headerReplyTo = $this->_config->getConfig()['headerReply-To'];

        $this->_appURL = $this->_config->getConfig()['appURL'];

        $this->_appName = $this->_config->getConfig()['appName'];
    }



    public function sendContactFormMsgToAdmin($name, $visitorEmail, $phone, $message)
    {
        // Add your "sending" email below, better to get this from the config file
        $headers  = "From: $this->_headerFrom\r\n";
        $headers .= "Reply-To: $visitorEmail\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";

        $to = $this->_appEmail;
        $subject = "Inquiry from your website contact form";

        $msg = $this->sendContactFormMsgToAdminTemplate($name, $visitorEmail, $phone, $message);

        // And send the email!
        $send = mail($to, $subject, $msg, $headers);
        if ($send)
        {
            return true;
        }
        else
        {
            return false;
        }
    }




    public function sendEmailActivationEmail($name, $email, $subject, $message)
    {
        //prepare to send an email to the new user with a link to activate their account
        //Time to send a welcome email to the new member with an account activation code
        $to = "$email";

        // Add your "sending" email below, notice we are getting this from the settings file
        $headers  = "From: $this->_headerFrom\r\n";
        $headers .= "Reply-To: $this->_headerReplyTo\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";

        $msg = $this->createNewMemberTemplate($name, $message);

        // And send the email!
        $send = mail($to, $subject, $msg, $headers);
        if ($send)
        {
            return true;
        }
        else
        {
            return false;
        }

    }





    public function sendWelcomeEmail($name, $email, $subject, $message)
    {
        //prepare to send an email to the new user with a link to activate their account
        //Time to send a welcome email to the new member with an account activation code
        $to = "$email";

        // Add your "sending" email below, notice we are getting this from the settings file
        $headers  = "From: $this->_headerFrom\r\n";
        $headers .= "Reply-To: $this->_headerReplyTo\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";

        $msg = $this->createNewMemberTemplate($name, $message);

        // And send the email!
        $send = mail($to, $subject, $msg, $headers);
        if ($send)
        {
            return true;
        }
        else
        {
            return false;
        }
    }




    public function sendPasswordResetEmail($email, $firstname, $resetCode)
    {
        $subject = "Reset your password at ".$this->_appName;

        $to = "$email";

        // Add your "sending" email below, notice we are getting this from the settings file
        $headers  = "From: $this->_headerFrom\r\n";
        $headers .= "Reply-To: $this->_headerReplyTo\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";

        $msg = $this->passwordResetTemplate($firstname, $resetCode);

        // And send the email!
        $send = mail($to, $subject, $msg, $headers);

        if ($send)
        {
            return true;
        }
        else
        {
            return false;
        }
    }





    ############################################ EMAIL TEMPLATES #######################################################

    private function sendContactFormMsgToAdminTemplate($name, $email, $phone, $message)
    {
        //Determine if we are live or not in order to build any links with the right URLs
        if ($this->_config->getConfig()['live'])
        {
            $url = $this->_config->getConfig()()['fileRootPathLive'];
        }
        else
        {
            $url = $this->_config->getConfig()['fileRootPathLocal'];
        }


        ######<!-- ==========================
        #####  FIRST SIMPLE EMAIL TEMPLATE WITH ONE IMAGE
        #######=========================== -->

        $msg = "
            <!DOCTYPE HTML>
		    <html class=\"no-js\" lang=\"en-gb\">
		    <head>
                    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                    <style type='text/css'>
                        #heading {
                                 background-color: #b3d4fc; /*This makes that blue bg color*/
               font-family: Verdana, Geneva, sans-serif;
                font-size: 12px;
                text-align: center;
             }
           #imageBox {
                    float: right;
                    }
                    
           </style>
        
        
           <!--[if lt IE 7]>
          <style type='text/css'>
          #wrapper { height:100%; }
          </style>
          <![endif]-->
        
          <!--[if lt IE 8]>
          <link rel='stylesheet' href='css/ie.css'>
          <![endif]-->
        
        </head>
        <body>
             <div id='maincontent' class='column'> 
                  <br />
                  <h1 id='heading'>Message from website <?=$this->_appUrl?> form</h1>
                  <h3>From $name,</h3>
                  <h3>Their email address: $email,</h3>
                  <h3>Phone number: $phone,</h3>
                  <h3>Description of the job:</h3>
                  <p>$message</p>
                  <br />";

        $msg .= "              
            </div>
        </body>
        </html>";

        return $msg;

        ######<!-- ==========================
        #####  FIRST SIMPLE EMAIL TEMPLATE WITH ONE IMAGE - END
        #######=========================== -->
    }






    /**
     * @param $username
     * @param $password
     * @param $firstname
     * @return string
     */
    private function passwordResetTemplate($firstname, $resetCode)
    {
        $name = ucfirst($firstname);

        $msg = "
            <!DOCTYPE HTML>
		    <html class=\"no - js\" lang=\"en - gb\">
		    <head>
                    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                    <style type='text/css'>
                        #heading {
                                 background-color: #b3d4fc; /*This makes that blue bg color*/
               font-family: Verdana, Geneva, sans-serif;
                font-size: 12px;
                text-align: center;
             }
           #imageBox {
                    float: right;
                    }
                    
           </style>
        
        
           <!--[if lt IE 7]>
          <style type='text/css'>
          #wrapper { height:100%; }
          </style>
          <![endif]-->
        
          <!--[if lt IE 8]>
          <link rel='stylesheet' href='css/ie.css'>
          <![endif]-->
        
        </head>
        <body>
             <div id='maincontent' class='column'> 
                  <br />
                  <h1 id='heading'>Message from $this->_appName</h1>
                    <h1>Dear $name<br /></h1> 
                    <h2>You requested to reset your log in details for $this->_appName</h2>
                    <p>Please click on the following link to reset your password.</p>
                    <br />
                    
                    <p><a href='".$this->_appURL."admin/verifyEmail?em=$resetCode'>Click here to reset your password</a> or copy and paste the 
                    following link in your browser:</p>
                    <p>".$this->_appURL."admin/verifyEmail?em=$resetCode</p>
                    <br />
                    <h3>$this->_appName</h3>
                    <br />
                  <br />";

        $msg .= "              
            </div>
        </body>
        </html>";

        return $msg;
    }







    private function createNewMemberTemplate($name, $message)
    {
        $name = ucfirst($name);
        $msg = "
            <!DOCTYPE HTML>
		    <html class=\"no - js\" lang=\"en - gb\">
		    <head>
                    <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
                    <style type='text/css'>
                        #heading {
                                 background-color: #b3d4fc; /*This makes that blue bg color*/
               font-family: Verdana, Geneva, sans-serif;
                font-size: 12px;
                text-align: center;
             }
           #imageBox {
                    float: right;
                    }
                    
           </style>
        
        
           <!--[if lt IE 7]>
          <style type='text/css'>
          #wrapper { height:100%; }
          </style>
          <![endif]-->
        
          <!--[if lt IE 8]>
          <link rel='stylesheet' href='css/ie.css'>
          <![endif]-->
        
        </head>
        <body>
             <div id='maincontent' class='column'> 
                  <br />
                  <h1 id='heading'>Welcome to Camerooncom</h1>
                  <h3>Dear $name,</h3>
                  <p>$message</p>
                  <br />";

        $msg .= "              
            </div>
        </body>
        </html>";

        return $msg;
    }
}