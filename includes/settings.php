<?php
        
        

	class settings
	{

		public function getSettings()
		{
			return [

				/*
				|--------------------------------------------------------------------------
				| SET the local DB connection credentials
				|--------------------------------------------------------------------------
				|
				| It's recommended to create another (2nd) user for your DB with less privileges,
				| so that you can switch between the 2 depending on the purpose.
				|
				| For the 'host', enter your local hostname e. g. 'localhost'
				|
				| Change this to match your application DB settings
				|
				|
				*/


				'DBcredentials' => [
					'username' => 'partyman',
					'pwd' => 'party123',
					'db' => 'pass_manager',
					'host' => 'localhost',
					'connectionType' => 'mysqli',
					'key' => 'takeThisWith@PinchOfSalt'
				],




				/*
				|--------------------------------------------------------------------------
				| SITE CONTACT DETAILS AND EMAIL SETTINGS
				|--------------------------------------------------------------------------
				|
				| The contact details of this site, like emails addresses, phone numbers, fax, postal addresses etc
				|
				*/

				//'appEmail' => 'gustavfn@yahoo.co.uk',


			];
		}
	}

	######################################################################################################################################################################

  
	
	
	