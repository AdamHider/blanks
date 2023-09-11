<?php 

require_once __DIR__ . '/vendor/autoload.php';
use Phpml\Classification\KNearestNeighbors;

$sentence1 = "şimdi bunı yapam";
$sentence1 = "я сейчас делаю это";


//src_token_sum, src_pred, src_subj, tgt_token_sum, tgt_pred, tgt_subj, token_pos, tgt_token_arr
$data = [
  [3, 0.33, 0.66, 4, 0.25, 0.75, 1, '[2]'],
  [3, 0.33, 0.66, 4, 0.25, 0.75, 2, '[4]'],
  [3, 0.33, 0.66, 4, 0.25, 0.75, 3, '[1,3]']
];

$samples = [
  [3, 0.33, 0.66, 4, 0.25, 0.75, 1],
  [3, 0.33, 0.66, 4, 0.25, 0.75, 2],
  [3, 0.33, 0.66, 4, 0.25, 0.75, 3]
];
$labels = ['[2]', '[4]', '[1,3]'];

$classifier = new KNearestNeighbors();
$classifier->train($samples, $labels);

$classifier->predict([3, 0.33, 0.66, 4, 0.25, 0.75, 3]);
