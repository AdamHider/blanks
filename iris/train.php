<?php

include __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Loggers\Screen;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Extractors\NDJSON;
use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\CrossValidation\Metrics\ProbabilisticAccuracy;
use Rubix\ML\CrossValidation\HoldOut;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\Classifiers\ClassificationTree;

$logger = new Screen();

$logger->info('Loading data into memory');


$training = Labeled::fromIterator(new NDJSON('dataset.ndjson'));

//[$training, $testing] = $dataset->randomize()->split(0.8);

$samples = [
    [0.99,0.33,0.66,4,0.25,0.75,0.5,1]
];

$labels = ['[2]'];

$testing = new Labeled($samples, $labels);

$estimator = new KNearestNeighbors(9);
//$estimator = new RandomForest(new ClassificationTree(10), 300, 0.1, true);

$logger->info('Training');

$estimator->train($training);

$logger->info('Making predictions');


//$predictions = $estimator->predict($dataset);


$predictions = $estimator->predict($testing);

$probabilities = $estimator->proba($testing);
print_r($predictions);
die;

$metric = new ProbabilisticAccuracy;

$score = $metric->score($probabilities, $testing->labels());

$logger->info("Accuracy is $score");

