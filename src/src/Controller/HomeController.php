<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PivotalTrackerV5\Client;

class HomeController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        $pivotal_tracker_client = new Client($_ENV['PIVOTAL_TRACKER_API_KEY'], '');
        $projects = $pivotal_tracker_client->getProjects(['fields' => 'name,description,point_scale,current_velocity,current_volatility,created_at']);
        usort($projects, function($project_1, $project_2) {
            return strcasecmp($project_1->name, $project_2->name);
        });
        
        return $this->render('index.html.twig', [
            'projects' => $projects
        ]);
    }
}