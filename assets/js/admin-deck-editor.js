jQuery(document).ready(function ($) {
    /**
     * Scryfall Helper for Admin Deck Editor
     * Adds a Select2 search dropdown before Commander/Partner fields to populate them.
     */
    function initScryfallHelper() {
        $('.scryfall-autocomplete').each(function () {
            var $wrapper = $(this);
            var $textInput = $wrapper.find('input[type="text"]');

            // Avoid duplicate initialization
            if ($wrapper.find('.scryfall-helper-dropdown').length > 0) return;

            // Create helper dropdown
            var $helperContainer = $('<div class="scryfall-helper-container"></div>');
            var $select = $('<select class="scryfall-helper-dropdown" style="width: 100%;"><option value="">Search Scryfall for a card...</option></select>');

            $helperContainer.append($select);
            $textInput.before($helperContainer);

            // Initialize Select2 using shared Scryfall config
            var select2Config = LPDH_Scryfall.getConfig();
            $select.select2(select2Config);

            // Handle card selection
            $select.on('select2:select', function (e) {
                var cardName = e.params.data.id;
                $textInput.val(cardName).trigger('change');

                // Clear selection after a short delay so the user can see what they picked
                setTimeout(function () {
                    $select.val(null).trigger('change');
                }, 500);
            });
        });
    }

    // Initialize on load
    initScryfallHelper();

    // Re-initialize if ACF fields are added dynamically
    if (window.acf) {
        acf.addAction('append', function ($el) {
            initScryfallHelper();
        });
    }
});
