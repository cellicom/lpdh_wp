jQuery(document).ready(function ($) {
    /**
     * Initialize Scryfall Autocomplete for Commander/Partner fields
     */
    function initScryfallAutocomplete(selector) {
        var $el = $(selector);
        if (!$el.length) return;

        // Use centralized config
        var select2Config = LPDH_Scryfall.getConfig();

        // Customize for frontend if needed (e.g. placeholder)
        select2Config.placeholder = 'Search for a legendary uncommon card...';
        select2Config.width = '100%';

        $el.select2(select2Config);
    }

    // Initialize on Commander and Partner
    initScryfallAutocomplete('#commander');
    initScryfallAutocomplete('#partner');
});
