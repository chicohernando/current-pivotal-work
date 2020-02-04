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

        return $this->render('navigation/_projects.html.twig', [
            'projects' => $projects
        ]);
    }

    public function notificationList() {
        $pivotal_tracker_client = new Client($_ENV['PIVOTAL_TRACKER_API_KEY'], '');
        $notifications = $pivotal_tracker_client->getMyNotifications();
        
        return $this->render('navigation/_notifications.html.twig', [
            'notifications' => $notifications
        ]);
    }

    public function iterationList(int $project_id) {
        $pivotal_tracker_client = new Client($_ENV['PIVOTAL_TRACKER_API_KEY'], $project_id);
        $iterations = $pivotal_tracker_client->getProjectIterations([
            'limit' => 5,
            'offset' => -4,
            'scope' => 'done_current',
            'fields' => 'number,velocity,start,finish'
        ]);
        $iterations = array_reverse($iterations);

        return $this->render('navigation/_iterations.html.twig', [
            'project_id' => $project_id,
            'iterations' => $iterations
        ]);
    }
}