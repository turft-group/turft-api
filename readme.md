# \<turft-API\>

The api for the turft.nl website

#### Instructions
 
 - Clone .git directory
 - Run `composer install`
 
 - Initialize laravel project:
   - Copy `.env.example` to `.env`: `cp .env.example .env`
   - Run `php artisan key:generate`
   - Run `php artisan app:name App`
 - Run ide helper
   - Run `php artisan ide-helper:generate`
   - Run `php artisan ide-helper:meta` (for phpstorm)
 - Build the database
   - Run `php artisan migrate`  
 - Start the API
   - Run `php artisan serve --host 0.0.0.0 --port 8000`
   
 You can now go to the API through localhost:8000
 
 #### Commands /api/
 
 ### Groups '/group/'
 - GET  '/'
   - The index retrieving all groups
 - POST '/'
   - Save a group with fields:
     - Name: String(50), UNIQUE
 - GET '/group/{groupId}'
   - Get a specific group
 - PUT '/group/{groupId}'
   - Update a specific group with fields:    
     - Name: String(50), UNIQUE  
 - DELETE '/group/{groupId}'
   - Delete a specific group
 - POST '/group/{group}/addUser'
   - Add a user to a group with specified role  
     - user: Integer(11), Unsigned
     - role: Enum("owner", "admin", "member")
     
### User 
 - POST '/oauth/token'
   - Retrieve a token based on credentials:
     - username: String(50)
     - password: String(50)
 - POST '/register'
   - Register a new user
     - email: String(50)
     - name: String(50)
     - password: String(50) 
