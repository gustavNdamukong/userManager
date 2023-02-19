<?php
namespace config;


if(file_exists("../autoloader.php")) {
    include_once "../autoloader.php";
}
else if (file_exists("autoloader.php"))
{
    include_once "autoloader.php";
}

class Config
{
    public function getConfig()
    {
        return [

            /**
            |--------------------------------------------------------------------------
            | DB connection credentials
            |--------------------------------------------------------------------------
             */

            'localDBcredentials' => [
                'username' => 'partyman',
                'pwd' => 'party123',
                'db' => 'user_manager', # MUST be same as what is specified in the Dockerfile db service
                'host' => 'localhost',
                'key' => 'takeThisWith@PinchOfSalt'
            ],

            /**
            |--------------------------------------------------------------------------
            | Allow members of the public to register
            |--------------------------------------------------------------------------
             */

            'allow_registration' => true,


            /**
            |--------------------------------------------------------------------------
            | Email config data
            |--------------------------------------------------------------------------
             */

            'appName' => 'UserManager', //your app name here

            'appEmail' => 'yourEmail@yourDomain.com',

            'headerFrom' => 'yourEmail@yourDomain.com',

            'headerReply-To' => 'yourEmail@yourDomain.com',

            'appURL' => 'https://yourWebsite@yourDomain.com'
        ];
    }
}