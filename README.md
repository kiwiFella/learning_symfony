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
