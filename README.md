<p align="center"><img src="http://zuzootech.com/logo.png"></p>

<p align="center">
<a href="https://packagist.org/packages/hirenmangukiya/autocrud"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

#### Laravel AutoCRUD, Make a Web Application Just In Minutes, With having a basic knowledge of Laravel.

## INTRODUCTION
AutoCRUD is laravel libraby which helps you to create CRUD operation using your **Database**. It also gives you an options for the form design. You can create CRUD for multiple tables. Display the data from multiple table with the search and pagination optoins and you can also add dynamic fields in a table.


## REQUIREMENTS
- PHP >=5.4
- Laravel >=5.3.*


## INSTALLATION

1. Go to the root directory of your project and run below command in your console.
````
composer require hirenmangukiya/autocrud:dev-master
````

2. Add the below line in your main `composer.json` file under the `autoload -> psr4`
````
"Hiren\\Autocrud\\": "vendor/hirenmangukiya/autocrud/src"
````

3. Autoload the composer 
````
composer dump-autoload
````

4. Add the providers in your project under the `config -> app.php -> providers` 
````
Hiren\Autocrud\AutocrudServiceProvider::class,
````

5. Copy all the assets into public directory of your project
````
php artisan vendor:publish --tag=public --force
````

6. Final step, **make sure that your project has connected with the database**
````
php artisan migrate
````
Here you setup all the things and it's ready to build your webapp.


## LICENCE

This project is register under the [MIT](https://packagist.org/packages/hirenmangukiya/autocrud)
