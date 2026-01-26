jQuery(document).ready(function ($) {
    /**
     * Initialize Scryfall Autocomplete for Commander/Partner fields
     */
    function initScryfallAutocomplete(selector) {
        $(selector).select2({
            ajax: {
                url: 'https://api.scryfall.com/cards/search',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        // Query: term + new filters: type:legendary (game:paper) is:commander r:u prefer:best
                        q: params.term + ' type:legendary (game:paper) is:commander r:u prefer:best'
                    };
                },
                processResults: function (data) {
                    if (!data.data) return { results: [] };

                    return {
                        results: data.data.map(function (card) {
                            return {
                                id: card.name,
                                text: card.name,
                                image: card.image_uris ? card.image_uris.small : (card.card_faces ? card.card_faces[0].image_uris.small : '')
                            };
                        })
                    };
                },
                cache: true
            },
            placeholder: 'Search for a legendary uncommon card...',
            minimumInputLength: 3,
            width: '100%',
            templateResult: formatCard,
            templateSelection: formatCardSelection
        });
    }

    /**
     * Format the dropdown item with an image
     */
    function formatCard(card) {
        if (card.loading) return card.text;

        var $container = $(
            "<div class='scryfall-result'>" +
            "<img src='" + (card.image || '') + "' class='scryfall-image' />" +
            "<div class='scryfall-name'>" + card.text + "</div>" +
            "</div>"
        );

        return $container;
    }

    /**
     * Format the selected item
     */
    function formatCardSelection(card) {
        return card.text || card.id;
    }

    // Initialize on Commander and Partner
    initScryfallAutocomplete('#commander');
    initScryfallAutocomplete('#partner');
});
