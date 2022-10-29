<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class TweetsController extends AbstractController
{
    private $users;
    private $tweetsDir;

    public function __construct($users, $tweetsDir)
    {
        $this->users = $users;
        $this->tweetsDir = $tweetsDir;
    }

    #[Route('/tweets', name: 'app_tweets_index')]
    public function index(): Response
    {
        return $this->render('tweets/index.html.twig', [
            'controller_name' => 'TweetsController',
            'users' => $this->users
        ]);
    }

    #[Route('/tweets/{user}', name: 'app_tweets_recent_for_user')]
    public function recent($user): BinaryFileResponse
    {
        if (!in_array($user, $this->users)) {
            throw $this->createNotFoundException('User not found');
        }

        $file = new File('../' . $this->tweetsDir . $user . '.json' );

        return $this->file($file, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
