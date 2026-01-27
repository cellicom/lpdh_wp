<?php
/**
 * Template Name: Commander Roulette
 *
 * @package Bootscore Child
 */

get_header();
?>

<div id="content" class="site-content container py-5">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <div class="header-roulette text-center mb-5">
                <h1 class="display-4 text-warning mb-3"><i class="fas fa-dice me-2"></i> Commander Roulette</h1>
                <p class="lead text-light">Find your next LPDH Commander! Only Uncommon Legendaries Creatures.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6 text-center">

                    <?php if (is_user_logged_in()): ?>
                        <!-- Card Display Area -->
                        <div id="roulette-display" class="card-display mb-4"
                            style="min-height: 400px; display: flex; align-items: center; justify-content: center; position: relative;">

                            <!-- Wheel of Fortune -->
                            <div id="wheel-container">
                                <div class="wheel-pointer">▼</div>
                                <div id="roulette-wheel" class="wheel">
                                    <div class="wheel-inner">
                                        <span>?</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Result Container -->
                            <div id="result-container" class="d-none"></div>

                        </div>

                        <!-- Card Info (Hidden initially) -->
                        <div id="roulette-info" class="mb-4 d-none">
                            <h3 id="card-name" class="text-white"></h3>
                            <p id="card-type" class=" small"></p>
                            <a id="card-link" href="#" target="_blank" class="btn btn-outline-info btn-sm">
                                View on Scryfall <i class="fas fa-external-link-alt ms-1"></i>
                            </a>
                        </div>

                        <!-- Controls -->
                        <div class="controls">
                            <!-- Token Display -->
                            <div id="token-display" class="mb-3">
                                <span class="badge bg-secondary rounded-pill px-3 py-2 fs-6">
                                    <i class="fas fa-coins me-1 text-warning"></i>
                                    Tokens: <strong id="token-count">...</strong>
                                </span>
                            </div>

                            <button id="spin-btn"
                                class="btn btn-warning btn-lg px-5 py-3 rounded-pill shadow-lg fw-bold pulse-animation">
                                <i class="fas fa-sync-alt me-2 d-none" id="spin-icon"></i>
                                <span id="spin-text">SPIN THE WHEEL</span>
                            </button>
                        </div>

                        <div id="error-message" class="alert alert-danger mt-4 d-none"></div>
                    <?php else: ?>
                        <div class="alert alert-info py-4">
                            <h4><i class="fas fa-lock me-2"></i> Access Restricted</h4>
                            <p class="mb-3">You must be logged in to test your luck against the Blind Eternities.</p>
                            <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>"
                                class="btn btn-primary">Login</a>
                            <a href="<?php echo esc_url(wp_registration_url()); ?>"
                                class="btn btn-outline-primary ms-2">Register</a>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </main>
    </div>
</div>



<?php
get_footer();
