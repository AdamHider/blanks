
<?php

require_once("constants.inc");


function compileList(){  
    set_time_limit(9000000000);
    ini_set('memory_limit', -1);
    $counter_file = 'counter.txt';
    $counter = file_get_contents($counter_file);
    
    $filename = "D:/wordlist/wordlist_united.csv";
    $target = "wordlist_cyr/wordlist_{$counter}.csv";
    
    $limit = 30000;
    $offset = (int) $counter;

    $file = fopen($filename, 'r');
    $f = fopen($target, 'w');
    // Initialize an empty array
    $array = [];

    // Read each line and parse it into an array
    while (($data = fgets($file)) !== false) {
        print_r($data);
        die;
        $data[0] =  translate($data[0], 'crh-cyrl');
        $data[1] =  translate($data[1], 'crh-cyrl');
        $data[2] =  str_replace('|', ':', $data[2]);
        fputcsv($f, $data, "\t");
    }

    
    //$words = getWordforms($limit, $offset);
    //$words = getLemmas($limit, $offset);
    //$words = getNotFound();
    //$words = getProperNames();
    /*
    $words = getProperNamesWf($limit, $offset);
    if(count($words) == 0){ die; }
    foreach($words as $word){          
        //$word['wordform'] = $word['wordform'];
        //$word['word'] =  translate($word['word'], 'crh-latn');
        $word['wordform'] =  translate($word['wordform'], 'crh-cyrl');
        $obj = [
            $word['wordform'] //, $word['word'], $word['template']
        ];
        echo $word['wordform']."\n";
        fputcsv($fp, $obj, "\t");
        //fwrite($fp, $translitted."\n");   
    }
    
    $offset = $offset + $limit;
    */
    $counter++;
    file_put_contents($counter_file, $counter);
    
    header("Refresh: 10; URL=".$_SERVER['REQUEST_URI']);
    print_r('finish');
}
$workingDir = 'D:/wordlist';
$files = scandir($workingDir);
$filesToJoin = array_diff($files, array('.', '..'));
//joinFiles($filesToJoin, $workingDir.'/', 'wordlist_final.csv');
compileList();
function joinFiles(array $files, $dirPrefix, $result) {
  set_time_limit(9000000000);
  ini_set('memory_limit', -1);
  if(!is_array($files)) {
      throw new Exception('`$files` must be an array');
  }

  $wH = fopen($dirPrefix.$result, "w+");

  foreach($files as $file) {
      $fh = fopen($dirPrefix.$file, "r");
      while(!feof($fh)) {
          fwrite($wH, fgets($fh));
      }
      fclose($fh);
      unset($fh);
      fwrite($wH, "\n"); //usually last line doesn't have a newline
  }
  fclose($wH);
  unset($wH);
}
function parseFile($file){
    $handle = fopen($file, "r");
    $chunk_size = 1024*1024;
    $iterations = 0;
    
    $counter = 0;
    if ($handle) {
        while (!feof($handle)) {
            $iterations++;
            $chunk = fread($handle, $chunk_size);
            $words = explode("\n", $chunk);
            $total = count($words);
            $counter = $counter + $total;
        }
        fclose($handle);
    }
    
    print_r($counter);
    die;
    // Output — Read the whole file in 13 iterations.
    echo "Read the whole file in $iterations iterations.";
    die;
}


function translate( $text, $toVariant ){
    include( 'exeptions.inc' );//Exeptions members
#                global $wgContLanguageCode;
#                $text = parent::translate( $text, $toVariant );
    $pat = "";
    $nonletters = '';
    switch( $toVariant ) {
    case 'crh-cyrl':
      $letters = CRH_L_UC . CRH_L_LC ;
      $wgContLanguageCode = 'crh-Cyrl';
      foreach( $mLatn2CyrlEx as $exword => $rep ) {
        $text = str_replace( "$exword", "$rep", $text );
      }
      break;
    case 'crh-latn':
      $letters = CRH_C_UC . CRH_C_LC;
      $wgContLanguageCode = 'crh-Latn';
      foreach( $mCyrl2LatnEx as $exword => $rep ) {
        $text = str_replace( "$exword", "$rep", $text );
      }
      break;
    default:
      $wgContLanguageCode = 'crh';
      return $text;
    }
    // we split text by word
    $regexp = '/(['.$letters.']+)/u';

    $words = array(); $delims = array();
    $count = split_by_regexp( $regexp, $text, $words, $delims );
    $delims = regsConverter( $delims, $toVariant );
    // merge words and non-words
    return merge_by_turns($words, $delims);
  }


  /**
   *  It translates word into variant by regexp
   */
function regsConverter( $array_of_words, $toVariant ) {
    $text = implode("\n", $array_of_words);
    if ($text == '') return;
    include( 'regular_expressions.inc' );//Regexps members

    switch( $toVariant ) {
      // from CYRIL to LATIN
      case 'crh-latn':
        foreach( $mCyrl2Latn as $pat => $rep ) {
          if($pat == 'all_other_letters') {
            $text = strtr($text, $all_other_letters_cyr2lat);
          }else{
            $text = preg_replace( "@$pat@um", "$rep", $text );
          }
        }
      break;
      // from LATIN to CYRIL
      case 'crh-cyrl':
        foreach( $mLatn2Cyrl as $pat => $rep ) {
          if($pat == 'all_other_letters') {
            $text = strtr($text, $all_other_letters_lat2cyr);
          }else{
            $text = preg_replace( "@$pat@um", "$rep", $text );
          }
        }
    }
    return explode("\n", $text);
}

function split_by_regexp( $regexp, $string, &$words, &$delims ) {
  $matches = preg_split( $regexp, $string, -1, PREG_SPLIT_DELIM_CAPTURE);
  $string = ''; 
#  print_r($matches);  
  $words = array(); $delims = array();
  foreach( $matches as $i=>$match ) {
    // on even position we have not delimiters
    if ($i%2 == 0) {
      // in $text we collect words separated with \n
      // this is to applay transliterate function on whole string at once
      $words[] = $match; #echo $i.' \'' .$match . "' <br> ";
      
    }else{
      // on odd positions we have delimiters(our words)
      $delims[] = $match; #echo $i.' ' .$match . "<br>";
    }
  }                               
 return count($delims);
}
/*
 * merge two arrays by turns into string 
 */
function merge_by_turns( $words, $delims ) {
  $count = count($words); $string = '';
  for($i=0; $i<$count; $i++) {
    if(isset($delims[$i])){
      $string .= $words[$i].$delims[$i] ;
    }
    
  }
  return $string;
}

