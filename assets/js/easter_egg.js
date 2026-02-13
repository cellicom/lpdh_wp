/**
 * LPDH Vaporwave Easter Egg
 * Triggered by clicking the heart icon in the footer.
 */
jQuery(document).ready(function ($) {
    const eggTrigger = $('#hearts');
    const body = $('body');
    const storageKey = 'lpdh_vaporwave_egg_active';

    // Audio Objects
    const audioOn = new Audio(lpdh_egg_vars.audio_on);
    const audioOff = new Audio(lpdh_egg_vars.audio_off);

    // 1. Check LocalStorage on Load
    if (localStorage.getItem(storageKey) === 'true') {
        // Just activate without animation on load to avoid startup noise/glitch
        body.addClass('vaporwave-glitch-active');
        eggTrigger.addClass('flipped');
    }

    eggTrigger.off('click.vaporwave').on('click.vaporwave', function (e) {
        console.log('📼 Egg Trigger Clicked'); // Debug
        e.preventDefault();
        e.stopPropagation(); // Prevent bubbling

        if (body.hasClass('vaporwave-glitch-active')) {
            deactivateGlitch();
        } else {
            activateGlitch();
        }
    });

    // Helper functions
    function activateGlitch() {
        // Play Sound
        audioOn.currentTime = 0;
        audioOn.play().catch(e => console.log('Audio play failed:', e));

        // Animation: Turn On
        body.addClass('vaporwave-turn-on');

        // Add Glitch Class immediately (animation handles the fade/scale in)
        body.addClass('vaporwave-glitch-active');
        eggTrigger.addClass('flipped');

        localStorage.setItem(storageKey, 'true');
        console.log('📼 VHS Glitch Activated');

        // Remove animation class after completion
        setTimeout(() => {
            body.removeClass('vaporwave-turn-on');
        }, 400);
    }

    function deactivateGlitch() {
        // Play Sound
        audioOff.currentTime = 0;
        audioOff.play().catch(e => console.log('Audio play failed:', e));

        // Animation: Turn Off
        body.addClass('vaporwave-turn-off');

        console.log('📼 VHS Glitch Deactivating...');

        // Wait for Turn Off animation to finish
        setTimeout(() => {
            // Remove Glitch
            body.removeClass('vaporwave-glitch-active');
            eggTrigger.removeClass('flipped');
            localStorage.setItem(storageKey, 'false');

            // Remove Turn Off class
            body.removeClass('vaporwave-turn-off');

            // Animation: Turn On (Normal View)
            body.addClass('vaporwave-turn-on');
            setTimeout(() => {
                body.removeClass('vaporwave-turn-on');
                console.log('📼 VHS Glitch Deactivated');
            }, 400);

        }, 400);
    }
});
