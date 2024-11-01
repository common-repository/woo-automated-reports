jQuery(function () {
    jQuery('select').each(function(){
        jQuery(this).select2({minimumResultsForSearch: -1});
    });
});
