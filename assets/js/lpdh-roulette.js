document.addEventListener('DOMContentLoaded', function () {
    const spinBtn = document.getElementById('spin-btn');
    const spinText = document.getElementById('spin-text');
    const spinIcon = document.getElementById('spin-icon');
    const display = document.getElementById('roulette-display');
    const wheelContainer = document.getElementById('wheel-container');
    const wheel = document.getElementById('roulette-wheel');
    const resultContainer = document.getElementById('result-container');
    const infoArea = document.getElementById('roulette-info');
    const cardName = document.getElementById('card-name');
    const cardType = document.getElementById('card-type');
    const cardLink = document.getElementById('card-link');
    const errorMsg = document.getElementById('error-message');

    // Initialize Token UI
    if (typeof lpdh_roulette_vars !== 'undefined' && lpdh_roulette_vars.initial_stats) {
        updateTokenDisplay(lpdh_roulette_vars.initial_stats);
    }

    if (spinBtn) {
        // Initial Check for 0 tokens
        if (typeof lpdh_roulette_vars !== 'undefined' &&
            lpdh_roulette_vars.initial_stats &&
            !lpdh_roulette_vars.initial_stats.is_admin &&
            lpdh_roulette_vars.initial_stats.remaining <= 0) {

            spinBtn.disabled = true;
            spinText.innerText = 'NO SPINS LEFT';
            showError('You have used all your spins for today. Come back tomorrow!');
        }

        spinBtn.addEventListener('click', function () {
            if (spinBtn.disabled) return;

            setLoading(true);
            errorMsg.classList.add('d-none');
            infoArea.classList.add('d-none');
            resultContainer.classList.add('d-none'); // Hide previous result
            wheelContainer.classList.remove('d-none'); // Show wheel

            // Reset wheel to 0deg without transition for replayability
            wheel.style.transition = 'none';
            wheel.style.transform = 'rotate(0deg)';

            // Force reflow
            wheel.offsetHeight;

            // Calculate target rotation (at least 5 spins + random)
            // 5 spins = 1800deg. Random = 0-360.
            const spins = 5;
            const degrees = Math.floor(Math.random() * 360);
            const totalRotation = (spins * 360) + degrees;

            // Start spinning with easing
            // cubic-bezier(0.2, 0.8, 0.3, 1) provides a nice ease-out
            const duration = 4000; // 4 seconds
            wheel.style.transition = `transform ${duration}ms cubic-bezier(0.25, 0.1, 0.25, 1)`;
            wheel.style.transform = `rotate(${totalRotation}deg)`;

            const startTime = Date.now();

            // Prepare FormData for AJAX
            const formData = new FormData();
            formData.append('action', 'lpdh_spin_roulette');
            formData.append('nonce', lpdh_roulette_vars.nonce); // Security

            fetch(lpdh_roulette_vars.ajax_url, {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(response => {
                    const elapsed = Date.now() - startTime;
                    const remaining = Math.max(0, duration - elapsed);

                    if (!response.success) {
                        // Handle Logic Error (e.g. No Tokens)
                        spinBtn.disabled = false;
                        spinText.innerText = 'SPIN THE WHEEL';
                        wheel.style.transition = 'none'; // Stop wheel
                        showError(response.data.message);
                        return;
                    }

                    const data = response.data;

                    setTimeout(() => {
                        // Animation finished (or close to)

                        // Add specific delay after stop as requested (0.5s)
                        setTimeout(() => {
                            displayCard(data.card);
                            updateTokenDisplay({
                                remaining: data.remaining_spins,
                                is_admin: data.remaining_spins > 100 // Safe check
                            });
                            setLoading(false);
                        }, 500);
                    }, remaining);
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Stop animation immediately if error
                    wheel.style.transition = 'none';
                    showError('System Error. Please try again.');
                    setLoading(false);
                });
        });
    }

    function updateTokenDisplay(stats) {
        const countEl = document.getElementById('token-count');
        if (!countEl) return;

        if (stats.is_admin || stats.remaining > 100) {
            countEl.innerHTML = '∞';
        } else {
            countEl.innerText = stats.remaining + '/3';
        }
    }

    function displayCard(card) {
        let imageUrl = '';
        if (card.image_uris && card.image_uris.normal) {
            imageUrl = card.image_uris.normal;
        } else if (card.card_faces && card.card_faces[0].image_uris) {
            // Handle double-faced cards
            imageUrl = card.card_faces[0].image_uris.normal;
        }

        // Hide wheel, show result
        wheelContainer.classList.add('d-none');
        resultContainer.innerHTML = `<img src="${imageUrl}" class="img-fluid animate__animated animate__fadeIn" alt="${card.name}">`;
        resultContainer.classList.remove('d-none');

        cardName.innerText = card.name;
        cardType.innerText = card.type_line;
        cardLink.href = card.scryfall_uri;

        infoArea.classList.remove('d-none');
        infoArea.classList.add('animate__animated', 'animate__fadeInUp');
    }

    function setLoading(isLoading) {
        spinBtn.disabled = isLoading;
        if (isLoading) {
            spinText.innerText = 'SPINNING...';
            spinIcon.classList.remove('d-none');
            spinIcon.classList.add('fa-spin');
        } else {
            spinText.innerText = 'SPIN AGAIN';
            spinIcon.classList.add('d-none');
            spinIcon.classList.remove('fa-spin');
        }
    }

    function showError(msg) {
        errorMsg.innerText = msg || 'The Blind Eternities are silent... (API Error).';
        errorMsg.classList.remove('d-none');
        // Do not hide the wheel on error, as per user request to show it static if 0 tokens
        // wheelContainer.classList.add('d-none'); 
    }
});
