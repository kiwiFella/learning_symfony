# CREATE DATABASE:
1. first setup .env file to match the database settings
''''
e.g. DATABASE_URL="mysql://`user`:`pass`@127.0.0.1:3306/`db_name`?serverVersion=10.11.2-MariaDB&charset=utf8mb4"
''''
2. then run `symfony console doctrine:database:create` - this will create an empty DB.

3. then run `symfony console make:entity` - it will ask for a name (eg. Movie) and then will create out `Entity` (which is like a model) - and the model name is the same as the DB table name

- it will then keep prompting us to enter table column names called `properties` and the typical DB settings like dataType (string, boolean) and var length, nullable.

- NOTE: to create DB relationships - for the `property` prompt (where it suggests 'sting') - enter `ManyToMany` (can also be ManyToOne, OneToMany, etc)

    - it will then ask for class name (which is the related Entity) - eg. 'Actor' ... click enter for defaults to create relationships.

4. Create Migration file - doctrine creates file to create/alter the database.
 - run `symfony console make:migration` - this will create a migration file in the `/migrations` folder - the php file has an `up` and `down` functions (basically create and drop functions)

5. Run the migration
- run `symfony console doctrine:migrations:migrate` 


# Add DB Data (Data Fixtures)
Data and relationships can be added to database by `fixtures` - it's kinda time consuming to setup - but would be useful if packaing dummy data to easily import into Db with a project.

1. get doctrine fixtures package: `composer require --dev doctrine/doctrine-fixture-bundle` - this creates a `DataFixtures` folder in `src` along with a dummy `AppFixtures.php` file.

2. see the `ActorFixtures.php` and `MovieFixtures.php` files to see how i set data and create the manytomany references.


#  Assets
symfony has built it'sown package for webpack - and has all kinds of symfony magic preset
1. run `composer require symfony/webpack-encore-bundle`
2. make sure you have yarn installed `yarn -v` if not then run `npm install --global yarn`
3. this will create an `assets` folder, a `webpack.config.js` and a `package.json` file...w e can see in the 'package.json' file the yarn scripts we can run to compile the frontend server... scripts like: `yarn dev` and `yarn watch`
    - i like to use `yarn watch` 
    ... you may have to run `yarn dev` once first befor running the `yarn watch` version!?

4. to manage assets easier install symfony assets - run: `composer require symfony/asset`
    - this allows us to use the `asset()` function when referencing assets paths - this is useful as with compiling the relative path to the /public/build path can be cumbersome.
    - in the `templates/base.html.twig` template we can see a new `{% block stylesheets %}` section - we can update it like the following to use the asset path:
    ````twig
        {% block stylesheets %}
            <link rel="stylesheet" href="{{ asset('build/app.css')}}">
        {% endblock %}
    ````


## FORMS:
symfony has a package for forms that allows us to use a 'form' class
1. run `composer require symfony/form`
2. we can now get symfony to generate forms for us... run something like `symfony console make:form ClassName Modelname` 
... so for my movie example it would be `symfony console make:form MovieFormType Movie`
3. this will create a new directory called 'Form' in the 'src' directory with out form.
    - this form automatically generates a form with the fields in the movie DB 
4. we generate the form in the Controller!!!!
    - this seems weird as it's frontend/view code in a controlller - but it's how symfony does it

5. very complex process... but basically follow this (put in Controller):
````php
//NOTE-1: use the autocomplete to add `use` components
// use App\Form\MovieFormType;
// use Symfony\Component\HttpFoundation\Request;
// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Component\HttpFoundation\File\Exception\FileException;

// NOTE-2:
// `$this->em` ... uses the EntityManagerInterface and is set in the __constructor

#[Route('/movies/create', name: 'create_movie')]
    public function create(Request $request): Response
    {
        $movie = new Movie;
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $newMovie = $form->getData();

            $imagePath = $form->get('imagePath')->getData();
            if($imagePath){
                $newFileName = uniqid(). '.'.$imagePath->guessExtension();

                try{
                    $imagePath->move(
                        $this-> getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                } catch (FileException $e){
                    return new Response($e->getMessage());
                }

                $newMovie-> setImagePath('/uploads/' . $newFileName);

            }

            $this->em->persist($newMovie);
            $this->em->flush();
            return $this->redirectToRoute('movies');
        }


        return $this->render('movies/create.html.twig',[
            'form' => $form->createView()
        ]);
    }
````


## CRUD
see the modelController for examples of all crud functions
- includes a complex example of file upload
- Model includes validation


## Login / Register
symfony has a package to easily create users login and register forms

1. run `composer require symfony/security-bundle`
    - this ads a new `make:user` command in the `symfony console` cli tool

2. run `symfony console make:user User` - note: 'User' on the end is the DB table
    - this will automatically create a USer Entity and User repository file as well as a security config package.

3. next we need to create a migration... run `symfony console make:migration`
    - this creates a new file in `migrations` directory.

4. run `symfony console doctrine:migrations:migrate` to run the migration.
    - this will create a `user` tablein the database

5. now we need to create the registration form to create users
    Symfony has another tool to do this - run: `symfony console make:registration-form`
    - to keep it simple set `no` to validate email

6. symfony has another tool to complete login scaffolding... 
    run: `symfony console make:auth`
    - select the login form option
    - call it something like 'LoginFormAuthenticator'
    - this will create a security template and controller as well as a config

7. we need to set the `redirect` route after login - goto `/src/Security/LoginFormAuthenticator.php` and add redirect rout to the `onAuthenticationSuccess` function

8. in templates we can prevent buttons and content from being displayed to non-logged in users -  by checking is `app.user` is true `{% if app.user %} ...`
- or terniary login/logout button example is `{{ app.user ? 'Logout' : 'Login'}}`

9. don't forget to lockdown the endpoints (ie prevent someone entering the function in the url) - in `config/packages/security.yaml` find `access control` uncomment one of the examples and do something like `- { path: ^/movies/create, roles: ROLE_USER }` - the default role for registered users is set in the `src/Entity/User.php` file

--------------

# TL;DR - Basics for a secure crud app

# Setup Symfony:
````bash
symfony new project_name --version="6.3.*" --webapp
cd project_name/
symfony server:start
````

# create database
1. setup .env file to point to mysql (user:pass & DB name)
2. `symfony console doctrine:database:create`

# create register/login for User 
1. `composer require symfony/security-bundle`
2. `symfony console make:user User`
3. `symfony console make:migration`
4. `symfony console doctrine:migrations:migrate`


# create table for contacts list 
1. `symfony console make:entity` 
2. `symfony console make:migration`
3. `symfony console doctrine:migrations:migrate`

# create controller & all crud functions & files
1. `php bin/console make:crud Contacts`

# create login form
1. `symfony console make:registration-form`
2. `symfony console make:auth`
3. set the `redirect` route in `onAuthenticationSuccess` function - on `/src/Security/LoginFormAuthenticator.php`
4. set auth & access based on `app.user.id`


# secure endpoints

# limit data to user id