<?php 


require_once("constants.inc");
set_time_limit(80000000000);
function init(){
    compileList();
}
function compileList(){  
    ini_set('memory_limit', '1500M'); 
    $counter_file = 'counter.txt';
    $counter = file_get_contents($counter_file);

    $filename = 'wordlists_tagged_cyr/wordlist_'.$counter.'.csv';
    $source = 'wordlists_tagged/wordlist_'.$counter.'.csv';
    /*
    $counter_file = 'components/com_lugat_editor/models/counter.txt';
    $counter = file_get_contents($counter_file);
    
    $limit = 30000;
    $offset = (int) $counter;
    echo $offset."\n";
    */

    

    if (!$fp = fopen($filename, 'a')) {
      echo "Cannot open file ($filename)";
      exit;
 }
    // Open wordlist file.
    $handle = fopen($source, "r") or die("Couldn't get handle");
    if ($handle) {
        while (($data = fgetcsv($handle, 1000, "\t")) !== false) {
            $array[] = $data;
            //$buffer = fgets($handle, 4096);
            //$word['wordform'] = $word['wordform'];
            //$word['word'] =  translate($word['word'], 'crh-latn');
            $data[0] =  translate($data[0], 'crh-cyrl');
            $data[1] =  translate($data[1], 'crh-cyrl');
            $data[2] = str_replace('|', ':', $data[2]);

            fputcsv($fp, $data, "\t");
            //fwrite($fp, $word_cyr."\n");  
            // Process buffer here..
        }
        fclose($handle);
    }
    $counter++;
    file_put_contents($counter_file, $counter);
    
    header("Refresh: 10; URL=".$_SERVER['REQUEST_URI']);
    print_r('finish');
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
    for($i = 0; $i < $count; $i++) {
        if(isset($words[$i]) && isset($delims[$i])){
            $string .= $words[$i].$delims[$i] ;
        } 
    }
    return $string;
}
  
init();