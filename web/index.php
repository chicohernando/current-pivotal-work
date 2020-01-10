<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$key = $_ENV['PIVOTAL_TRACKER_API_KEY'];
$project_id = $_ENV['PIVOTAL_TRACKER_PROJECT_ID'];
$nutron_project_id = $_ENV['PIVOTAL_TRACKER_NUTRON_PROJECT_ID'];
$owner = $_ENV['PIVOTAL_TRACKER_OWNER'];
$team_initials = explode(',', $_ENV['PIVOTAL_TRACKER_TEAM_INITIALS']);
$nutron_team_initials = explode(',', $_ENV['PIVOTAL_TRACKER_NUTRON_TEAM_INITIALS']);

$pivotal_tracker = new \PivotalTrackerV5\Client($key, $project_id);

function populate_owner_initials($stories, $memberships) {
    foreach ($stories as $story) {
        $story->owner_initials = array();
        foreach ($story->owner_ids as $owner_id) {
            foreach ($memberships as $membership) {
                if ($membership->person->id == $owner_id) {
                    $story->owner_initials[] = strtoupper($membership->person->initials);
                    break;
                }
            }
        }
    }
}

function generate_iterations_based_on_starting_label($starting_label, $number_of_iterations) {
    $epics = array();
    $date = strtotime($starting_label);
    for ($i = 0; $i < $number_of_iterations; $i++) {
        $epics []= date('Y-m-d', $date);
        $date = strtotime('-2 weeks', $date);
    }

    return $epics;
}

$app->get('/', function(Silex\Application $app) use ($key, $project_id, $pivotal_tracker) {
    $stories = $pivotal_tracker->getStories('owner:' . $owner . ' -state:unstarted -state:accepted');
    return $app['twig']->render('index.twig', array(
        'stories' => $stories,
    ));
});

$app->get('/project/{project_id}/', function(Silex\Application $app, $project_id) use ($key) {
    $pivotal_tracker_client = new \PivotalTrackerV5\Client($key, $project_id);

    $iterations = $pivotal_tracker_client->getProjectIterations(array(
        'limit' => 10,
        'offset' => -9,
        'scope' => 'done_current',
        'fields' => 'number,team_strength,accepted_points,effective_points,velocity,start,finish'
    ));
    $iterations = array_reverse($iterations);

    return $app['twig']->render('project.twig', array(
        'project_id' => $project_id,
        'iterations' => $iterations
    ));
});

$app->get('/project/{project_id}/iteration/{iteration_id}/', function(Silex\Application $app, $project_id, $iteration_id) use ($key) {
    $pivotal_tracker_client = new \PivotalTrackerV5\Client($key, $project_id);

    $iteration = $pivotal_tracker_client->getIteration($iteration_id, array(
        'fields' => 'number,team_strength,accepted_points,effective_points,velocity,start,finish,stories(id,name,story_type,estimate,current_state,url,owner_ids),points,accepted,created,analytics'
    ));

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
                $result_per_person = new stdClass();
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
            $result_per_person->points += $story->estimate;
        }
    }

    usort($results_per_person, function($person_1, $person_2) {
        return strcasecmp($person_1->name, $person_2->name);
    });

    return $app['twig']->render('iteration.twig', array(
        'project_id' => $project_id,
        'iteration_id' => $iteration_id,
        'results_per_person' => $results_per_person,
        'iteration' => $iteration
    ));
});


$app->get('/ppp/{starting_label}', function (Request $request, Silex\Application $app, $starting_label) use ($key, $project_id, $pivotal_tracker, $team_initials) {
    $query_parameters = $request->query->all();
    $number_of_iterations = isset($query_parameters['number_of_iterations']) ? $query_parameters['number_of_iterations'] : 10;
    $owners = $team_initials;
    $iterations = array();
    $points_per_person = array();
    $sums_per_person = array();
    foreach ($owners as $owner) {
        $sums_per_person[$owner] = 0;
    }

    $iterations = generate_iterations_based_on_starting_label($starting_label, $number_of_iterations);

    foreach ($iterations as $iteration) {
        //TODO make sure to remove work specific date logic
        $iteration_start_date = date('Y-m-d', strtotime('-3 monday', strtotime($iteration)));
        $iteration_end_date = date('Y-m-d', strtotime('-1 sunday', strtotime($iteration)));

        foreach ($owners as $owner) {
            $points_per_person[$iteration][$owner] = 0;
            $stories = $pivotal_tracker->getStories('owner:' . $owner . ' state:accepted accepted_since:"' . $iteration_start_date . '" accepted_before:"' . $iteration_end_date . '"');
            foreach ($stories as $story) {
                $points_per_person[$iteration][$owner] += $story->estimate;
                $sums_per_person[$owner] += $story->estimate;
            }
        }
    }

    foreach ($owners as $owner) {
        $points_per_person['Average'][$owner] = $sums_per_person[$owner] / $number_of_iterations;
    }

    $iterations []= 'Average';

    return $app['twig']->render('points_per_person.twig', array(
        'owners' => $owners,
        'iterations' => $iterations,
        'points_per_person' => $points_per_person,
        'number_of_iterations' => $number_of_iterations
    ));
});

$app->get('/my', function (Silex\Application $app) use ($key, $project_id, $owner, $pivotal_tracker) {
    $stories = $pivotal_tracker->getStories('owner:' . $owner . ' -state:unstarted -state:accepted');
    $memberships = $pivotal_tracker->getMemberships();
    populate_owner_initials($stories, $memberships);
    return $app['twig']->render('index.twig', array(
        'stories' => $stories,
    ));
});

$app->get('/started', function (Silex\Application $app) use ($key, $project_id, $pivotal_tracker) {
    $stories = $pivotal_tracker->getStories('state:started');
    $memberships = $pivotal_tracker->getMemberships();
    populate_owner_initials($stories, $memberships);
    return $app['twig']->render('index.twig', array(
        'stories' => $stories,
    ));
});

$app->get('/nutron/ppp/{starting_date}', function (Request $request, Silex\Application $app, $starting_date) use ($key, $nutron_project_id, $nutron_team_initials) {
    $query_parameters = $request->query->all();

    $number_of_iterations = isset($query_parameters['number_of_iterations']) ? $query_parameters['number_of_iterations'] : 10;
    $points_per_person = array();
    $sums_per_person = array();
    $owners = $nutron_team_initials;

    $iterations = generate_iterations_based_on_starting_label($starting_date, $number_of_iterations);
    $pivotal_tracker = new \PivotalTrackerV5\Client($key, $nutron_project_id);

    foreach ($iterations as $iteration) {
        $start_timestamp = strtotime($iteration);
        $accepted_filter = 'accepted:' . $iteration . '..' . date('Y-m-d', strtotime('+2 saturday', $start_timestamp));
        foreach ($owners as $owner) {
            $points_per_person[$iteration][$owner] = 0;
            $stories = $pivotal_tracker->getStories('owner:' . $owner . ' ' . $accepted_filter);
            foreach ($stories as $story) {
                $points_per_person[$iteration][$owner] += $story->estimate;
                $sums_per_person[$owner] += $story->estimate;
            }
        }
    }

    foreach ($owners as $owner) {
        $points_per_person['Average'][$owner] = $sums_per_person[$owner] / $number_of_iterations;
    }

    $iterations []= 'Average';

    return $app['twig']->render('points_per_person.twig', array(
        'owners' => $owners,
        'iterations' => $iterations,
        'points_per_person' => $points_per_person,
        'number_of_iterations' => $number_of_iterations
    ));
});

$app->run();