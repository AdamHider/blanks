<?php 
$mysqli = new mysqli("127.0.0.1:3307", "root", "", "lugat_db");
$mysqli->set_charset("utf8");

function init(){
  splitIntoDocs();
}
function splitIntoDocs(){  
  ini_set('memory_limit', '1500M'); 
  $counter_file = 'counter.txt';
  $counter = file_get_contents($counter_file);

  $filename = 'docs/'.$counter.'.txt';
  $source = 'ru.vert';
  $language_id = 2;
  $sourceData = file_get_contents($source);
  $sourceDocs = explode('</doc>', $sourceData);
  array_pop($sourceDocs);
  foreach($sourceDocs as $doc){
    preg_match('/Title=".*"/u', $doc, $docHeading);
    $docHeadingAttributes = explode('" ', $docHeading[0]);
    $result = [];
    $docCridentials = [
      'title' => str_replace('"', '', explode('=', $docHeadingAttributes[0])[1]),
      'author' => str_replace('"', '', explode('=', $docHeadingAttributes[1])[1]),
      'year' => str_replace('"', '', explode('=', $docHeadingAttributes[2])[1]),
      'language_id' => $language_id
    ];
    preg_match('/<s.*\/s>/su', $doc, $docContent);
    $sentences = explode('<s>', str_replace("\n", ' ', str_replace("</s>", '', $docContent[0])));
    array_shift($sentences);
    $book_id = insertBook($docCridentials);
    foreach($sentences as $index => $sentence){
      $sentenceCridentials = [
        'book_id' => $book_id,
        'text' => $sentence,
        'index' => $index,
        'language_id' => $language_id
      ];
      insertSentence($sentenceCridentials);
    }
  }
 
  /*
  $counter_file = 'components/com_lugat_editor/models/counter.txt';
  $counter = file_get_contents($counter_file);
  
  $limit = 30000;
  $offset = (int) $counter;
  echo $offset."\n";
  */

  
}

init();

function insertBook($data){
    global $mysqli;
    $sql = "
        INSERT INTO  lgta_books
        SET 
        author = '".addslashes($data['author'])."', 
        title = '".addslashes($data['title'])."', 
        year = '".$data['year']."', 
        language_id = '".$data['language_id']."'
        ";
    $mysqli->query($sql);
    return $mysqli->insert_id;
}
function insertSentence($data){
  global $mysqli;
  $sql = "
      INSERT INTO  lgta_sentences
      SET 
      book_id = '".$data['book_id']."', 
      text = '".addslashes($data['text'])."', 
      `index` = '".$data['index']."', 
      language_id = '".$data['language_id']."'
  ";
  $mysqli->query($sql);
  return $mysqli->insert_id;
}