
# pull from git then
# Run commands as follows

-- composer install
-- php artisan migrate 
-- php artisan db:seed --class=BooksTableSeeder 
-- php artisan queue:work 
-- php artisan storage:link 
-- php artisan serve 
# To create admin type user first register then 
1. Setup data base credential
2. login using postman 
    --post request -> "http://127.0.0.1:8000/api/login"
    --send data in form data {email:"youremail", password:"yourpassword"}

3. call api to change user type
    --post request-> "http://127.0.0.1:8000/api/users/assign-role/{userId}"
    --send data in form data {role:"admin"}

4. contact for any installation guide
    {email:avinashranjan633@gmail.com , mobile:8910804186}

