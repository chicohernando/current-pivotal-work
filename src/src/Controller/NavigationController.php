<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use PivotalTrackerV5\Client;

class NavigationController extends AbstractController
{
    public function projectList() {
        $pivotal_tracker_client = new Client($_ENV['PIVOTAL_TRACKER_API_KEY'], '');
        $projects = $pivotal_tracker_client->getProjects();
        usort($projects, function($project_1, $project_2) {
            return strcasecmp($project_1->name, $project_2->name);
        });
        return $this->render('navigation/_projects.html.twig', array(
            'projects' => $projects,
        ));
    }
}