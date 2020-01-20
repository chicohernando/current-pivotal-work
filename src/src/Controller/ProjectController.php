<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PivotalTrackerV5\Client;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project/{project_id<\d+>}", name="project_detail")
     */
    public function detail(int $project_id)
    {
        $pivotal_tracker_client = new Client($_ENV['PIVOTAL_TRACKER_API_KEY'], $project_id);
        //TODO replace with getProject call
        // $projects = $pivotal_tracker_client->getProjects(['fields' => 'name,description,point_scale,current_velocity,current_volatility,created_at']);
        // $project = array_shift($projects);

        //get iterations and reverse them since they come in oldest to newest order
        // $iterations = $pivotal_tracker_client->getProjectIterations([
        //     'limit' => 10,
        //     'offset' => -9,
        //     'scope' => 'done_current',
        //     'fields' => 'number,team_strength,accepted_points,effective_points,velocity,start,finish'
        // ]);
        // $iterations = array_reverse($iterations);

        $project = $iterations = [];
        
        return $this->render('project/detail.html.twig', [
            'project' => $project,
            'iterations' => $iterations
        ]);
    }
}