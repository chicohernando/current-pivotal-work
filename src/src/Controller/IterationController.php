<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PivotalTrackerV5\Client;

class IterationController extends AbstractController
{
    /**
     * @Route("/project/{project_id<\d+>}/iteration/{iteration_id<\d+>}", name="iteration_detail")
     */
    public function detail(int $project_id, int $iteration_id)
    {
        $pivotal_tracker_client = new Client($_ENV['PIVOTAL_TRACKER_API_KEY'], $project_id);
        $iteration = $pivotal_tracker_client->getIteration($iteration_id, ['fields' => 'number,team_strength,accepted_points,effective_points,velocity,start,finish,stories(id,name,story_type,estimate,current_state,url,owner_ids),points,accepted,created,analytics']);

        dump($iteration);
        return $this->render('iteration/detail.html.twig', [
            'iteration' => $iteration
        ]);
    }
}