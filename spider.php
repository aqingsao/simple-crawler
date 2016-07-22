<?php
include('simple_html_dom.php');
$terms = array();
$errors = 0;

function crawl_page($term, $max_depth = 5){
  global $terms;
  global $errors;

  if($term['depth'] >= $max_depth){
    return;
  }

  $term['visited'] = true;
  $html = file_get_html('http://www.webopedia.com'.$term['url']);  
  if(!$html){
    echo 'Failed to open: '.$term['url'].PHP_EOL;
    if($errors++ > 4){
      exit();
    }
    return;
  }

  $ele = $html->find('#article_main_column p');
  if(!empty($ele)){
    $ele = $ele[0];
    $term['desc'] = $ele->plaintext;
    echo 'Successfully get content: '.$term['url'].PHP_EOL;
    file_put_contents('webopedia.out', json_encode($term).PHP_EOL, FILE_APPEND);
  }
  else{
    echo 'Failed to get content: '.$term['url'].PHP_EOL;
  }

  foreach($html->find('#related_terms a') as $element) {
    if(empty($element)){
      continue;;
    }
    $name = $element->plaintext;
    $depth = $term['depth']+1;
    if(!array_key_exists($name, $terms)){
      $terms[$name] = array('name'=>$name,'url'=>$element->href, 'depth'=>$depth);
      echo $depth. ': '.$term['name'].'-->'.$name.PHP_EOL;
      crawl_page($terms[$name], $max_depth);
      sleep(1);
    }
  }
  sleep(3);
}

crawl_page(array('name'=>'hadoop_mapreduce','url'=>"/TERM/H/hadoop_mapreduce.html", 'depth'=>0), 2);