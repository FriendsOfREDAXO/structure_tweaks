/**
 * @author Friends Of Redaxo
 */
var structureTweaks = function() {
   /**
     * @type {Array}
     */
    this.hiddenArticles = [];

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
    }
};
