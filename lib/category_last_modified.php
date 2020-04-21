<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_category_last_modified extends structure_tweaks_base
{
  /**
   * Split categories
   */
  public static function init()
  {
    rex_extension::register('PACKAGES_INCLUDED', function () {
      if (rex_addon::get('structure')->isAvailable() && rex_request('page', 'string') == 'structure') {
        rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
      }
    });
  }
  
  /**
   * EP CALLBACK
   * @param rex_extension_point $ep
   * @return string
   */
  public static function ep(rex_extension_point $ep)
  {
    $subject = $ep->getSubject();
    
    $addon = self::addon();
    
    $show_lastmodified_categories = $addon->getConfig('show_lastmodified_categories');
    $show_lastmodified_articles = $addon->getConfig('show_lastmodified_articles');
    
    if ($show_lastmodified_categories == true) {
      $subject .= '
              <script>
  
                  $( document ).ready(function() {
                      var structureTweaks_lastModifiedCategories = new structureTweaks();                        
                      structureTweaks_lastModifiedCategories.lastModifiedCategoriesFkt();
                  });
                    $(document).on("pjax:end", function() {
                        var structureTweaks_lastModifiedCategories = new structureTweaks();                        
                      structureTweaks_lastModifiedCategories.lastModifiedCategoriesFkt();
                      });
              </script>
          ';
    };

    if ($show_lastmodified_articles == true) {
      $subject .= '
            <script>

                $( document ).ready(function() {
                    var structureTweaks_lastModifiedArticles = new structureTweaks();                        
                    structureTweaks_lastModifiedArticles.lastModifiedArticlesFkt();
                });
                  $(document).on("pjax:end", function() {
                      var structureTweaks_lastModifiedArticles = new structureTweaks();                        
                    structureTweaks_lastModifiedArticles.lastModifiedArticlesFkt();
                  });
              
            </script>
        ';
    }
    
    return $subject;
  }
}
