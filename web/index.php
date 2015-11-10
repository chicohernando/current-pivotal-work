<?php
// web/index.php
require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->get('/key/{key}/project/{project_id}/owner/{owner}', function (Silex\Application $app, $key, $project_id, $owner) use ($app) {
    if (empty($key)) {
        $app->abort(404, 'You must supply a pivotal tracker api key');
    }
    
    if (empty($project_id)) {
        $app->abort(404, 'You must supply a project id');
    }
    
    $pivotal_tracker =  new \PivotalTrackerV5\Client($key, $project_id);
    $stories = $pivotal_tracker->getStories('owner:' . $owner . ' -state:unstarted -state:accepted');
    return $app['twig']->render('index.twig', array(
        'stories' => $stories,
    ));
});

$app->run();