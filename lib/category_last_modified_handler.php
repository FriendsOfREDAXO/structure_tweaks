<?php 

class rex_api_getLastModifiedCategories extends rex_api_function {

    protected $published = true;

    function execute() {
      
      error_reporting(0);
      header("Content-Type: text/html; charset=utf-8");
      
      $addon = rex_addon::get('structure_tweaks');
      
      $sql = rex_sql::factory();
      $articles = $sql->getArray('SELECT * FROM '.rex::getTable(article).' WHERE clang_id = '.rex_clang::getCurrentId().' ');
      
      $return = [];
      foreach ($articles as $article) {        
        
        $format = $addon->getConfig('format');
        if ($format == '') $format = "d.m.Y";
        
        $datewidth = $addon->getConfig('datewidth');
        if ($datewidth == '') $datewidth = "80px";
        
        $userwidth = $addon->getConfig('userwidth');
        if ($userwidth == '') $userwidth = "80px";

        $item = [
          'article_id' => $article['id'],
          'clangId' => $article['clang_id'],
          'label' => $article['label'],
          'updatedate' => date($format,strtotime($article['updatedate'])),
          'updateuser' => $article['updateuser'],
          'datewidth' => $datewidth,
          'userwidth' => $userwidth,
        ];
        
        $return[] = $item;
      }
      
      echo json_encode($return);
      exit;
    }
}

?>