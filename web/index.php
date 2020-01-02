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

function generate_epics_based_on_starting_label($starting_label, $number_of_iterations) {
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

$app->get('/ppp/{starting_label}', function (Request $request, Silex\Application $app, $starting_label) use ($key, $project_id, $pivotal_tracker, $team_initials) {
    $query_parameters = $request->query->all();
    $number_of_iterations = isset($query_parameters['number_of_iterations']) ? $query_parameters['number_of_iterations'] : 10;
    $owners = $team_initials;
    $epics = array();
    $points_per_person = array();
    $sums_per_person = array();
    foreach ($owners as $owner) {
        $sums_per_person[$owner] = 0;
    }

    $epics = generate_epics_based_on_starting_label($starting_label, $number_of_iterations);

    foreach ($epics as $epic) {
        foreach ($owners as $owner) {
            $points_per_person[$epic][$owner] = 0;
            $stories = $pivotal_tracker->getStories('owner:' . $owner . ' state:accepted label:' . $epic);
            foreach ($stories as $story) {
                $points_per_person[$epic][$owner] += $story->estimate;
                $sums_per_person[$owner] += $story->estimate;
            }
        }
    }

    foreach ($owners as $owner) {
        $points_per_person['Average'][$owner] = $sums_per_person[$owner] / $number_of_iterations;
    }

    $epics []= 'Average';

    return $app['twig']->render('points_per_person.twig', array(
        'owners' => $owners,
        'epics' => $epics,
        'points_per_person' => $points_per_person
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

    $epics = generate_epics_based_on_starting_label($starting_date, $number_of_iterations);
    $pivotal_tracker = new \PivotalTrackerV5\Client($key, $nutron_project_id);

    foreach ($epics as $epic) {
        $start_timestamp = strtotime($epic);
        $accepted_filter = 'accepted:' . $epic . '..' . date('Y-m-d', strtotime('+2 saturday', $start_timestamp));
        foreach ($owners as $owner) {
            $points_per_person[$epic][$owner] = 0;
            $stories = $pivotal_tracker->getStories('owner:' . $owner . ' ' . $accepted_filter);
            foreach ($stories as $story) {
                $points_per_person[$epic][$owner] += $story->estimate;
                $sums_per_person[$owner] += $story->estimate;
            }
        }
    }

    foreach ($owners as $owner) {
        $points_per_person['Average'][$owner] = $sums_per_person[$owner] / $number_of_iterations;
    }

    $epics []= 'Average';

    return $app['twig']->render('points_per_person.twig', array(
        'owners' => $owners,
        'epics' => $epics,
        'points_per_person' => $points_per_person
    ));
});

$app->run();