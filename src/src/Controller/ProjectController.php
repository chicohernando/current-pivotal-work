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
        $project = $pivotal_tracker_client->getProject($project_id, ['fields' => 'id,name,status,version,iteration_length,week_start_day,point_scale,point_scale_is_custom,bugs_and_chores_are_estimatable,automatic_planning,enable_tasks,start_date,time_zone,velocity_averaged_over,shown_iterations_start_time,start_time,number_of_done_iterations_to_show,has_google_domain,description,profile_content,enable_incoming_emails,initial_velocity,project_type,public,current_iteration_number,current_standard_deviation,current_velocity,current_volatility,account_id,created_at,updated_at']);
        
        //get iterations and reverse them since they come in oldest to newest order
        $iterations = $pivotal_tracker_client->getProjectIterations([
            'limit' => 20,
            'offset' => -19,
            'scope' => 'done_current',
            'fields' => 'number,team_strength,accepted_points,effective_points,velocity,start,finish'
        ]);
        $iterations = array_reverse($iterations);

        return $this->render('project/detail.html.twig', [
            'project' => $project,
            'iterations' => $iterations
        ]);
    }
}