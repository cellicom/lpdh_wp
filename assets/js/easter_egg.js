jQuery(function ($) {
    /**
     * POINT 3: Search Rare Easter Egg
     */
    const forbiddenCards = [
        'black lotus', 'mox ruby', 'mox sapphire', 'mox jet', 'mox pearl', 'mox emerald', 'mox opal',
        'time walk', 'ancestral recall', 'timetwister', 'mox diamond', 'mana vault',
    ];

    const rejectionMessages = [
        "Sorry, this is LPDH territory. No riches allowed here!",
        "Your wallet is too heavy for this format. Go back to Modern!",
        "A rare card? On this site? I'm calling the LPDH Police.",
        "The legendary LPDH gods are offended by your search.",
        "Error 403: Wealth detected. Commoners only!",
        "You seek power? Seek it in common things."
    ];

    const $searchInput = $('#search-overlay-input');
    const $searchOverlay = $('#search-overlay');

    $('.search-form-overlay').on('submit', function (e) {
        const query = $searchInput.val().toLowerCase().trim();

        if (forbiddenCards.includes(query)) {
            e.preventDefault();

            // Check if results exist before triggering
            $.ajax({
                url: lpdh_objects.ajax_url,
                type: 'POST',
                data: {
                    action: 'check_search_results',
                    query: query,
                    nonce: lpdh_objects.nonce
                },
                success: function (response) {
                    if (response.success && !response.data.has_results) {
                        // Select a random message
                        const randomMsg = rejectionMessages[Math.floor(Math.random() * rejectionMessages.length)];

                        // Create a "Burn" effect overlay
                        const $burnOverlay = $('<div class="burn-easter-egg"><div class="burn-content"><h1>' + randomMsg + '</h1><p>Redirecting to a real LPDH deck...</p></div></div>');
                        $('body').append($burnOverlay);

                        // Hide search overlay
                        $searchOverlay.removeClass('active');
                        $('body').css('overflow', '');

                        // Trigger animation
                        setTimeout(() => {
                            $burnOverlay.addClass('active');
                        }, 10);

                        // Redirect after 5 seconds
                        setTimeout(() => {
                            window.location.href = window.location.origin + '/?random=1';
                        }, 5000);
                    } else {
                        // Results exist, allow search to proceed
                        $('.search-form-overlay').off('submit').submit();
                    }
                },
                error: function () {
                    // Fail safe: allow search
                    $('.search-form-overlay').off('submit').submit();
                }
            });
        }

        /**
         * POINT 1: Counterspell Search Easter Egg
         */
        if (query === 'counterspell' || query === 'contromagia') {
            e.preventDefault();

            // Clear input
            $searchInput.val('');

            // Hide search overlay
            $searchOverlay.removeClass('active');
            $('body').css('overflow', '');

            showCounterspellOverlay("Your search is countered");
        }
    });

    /**
     * Reusable Counterspell Overlay Function
     */
    function showCounterspellOverlay(message) {
        const randomArt = counterspellArts[Math.floor(Math.random() * counterspellArts.length)];

        // Create overlay
        const $overlay = $('<div class="deck-counter-overlay">' +
            '<div class="counter-text">' + message + '</div>' +
            '<img src="' + randomArt + '" class="counter-card-img">' +
            '</div>');

        $('body').append($overlay);

        // Animate overlay appearance
        setTimeout(() => { $overlay.addClass('active'); }, 10);

        // Cleanup after animation (approx 3 seconds total)
        setTimeout(() => {
            $overlay.fadeOut(500, function () { $(this).remove(); });
        }, 3000);
    }

    /**
     * POINT 4: Console Easter Egg
     */
    console.log(
        "%cGut and Balmor live!",
        "color: #ff4500; font-size: 30px; font-weight: bold; text-shadow: 2px 2px 0px #000, 3px 3px 0px #ff0000; padding: 10px; border: 2px solid #ff4500; border-radius: 5px; background: #1a1a1a;"
    );
    /**
     * POINT 5: Commander Tax Easter Egg
     */
    let commanderTax = 0;
    const $avatars = $('.profile-editor-form img.rounded-circle, .author-avatar img.rounded-circle');

    $avatars.on('click', function () {
        const $this = $(this);
        commanderTax += 2;

        // Remove old badge if exists
        $this.parent().find('.commander-tax-badge').remove();

        // Create and append badge
        const $badge = $('<div class="commander-tax-badge">' + commanderTax + ' Commander Tax</div>');
        $this.parent().css('position', 'relative').append($badge);

        // Thresholds
        if (commanderTax >= 20) {
            $this.addClass('tax-overload');

            if (commanderTax % 20 === 0) {
                // Show message once
                $badge.text("Commander reached the moon!\nYour Commander is so hated...");
            }
        }

        // Reset after 10 seconds of inactivity
        clearTimeout(window.taxResetTimeout);
        window.taxResetTimeout = setTimeout(() => {
            commanderTax = 0;
            $('.commander-tax-badge').fadeOut(500, function () { $(this).remove(); });
            $this.removeClass('tax-overload');
        }, 10000);
    });

    /**
     * POINT 2: Day/Night Cycle Easter Egg
     */
    let keyBuffer = "";
    $(document).on('keydown', function (e) {
        keyBuffer += e.key.toLowerCase();
        keyBuffer = keyBuffer.slice(-2); // Keep last 2 keys

        if (keyBuffer === "dn") {
            const $html = $('html');
            const currentTheme = $html.attr('data-bs-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            // Create cycle overlay
            const isNight = newTheme === 'dark';
            const cycleText = isNight ? "Nightbound" : "Daybound";
            const iconClass = isNight ? "fas fa-moon" : "fas fa-sun";
            const stateClass = isNight ? "night-state" : "day-state";

            const $cycle = $('<div class="day-night-cycle-overlay ' + stateClass + '">' +
                '<div class="cycle-content">' +
                '<div class="cycle-icon"><i class="' + iconClass + '"></i></div>' +
                '<h3>' + cycleText + '</h3>' +
                '</div></div>');
            $('body').append($cycle);

            // Trigger animation
            setTimeout(() => { $cycle.addClass('active'); }, 10);

            // Toggle theme half-way through animation
            setTimeout(() => {
                $html.attr('data-bs-theme', newTheme);
                // Save preference if needed, but for an Easter Egg it's fine just for session
            }, 1000);

            // Cleanup
            setTimeout(() => {
                $cycle.fadeOut(500, function () { $(this).remove(); });
            }, 2500);

            keyBuffer = ""; // Reset
        }
    });

    /**
     * POINT 6: Deck Counterspell Easter Egg
     */
    const counterspellArts = [
        "https://cards.scryfall.io/normal/front/6/1/610a7782-ffe4-4b1c-bcc2-b3d89357e3da.jpg?1757376533", // Marvel Universe
        "https://cards.scryfall.io/normal/front/3/6/36f9d5b0-51a5-4faa-a879-b83871ae39cc.jpg?1757550235", // Through the Omenpaths Bonus Sheet
        "https://cards.scryfall.io/normal/front/e/1/e1e48b21-1a4f-4708-a4b4-e2e296df924c.jpg?1748703796", // Final Fantasy: Through the Ages
        "https://cards.scryfall.io/normal/front/5/d/5d93b770-dc46-46ad-aefe-282dac8cc246.jpg?1749029685", // Secret Lair Drop
        "https://cards.scryfall.io/normal/front/0/5/053f724c-c88d-44c0-9902-6400c064640b.jpg?1741641585", // Secret Lair Drop
        "https://cards.scryfall.io/normal/front/6/2/62504a3f-df9f-4fab-a5fc-50fc39288052.jpg?1739103541", // Secret Lair Drop
        "https://cards.scryfall.io/normal/front/f/2/f2a7042f-a6f0-4e77-86a2-5eb0d2587363.jpg?1760608521", // URL/Convention Promos
        "https://cards.scryfall.io/normal/front/4/f/4f616706-ec97-4923-bb1e-11a69fbaa1f8.jpg?1751282477", // Duskmourn: House of Horror Commander
        "https://cards.scryfall.io/normal/front/9/7/976f36ce-57b0-4364-9009-b3bbf5763050.jpg?1723221085", // Mystery Booster 2
        "https://cards.scryfall.io/normal/front/8/9/8916e24f-9c74-4b6c-9894-d60669854f35.jpg?1712162366", // MagicFest 2024
        "https://cards.scryfall.io/normal/front/7/c/7c3271da-cc20-48c2-ac61-b64a8e47f9e5.jpg?1755162163", // Secret Lair Drop
        "https://cards.scryfall.io/normal/front/7/e/7e47212d-399e-4121-92d4-9fb0d85767cb.jpg?1690002060", // Commander Masters
        "https://cards.scryfall.io/normal/front/8/4/8493131c-0a7b-4be6-a8a2-0b425f4f67fb.jpg?1689996248", // Commander Masters
        "https://cards.scryfall.io/normal/front/1/b/1b73577a-8ca1-41d7-9b2b-7300286fde43.jpg?1680795078", // Dominaria Remastered
        "https://cards.scryfall.io/normal/front/d/1/d14d5ff3-40c0-4f22-91ad-d6c8447cb9e0.jpg?1675201573", // Dominaria Remastered
        "https://cards.scryfall.io/normal/front/0/2/02da8709-4228-4fed-9d2d-781e686661df.jpg?1675199223", // Dominaria Remastered
        "https://cards.scryfall.io/normal/front/5/2/52042404-12db-4914-bfa0-8249a5942088.jpg?1674674379", // Starter Commander Decks
        "https://cards.scryfall.io/normal/front/4/d/4dd995aa-6aa1-495e-8d00-37fdbdbdbc7b.jpg?1664927402", // 30th Anniversary Edition
        "https://cards.scryfall.io/normal/front/6/8/68787949-4c50-47a1-a28c-05ea37dd13cd.jpg?1664924744", // 30th Anniversary Edition
        "https://cards.scryfall.io/normal/front/4/7/47973a25-a600-49ed-b92e-83cdd65be1e3.jpg?1673301430", // Game Night: Free-for-All
        "https://cards.scryfall.io/normal/front/f/3/f35ec9da-f38b-4b7f-9eb5-090ca7755668.jpg?1682712820", // Secret Lair Drop
        "https://cards.scryfall.io/normal/front/1/d/1d051df1-6fca-4712-afcd-c2ef5693f4be.jpg?1681081786", // Magic Online Promos
        "https://cards.scryfall.io/normal/front/5/9/5932f2dc-3bb6-4f5e-8e8d-c62e7413ac0a.jpg?1675536835", // Secret Lair Drop
        "https://cards.scryfall.io/normal/front/2/c/2c358d75-01ad-4487-8104-425124b96aae.jpg?1702814066", // Modern Horizons 2
        "https://cards.scryfall.io/normal/front/1/9/1920dae4-fb92-4f19-ae4b-eb3276b8dac7.jpg?1628801663", // Modern Horizons 2
        "https://cards.scryfall.io/normal/front/7/9/791cc2f2-2ec6-4df0-8c59-a31dee319729.jpg?1736927635", // Media and Collaboration Promos
        "https://cards.scryfall.io/normal/front/8/1/8118282a-1473-4c0b-a283-1f58e0d0209a.jpg?1638111985", // Strixhaven Mystical Archive
        "https://cards.scryfall.io/normal/front/f/f/ffdf9d2a-c163-43df-9a2f-20b8749c86ae.jpg?1631491044", // Strixhaven Mystical Archive
        "https://cards.scryfall.io/normal/front/e/0/e095aa89-15d1-4532-9b06-92a278fe57ec.jpg?1630638625", // Magic Online Promos
        "https://cards.scryfall.io/normal/front/f/5/f5c6f284-fc07-4849-8210-80f12f77f518.jpg?1608918439", // Commander Legends
        "https://cards.scryfall.io/normal/front/c/e/ce30f926-bc06-46ee-9f35-0cdf09a67043.jpg?1618695699", // Commander Legends
        "https://cards.scryfall.io/normal/front/a/4/a457f404-ddf1-40fa-b0f0-23c8598533f4.jpg?1645328634", // The List
        "https://cards.scryfall.io/normal/front/6/a/6ac949a7-51ea-4c7e-ad46-87e5ee88b99b.jpg?1573507224", // The List
        "https://cards.scryfall.io/normal/front/1/6/16b0b5e6-ac30-417b-938a-31cda2f697f8.jpg?1698800439", // Signature Spellbook: Jace
        "https://cards.scryfall.io/normal/front/c/c/cca8eb95-d071-46a4-885c-3da25b401806.jpg?1562441143", // Masters 25
        "https://cards.scryfall.io/normal/front/8/b/8b8fb42c-e900-4495-ba5a-f62ceb4681cc.jpg?1562546347", // Magic Online Promos
        "https://cards.scryfall.io/normal/front/3/1/3126d20f-1082-4ebc-b2fa-b12be3ba1bac.jpg?1562904991", // Amonkhet Invocations
        "https://cards.scryfall.io/normal/front/0/c/0c9a7cb0-5bff-48ff-b620-2838816ac9b5.jpg?1580013910", // Eternal Masters
        "https://cards.scryfall.io/normal/front/5/0/500e6211-c101-43f1-a03a-1750f762deaf.jpg?1562429367", // Tempest Remastered
        "https://cards.scryfall.io/normal/front/2/a/2a6c681a-2b5f-4c4e-81c3-91e8aba47985.jpg?1562378229", // Duel Decks Anthology: Jace vs. Chandra
        "https://cards.scryfall.io/normal/front/1/8/18388c02-8b29-4f83-877b-706c68a9ee29.jpg?1562899820", // Vintage Masters
        "https://cards.scryfall.io/normal/front/e/b/ebd2bfed-3765-4dd7-a2ec-b27d8d44359d.jpg?1562548953", // Magic Online Promos
        "https://cards.scryfall.io/normal/front/1/1/113675bf-4916-4902-a40f-4b587ac0bebe.jpg?1562899139", // Masters Edition IV
        "https://cards.scryfall.io/normal/front/7/1/71cfcba5-1571-48b8-a3db-55dca135506e.jpg?1562843855", // Duel Decks: Jace vs. Chandra
        "https://cards.scryfall.io/normal/front/6/c/6c4de9a0-b778-4e58-ab7d-23aeddffc5af.jpg?1562869046", // Masters Edition II
        "https://cards.scryfall.io/normal/front/3/9/394d65a0-0792-43d5-ab09-354cf8984428.jpg?1562639750", // Friday Night Magic 2005
        "https://cards.scryfall.io/normal/front/2/2/22b31ea9-f967-4e5a-b293-c7773cc4a0ba.jpg?1561894909", // World Championship Decks 2002
        "https://cards.scryfall.io/normal/front/e/c/ec4fb462-9226-43cb-862f-7467762e6c8b.jpg?1562492004", // World Championship Decks 2001
        "https://cards.scryfall.io/normal/front/1/8/180fa82e-9e25-4a9c-b9a7-3e1dffaaa5ea.jpg?1562490071", // World Championship Decks 2001
        "https://cards.scryfall.io/normal/front/4/6/4652c51c-881f-4732-872d-17f60847ef29.jpg?1562490542", // World Championship Decks 2001
        "https://cards.scryfall.io/normal/front/5/3/5323b7eb-976b-49fa-bc95-53337b43f9a3.jpg?1562490579", // World Championship Decks 2001
        "https://cards.scryfall.io/normal/front/8/b/8bed211e-f3ec-4e9e-b9a7-0989930dd049.jpg?1675830185", // Seventh Edition
        "https://cards.scryfall.io/normal/front/2/9/29bb1b85-9444-4bfa-b622-092a6873631c.jpg?1562234566", // Seventh Edition
        "https://cards.scryfall.io/normal/front/0/b/0bd56820-a903-4118-bbef-3e091789482f.jpg?1562897460", // Beatdown Box Set
        "https://cards.scryfall.io/normal/front/0/a/0a448077-3b1f-4efd-a606-e3ff40fe1621.jpg?1562766130", // World Championship Decks 2000
        "https://cards.scryfall.io/normal/front/7/7/7700d907-e212-4893-8c65-02c8906a803c.jpg?1761043476", // Judge Gift Cards 2000
        "https://cards.scryfall.io/normal/front/9/a/9a765377-bc8c-480a-9903-bd942c20fc47.jpg?1562931415", // Battle Royale Box Set
        "https://cards.scryfall.io/normal/front/7/b/7bd03c80-7812-4704-9e07-9cf73b49c01f.jpg?1562381815", // Mercadian Masques
        "https://cards.scryfall.io/normal/front/b/2/b2227782-6709-4d0a-82b4-5070a7ddb647.jpg?1562875043", // Starter 1999
        "https://cards.scryfall.io/normal/front/e/e/ee0d3f5f-7790-4772-bead-5d7114a23e94.jpg?1562824353", // Classic Sixth Edition
        "https://cards.scryfall.io/normal/front/b/2/b230b4f7-0e2a-42ba-a780-27c9714b70a2.jpg?1562932586", // World Championship Decks 1998
        "https://cards.scryfall.io/normal/front/d/a/dacdd380-71cf-4832-bd02-3697501325f3.jpg?1562056885", // Tempest
        "https://cards.scryfall.io/normal/front/1/3/13c474f7-38f1-4e46-8619-dd2a1b23c158.jpg?1562899172", // World Championship Decks 1997
        "https://cards.scryfall.io/normal/front/4/a/4a196a26-d03e-4535-940c-0e7976da216d.jpg?1562910475", // World Championship Decks 1997
        "https://cards.scryfall.io/normal/front/b/9/b975289d-d8b8-46b4-8c60-d6ed4b594519.jpg?1562593755", // Fifth Edition
        "https://cards.scryfall.io/normal/front/7/0/7065deea-6117-47d4-9d72-fc67af5bb483.jpg?1561757383", // DCI Legend Membership
        "https://cards.scryfall.io/normal/front/7/0/703dc932-3f90-47e9-8d13-aad1b65f1651.jpg?1562915791", // Pro Tour Collector Set
        "https://cards.scryfall.io/normal/front/1/4/14b62ca7-2147-422e-97dd-573b474ef97f.jpg?1562898925", // Pro Tour Collector Set
        "https://cards.scryfall.io/normal/front/a/e/aedbcbaa-40f0-485f-8427-778edc2d2ec0.jpg?1562927522", // Ice Age
        "https://cards.scryfall.io/normal/front/e/8/e8493631-6c9c-40a8-b7de-ecf26ba6bf7d.jpg?1559601564", // Fourth Edition
        "https://cards.scryfall.io/normal/front/8/8/885b5ca4-b8e4-4a66-9508-a12396959253.jpg?1559593080", // Summer Magic / Edgar
        "https://cards.scryfall.io/normal/front/a/7/a7b5b4b1-1df0-46c4-97ff-f0ca2d1c91fb.jpg?1539998257", // Foreign Black Border
        "https://cards.scryfall.io/normal/front/0/a/0a1b4e2e-5459-4fae-81d9-1e882647daac.jpg?1559597169", // Revised Edition
        "https://cards.scryfall.io/normal/front/1/e/1ec94c01-4e28-4aa6-ae6d-76aec880dbc7.jpg?1559591924", // Intl. Collectors' Edition
        "https://cards.scryfall.io/normal/front/9/0/901efc0f-b444-41bf-ab55-2a2860aa4a52.jpg?1559591591", // Collectors' Edition
        "https://cards.scryfall.io/normal/front/7/c/7c3271da-cc20-48c2-ac61-b64a8e47f9e5.jpg?1559592109", // Unlimited Edition
        "https://cards.scryfall.io/normal/front/9/e/9e11bf7c-f439-4529-b29a-d711359807ef.jpg?1559591924", // Limited Edition Beta
        "https://cards.scryfall.io/normal/front/0/d/0df55e3f-14de-46ef-b6b1-616618724d9e.jpg?1559591713", // Limited Edition Alpha
        "https://cards.scryfall.io/normal/front/8/9/89aafeb7-31e3-4389-9c4e-4903c342a021.jpg", // Mana Tithe (STA Japanese)
        "https://cards.scryfall.io/normal/front/e/7/e7f32354-893d-4f0b-b555-e0757fb5443b.jpg", // Mana Tithe (STA English)
        "https://cards.scryfall.io/normal/front/9/a/9ae707d5-d81d-4320-b947-6016dc188898.jpg", // Mana Tithe (TSR)
        "https://cards.scryfall.io/normal/front/a/b/ab03b4e2-91ee-449b-bb8a-ac340ecdd582.jpg", // Mana Tithe (The List)
        "https://cards.scryfall.io/normal/front/c/1/c114301d-0ee7-4a70-bbbb-c5b8bb9fcd90.jpg", // Mana Tithe (MTGO)
        "https://cards.scryfall.io/normal/front/6/5/652b0ce3-293d-4599-8a04-9df01b9bc678.jpg", // Mana Tithe (Player Rewards)
        "https://cards.scryfall.io/normal/front/7/d/7d48d622-f397-4f31-b1a5-0c23f60aa71c.jpg"  // Mana Tithe (Planar Chaos)
    ];

    /**
     * POINT 6: Deck Counterspell Easter Egg
     */
    $('.deck-image-wrapper img').on('click', function () {
        showCounterspellOverlay("Your commander is countered");
    });
});
