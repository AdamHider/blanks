<?php
$mysqli = new mysqli("127.0.0.1:3307","root","","lugat_db");

echo "This is a translator model";

$sentence = "Şimdi bunı yapam";

$tokenList = tokenize($sentence);

$prediction = predict($tokenList);

print_r($prediction);
die;
function tokenize($sentence)
{
    $result = [];
    $sentenceSplit = sentenceSplit($sentence);
    
    foreach($sentenceSplit as $index => $token){
        $tokenPosition = tokenCalculatePosition($sentenceSplit, $index);
        $result[] = [
            'value' => $token,
            'position' => $tokenPosition
        ];
    }
    return $result;
}

function predict($tokenList)
{
    $result = [];
    $predictionList = [];
    foreach($tokenList as $token){
        $prediction = dbPredictToken($token['value'], $token['position']);
        $predictionList = array_merge($predictionList, $prediction);
    }
    $predictionNormalized = predictionNormalize($predictionList);


    return $result;
}
function predictionNormalize($predictionList)
{
    usort($predictionList, 'array_sort');
    $result = groupBy($predictionList, 'value');
    return $result;
}
function sentenceSplit($sentence)
{
    $result = explode(' ', $sentence);
    return $result;
}
function tokenCalculatePosition($sentenceSplit, $index)
{
    $sentenceLength = count($sentenceSplit);
    return round(($index+1) * 100 / $sentenceLength,2);
}
function dbPredictToken($token, $position)
{
    global $mysqli;
    $sql = "
        SELECT t1.token, t1.position
        FROM lugat_db.lgt_example_relation_test t JOIN lugat_db.lgt_example_relation_test t1 ON t.group_id = t1.group_id and t.token != t1.token 
        WHERE t.token = '$token'
        GROUP BY t1.coords ORDER BY abs(t.position - $position)
    ";
    $result = $mysqli->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}


function array_sort($a, $b)
{
    return $a['position'] <=> $b['position'];
}
function groupBy($arr, $key)
{   
    $result = [];
    $keys = [];
    foreach($arr as $a){
        if(!in_array($key, $keys)){
            $result[] = $a;
            continue;
        }
        $keys[] = $a[$key];
    }
    return $result;
}