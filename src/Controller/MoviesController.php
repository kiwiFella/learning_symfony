<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    
    #[Route('/movies', name: 'movies')]
    public function index(): Response
    {
        $repository = $this->em->getRepository(Movie::class);
       
        // $movies = $repository->findAll();   // equivalent to: SELECT * from Movies
        // $movies = $repository->find(8);     // equivalent to: SELECT * from Movies WHERE id == 8
        // $movies = $repository->findBy([], ['id' => 'DESC']);    // equivalent to: SELECT * from Movies ORDER BY id DESC
        // $movies = $repository->findOneBy(['id' => 7, 'title' => 'The Dark Knight'], ['id' => 'DESC']);    // equivalent to: SELECT * from Movies WHERE id == 7 AND title == 'The Dark Knight' ORDER BY id DESC
        // $movies = $repository->count([]);
        // $movies = $repository->getClassName([]);
        $movies = $repository->findAll();


        // dd($movies); //die dump
        return $this->render('movies/index.html.twig',[
            'movies' => $movies,
        ]);
    }
}
