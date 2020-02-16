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
        $project = $pivotal_tracker_client->getProject($project_id, ['fields' => 'name']);
        $iteration = $pivotal_tracker_client->getIteration($iteration_id, ['fields' => 'number,team_strength,accepted_points,effective_points,velocity,start,finish,stories(id,name,story_type,estimate,current_state,url,owner_ids),points,accepted,created,analytics']);

        $memberships = $pivotal_tracker_client->getMemberships();
        $people = array();
        foreach ($memberships as $membership) {
            $people[$membership->person->id] = $membership->person;
        }

        $results_per_person = array();
        foreach ($iteration->stories as $story) {
            if ($story->story_type == 'release') {
                continue;
            }

            foreach ($story->owner_ids as $owner_id) {
                if (!isset($results_per_person[$owner_id])) {
                    $result_per_person = new \stdClass();
                    $result_per_person->name = $people[$owner_id]->name;
                    $result_per_person->bugs = 0;
                    $result_per_person->features = 0;
                    $result_per_person->chores = 0;
                    $result_per_person->points = 0;
                    $results_per_person[$owner_id] = $result_per_person;
                }

                $result_per_person = $results_per_person[$owner_id];
                switch ($story->story_type) {
                    case 'bug':
                        $result_per_person->bugs += 1;
                        break;
                    case 'chore':
                        $result_per_person->chores += 1;
                        break;
                    case 'feature':
                        $result_per_person->features += 1;
                        break;
                }

                if ($story->current_state == 'accepted') {
                    $result_per_person->points += $story->estimate;
                }
            }
        }

        usort($results_per_person, function($person_1, $person_2) {
            return strcasecmp($person_1->name, $person_2->name);
        });

        return $this->render('iteration/detail.html.twig', [
            'project' => $project,
            'project_id' => $project_id,
            'iteration_id' => $iteration_id,
            'results_per_person' => $results_per_person,
            'iteration' => $iteration
        ]);
    }
}