/**
 * @author Friends Of Redaxo
 */
var structureTweaks = function() {
   /**
     * @type {Array}
     */
    this.hiddenArticles = [];
   /**
     * @type {Array}
     */
    this.hiddenCategories = [];
   /**
     * @type {Array}
     */
    this.hiddenCategoryRows = [];
    /**
     * @type {Array}
     */
    this.splitterCategories = [];

    /**
     * @see Addon quick_navigation
     * @param key
     * @returns {*}
     */
    this.getUrlVars = function(key) {
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });

        if (key !== undefined) {
            return vars[key];
        }

        return vars;
    };

    /**
     * @param articles
     * @returns {structureTweaks}
     */
    this.setHiddenArticles = function(articles) {
        this.hiddenArticles = JSON.parse(articles);

        return this;
    };

    /**
     * @param articles
     * @returns {structureTweaks}
     */
    this.setHiddenCategories = function(articles) {
        this.hiddenCategories = JSON.parse(articles);

        return this;
    };

    /**
     * @param articles
     * @returns {structureTweaks}
     */
    this.setHiddenCategoryRows = function(articles) {
        this.hiddenCategoryRows = JSON.parse(articles);

        return this;
    };

    /**
     * @param categories
     * @returns {structureTweaks}
     */
    this.setSplitterCategories = function(categories) {
        this.splitterCategories = JSON.parse(categories);

        return this;
    };

    /**
     * Hide articles
     * @returns {structureTweaks}
     */
    this.hideArticles = function() {
        var categoryId = this.getUrlVars('category_id');

        /*if (this.getUrlVars('page') == 'linkmap') {
            jQuery(".rex-icon-startarticle").parents("li").addClass("rex-startarticle");
        } */

        if (categoryId && this.hiddenArticles.indexOf(categoryId) > -1) {
            jQuery(".rex-startarticle").addClass("is-hidden");
        }

        return this;
    };

    /**
     * Hide category functions
     * @returns {structureTweaks}
     */
    this.hideCategoryFunctions = function() {
        var clangId = this.getUrlVars('clang');
        if (clangId === undefined) {
            clangId = 1;
        }

        var articleId = this.getUrlVars('article_id');
        if (articleId === undefined) {
            articleId = 0;
        }

        var categoryId = this.getUrlVars('category_id');
        if (categoryId === undefined) {
            categoryId = 0;
        }

        var catStart = this.getUrlVars('catstart');
        if (catStart === undefined) {
            catStart = 0;
        }

        for (var i = 0; i < this.hiddenCategories.length; i++) {
            var searchStart = 'index.php?page=structure&category_id=' + categoryId + '&article_id=' + articleId + '&clang=' + clangId;
            var searchEnd   = '&catstart=' + catStart;

            var $categoryStatus = $('a[href="' + searchStart + '&category-id=' + this.hiddenCategories[i] + '&rex-api-call=category_status' + searchEnd + '"]');
            if ($categoryStatus.length) {
                $categoryStatus.parents('td').addClass('structure-tweaks-status').parents('tr').addClass('structure-tweaks-container');
            }

            var $categoryDelete = $('a[href="' + searchStart + '&category-id=' + this.hiddenCategories[i] + '&rex-api-call=category_delete' + searchEnd + '"]');
            if ($categoryDelete.length) {
                $categoryDelete.parents('td').addClass('structure-tweaks-delete').parents('tr').addClass('structure-tweaks-container');
            }

            var $categoryMeta = $('a[href="' + searchStart + '&edit_id=' + this.hiddenCategories[i] + '&function=edit_cat' + searchEnd + '"]');
            if ($categoryMeta.length) {
                $categoryMeta.parents('td').addClass('structure-tweaks-meta').parents('tr').addClass('structure-tweaks-container');
            }
        }

        return this;
    };

    /**
     * Hide categories
     * @returns {structureTweaks}
     */
    this.hideCategories = function() {
        var that = this;
        jQuery(".rex-page-section").first().find(".rex-table-id").each(function() {
            var categoryId = $(this).html();
            if (that.hiddenCategoryRows.indexOf(categoryId) >= 0) {
                $(this).parents('tr').addClass('structure-tweaks-category is-hidden');
            }
        });

        return this;
    };

    /**
     * Split categories
     * @returns {structureTweaks}
     */
    this.splitCategories = function() {
        var clangId = this.getUrlVars('clang');
        if (clangId === undefined) {
            clangId = 1;
        }
        var articleId = this.getUrlVars('article_id');
        if (articleId === undefined) {
            articleId = 0;
        }

        for (var i = 0; i < this.splitterCategories.length; i++) {
            var search = 'index.php?page=structure&category_id=' + this.splitterCategories[i]['article_id'] + '&article_id=' + articleId + '&clang=' + clangId;
            var $categoryRow = $('a[href="' + search + '"]');
            var label = this.splitterCategories[i]['label'];
            if (!label) {
                label = '&nbsp;';
            }

            // Insert splitter
            if ($categoryRow.length) {
                $categoryRow
                    .parents('tr').before('<tr class="structure-tweaks-splitter"><td colspan="2"></td><td>' + label + '</td><td colspan="4"></td></tr>')
                    .parents('.panel').addClass('structure-tweaks-splitted');
            }
        }

        return this;
    };

    /**
     * @returns {structureTweaks}
     */
    this.pageCategories = function() {
        var value = jQuery('#rex-structure-tweaks-startartikel-type option:selected').val();
        if (value === undefined) {
            value = "";
        }

        if (value != 'split_category') {
            jQuery("#rex-structure-tweaks-startartikel-label").parents('dl').slideUp(100);
        }

        jQuery('#rex-structure-tweaks-startartikel-type').change(function() {
            var value = jQuery(this).find('option:selected').val();

            if (value == 'split_category') {
                jQuery("#rex-structure-tweaks-startartikel-label").parents('dl').slideDown(100);
            } else {
                jQuery("#rex-structure-tweaks-startartikel-label").parents('dl').slideUp(100);
            }
        });

        return this;
    };
};
