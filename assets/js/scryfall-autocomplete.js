/**
 * LPDH Scryfall Autocomplete Utilities
 * Centralizes the Select2 configuration and formatting for Scryfall card searches.
 */
var LPDH_Scryfall = (function ($) {
    'use strict';

    /**
     * Shared formatting for the dropdown result items
     */
    function formatCardResult(card) {
        if (card.loading) return card.text;

        var imgUrl = card.image || '';
        var markup = '<div class="scryfall-result">' +
            (imgUrl ? '<img src="' + imgUrl + '" class="scryfall-image" />' : '') +
            '<span class="scryfall-name">' + card.text + '</span>' +
            '</div>';

        return markup;
    }

    /**
     * Shared formatting for the selected item
     */
    function formatCardSelection(card) {
        return card.text || card.id;
    }

    /**
     * Generates a standard Select2 configuration for Scryfall
     * @param {string} querySuffix - Additional filters (e.g., commander filters)
     * @returns {Object} Select2 configuration object
     */
    function getSelect2Config(querySuffix) {
        querySuffix = querySuffix || 'type:legendary (game:paper) is:commander r:u prefer:best';

        return {
            ajax: {
                url: 'https://api.scryfall.com/cards/search',
                dataType: 'json',
                delay: 400,
                data: function (params) {
                    return {
                        q: params.term + ' ' + querySuffix
                    };
                },
                processResults: function (data) {
                    if (!data || !data.data) return { results: [] };

                    var results = data.data.map(function (card) {
                        return {
                            id: card.name,
                            text: card.name,
                            image: card.image_uris ? card.image_uris.small : (card.card_faces ? card.card_faces[0].image_uris.small : '')
                        };
                    });

                    return { results: results };
                },
                cache: true
            },
            minimumInputLength: 3,
            templateResult: formatCardResult,
            templateSelection: formatCardSelection,
            escapeMarkup: function (m) { return m; }
        };
    }

    return {
        getConfig: getSelect2Config,
        formatResult: formatCardResult,
        formatSelection: formatCardSelection
    };

})(jQuery);
