/**
 * LPDH Cookie Consent Initialization
 * Configured for bs-cookie-settings plugin
 */
window.addEventListener('load', function () {
    // Obtain plugin
    var cc = initCookieConsent();

    // Run plugin with LPDH configuration
    cc.run({
        current_lang: 'en',
        autoclear_cookies: true,
        page_scripts: true,
        force_consent: false,
        cookie_name: 'lpdh_cookie_consent', // Custom name to avoid conflicts
        cookie_expiration: 182,

        gui_options: {
            consent_modal: {
                layout: 'bar',               // box/cloud/bar
                position: 'bottom center',     // bottom/middle/top + left/right/center
                transition: 'slide',           // zoom/slide
                swap_buttons: false            // enable to swap buttons
            },
            settings_modal: {
                layout: 'box',                 // box/bar
                // position: 'left',           // right/left (only if layout: bar)
                transition: 'slide'            // zoom/slide
            }
        },

        languages: {
            'en': {
                consent_modal: {
                    title: 'Choose your Destiny! 🍪',
                    description: 'Hey! We use cookies to sync your digital experience, remember if you\'re a LPDH fan or a MTG Planeswalker, and keep our stats from glitching. No real cookies were harmed in the making of this site (unfortunately). By clicking "Gimme all!", you\'re basically saying "Yes" to all the digital treats. You can also micro-manage them via <a data-bs-toggle="modal" href="#bs-cookie-modal" class="cc-link">preferences</a>.',
                    primary_btn: {
                        text: 'Gimme all! 🚀',
                        role: 'accept_all'
                    },
                    secondary_btn: {
                        text: 'Only the basics 🛡️',
                        role: 'accept_necessary'
                    },
                    settings_btn: {
                        text: 'Customize my choice'
                    },
                    consent_footer: {
                        description: '<a href="/privacy-policy/">Privacy policy</a>'
                    }
                },

                settings_modal: {
                    title: 'The cookie vault 🍪',
                    save_settings_btn: 'Lock in settings',
                    accept_all_btn: 'Gimme all!',
                    reject_all_btn: 'Just the basics',
                    close_btn_label: 'Close',
                    cookie_table_headers: [
                        { col1: 'Cookie Name' },
                        { col2: 'Source' },
                        { col3: 'How Long?' },
                        { col4: 'What it does' }
                    ],
                    blocks: [
                        {
                            title: 'Your digital footprint',
                            description: 'We use cookies to ensure basic functionalities and improve your experience. You can opt-in or opt-out of each class below. Choose wisely!'
                        },
                        {
                            title: 'The essentials (Necessary)',
                            description: 'These cookies are like the engine of the site. Without them, we\'re just a static page. They cannot be disabled, sorry!',
                            toggle: {
                                value: 'necessary',
                                enabled: true,
                                readonly: true
                            }
                        },
                        {
                            title: 'Personalization (Functional)',
                            description: 'Remembers the cool stuff, like your favorite theme (Vaporwave, Lost Wood), your language, and those session details that make life easier.',
                            toggle: {
                                value: 'functional',
                                enabled: true,
                                readonly: false
                            }
                        },
                        {
                            title: 'Analytics',
                            description: 'Helps us understand how you interact with the site via Google Analytics.',
                            toggle: {
                                value: 'analytics',
                                enabled: false,
                                readonly: false
                            },
                            cookie_table: [
                                {
                                    col1: '^_ga',
                                    col2: 'google.com',
                                    col3: '2 years',
                                    col4: 'Google Analytics tracking cookie.',
                                    is_regex: true
                                },
                                {
                                    col1: '_gid',
                                    col2: 'google.com',
                                    col3: '1 day',
                                    col4: 'Google Analytics session identifier.'
                                }
                            ]
                        },
                        {
                            title: 'More information',
                            description: 'For queries regarding our cookie policy, please <a href="mailto:legendarypaupercommander@gmail.com">contact us</a>.'
                        }
                    ]
                }
            }
        }
    });
});
