<?php
/**
 * Template for displaying single event posts
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <?php while (have_posts()):
                        the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                                <?php
                                $event_date = get_field('field_event_date');
                                $place_obj = get_field('field_event_place');
                                $fb_link = get_field('field_event_fb_link');
                                ?>

                                <div class="event-details d-flex justify-content-center gap-4 mt-2 align-items-center">
                                    <?php if ($event_date): ?>
                                        <div class="event-date">
                                            <i class="fas fa-calendar-alt me-1" style="color: #003366;"></i>
                                            <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($event_date))); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($place_obj): ?>
                                        <div class="event-place">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            <a href="<?php echo get_permalink($place_obj->ID); ?>" class="text-decoration-none">
                                                <?php echo esc_html($place_obj->post_title); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($fb_link): ?>
                                        <div class="event-fb">
                                            <a href="<?php echo esc_url($fb_link); ?>" target="_blank"
                                                class="text-decoration-none" title="Evento Facebook">
                                                <i class="fab fa-facebook fa-lg" style="color: #1877F2;"></i> Event
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <?php if (has_post_thumbnail()): ?>
                                <div class="entry-featured-image mb-5 text-center">
                                    <?php the_post_thumbnail('medium_large', array('class' => 'img-fluid rounded shadow-sm', 'style' => 'max-height: 400px; width: auto;')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="entry-content mb-5">
                                <?php the_content(); ?>
                            </div>

                            <?php
                            // Rankings Table
                            $rankings = get_field('field_event_ranking');
                            if (is_array($rankings) && !empty($rankings)): ?>
                                <div class="event-rankings mb-5">
                                    <h3 class="mb-3 border-bottom pb-2">Player Rankings</h3>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                                    <th scope="col">Player</th>
                                                    <th scope="col">Deck</th>
                                                    <th scope="col" class="text-center">Points</th>
                                                    <th scope="col" class="text-center d-none d-sm-table-cell">W-D-L</th>
                                                    <th scope="col" class="text-center d-none d-md-table-cell">Via %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_rankings = count($rankings);
                                                foreach ($rankings as $index => $rank):
                                                    $pos = isset($rank['pos']) ? $rank['pos'] : '';

                                                    // Logic for Player Name and Link
                                                    $name = isset($rank['name']) ? $rank['name'] : '';
                                                    $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
                                                    $display_name = $name;
                                                    $player_profile_url = '';
                                                    $user_id = 0;

                                                    if ($player_id_field) {
                                                        if (is_array($player_id_field) && isset($player_id_field['ID'])) {
                                                            $user_id = $player_id_field['ID'];
                                                            $display_name = $player_id_field['display_name'];
                                                        } elseif (is_numeric($player_id_field)) {
                                                            $user_id = $player_id_field;
                                                            $user_info = get_userdata($user_id);
                                                            if ($user_info) {
                                                                $display_name = $user_info->display_name;
                                                            }
                                                        }

                                                        if ($user_id) {
                                                            $player_profile_url = get_author_posts_url($user_id);
                                                        }
                                                    }

                                                    $deck = isset($rank['deck']) ? $rank['deck'] : '';
                                                    $player_deck_id = isset($rank['player_deck_id']) ? $rank['player_deck_id'] : '';
                                                    $points = isset($rank['points']) ? $rank['points'] : '0';
                                                    $win = isset($rank['win']) ? $rank['win'] : '0';
                                                    $draw = isset($rank['draw']) ? $rank['draw'] : '0';
                                                    $lose = isset($rank['lose']) ? $rank['lose'] : '0';
                                                    $via = isset($rank['via']) ? $rank['via'] : '-';

                                                    // Commander & Partner Images for Popover
                                                    $icon_html = '';
                                                    $popover_content = '';

                                                    if ($player_deck_id) {
                                                        $cmdr_img_url = get_commander_image($player_deck_id);
                                                        $partner_img_url = get_partner_image($player_deck_id);

                                                        if ($cmdr_img_url && $partner_img_url) {
                                                            // Both images - Split Icon
                                                            $icon_html = '<div class="position-relative overflow-hidden rounded-circle" style="width: 40px; height: 40px;">';
                                                            $icon_html .= '<img src="' . esc_url($cmdr_img_url) . '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; left: 0; top: 0; clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);">';
                                                            $icon_html .= '<img src="' . esc_url($partner_img_url) . '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; left: 0; top: 0; clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);">';
                                                            $icon_html .= '</div>';
                                                            $popover_content = '<div class=\'d-flex\'><img src=\'' . esc_url($cmdr_img_url) . '\' class=\'me-1 rounded cmdr-popover-img\'><img src=\'' . esc_url($partner_img_url) . '\' class=\'rounded cmdr-popover-img\'></div>';
                                                        } elseif ($cmdr_img_url) {
                                                            // Only Commander
                                                            $icon_html = '<img src="' . esc_url($cmdr_img_url) . '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">';
                                                            $popover_content = '<img src=\'' . esc_url($cmdr_img_url) . '\' class=\'rounded cmdr-popover-img-large\'>';
                                                        }

                                                        if ($icon_html && $popover_content) {
                                                            $icon_html = '<a tabindex="0" class="text-decoration-none d-inline-block me-2 flex-shrink-0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true" data-bs-custom-class="deck-popover" data-bs-content="' . $popover_content . '">' . $icon_html . '</a>';
                                                        } elseif ($icon_html) {
                                                            $icon_html = '<div class="d-inline-block me-2 flex-shrink-0">' . $icon_html . '</div>';
                                                        }
                                                    }

                                                    // Colors for top 3
                                                    $row_class = '';
                                                    $pos_int = intval($pos);

                                                    if ($pos_int === 1) {
                                                        $row_class = 'rank-gold';
                                                    } elseif ($pos_int === 2) {
                                                        $row_class = 'rank-silver';
                                                    } elseif ($pos_int === 3) {
                                                        $row_class = 'rank-bronze';
                                                    }

                                                    // Last player clown emoji
                                                    $display_pos = ($index === $total_rankings - 1) ? '🤡' : esc_html($pos);

                                                    // Deck display
                                                    $deck_display = esc_html($deck);
                                                    if ($player_deck_id) {
                                                        $deck_post = get_post($player_deck_id);
                                                        if ($deck_post) {
                                                            $commander = get_field('commander', $player_deck_id);
                                                            $partner = get_field('partner', $player_deck_id);

                                                            // Link to deck
                                                            $deck_link = get_permalink($player_deck_id);
                                                            $deck_title_html = '<a href="' . esc_url($deck_link) . '" class="text-decoration-none text-reset">' . esc_html($deck_post->post_title) . '</a>';

                                                            $deck_display = '<div>' . $deck_title_html . '</div>';
                                                            if ($commander) {
                                                                $deck_display .= '<div class="small">(' . esc_html($commander) . ($partner ? ' + ' . esc_html($partner) : '') . ')</div>';
                                                            }
                                                        }
                                                    }

                                                    // Edit/Add Deck Button for Logged-in User
                                                    $action_btn = '';
                                                    if (is_user_logged_in() && $user_id == get_current_user_id()) {
                                                        if ($player_deck_id) {
                                                            $action_btn = '<a href="#" class="ms-auto open-deck-modal" data-row-index="' . $index . '" data-deck-id="' . $player_deck_id . '" title="Edit Deck"><i class="fas fa-edit"></i></a>';
                                                        } else {
                                                            $action_btn = '<a href="#" class="btn btn-sm btn-primary fw-bold shadow-sm open-deck-modal ms-auto" data-row-index="' . $index . '" data-deck-id=""><i class="fas fa-plus me-1"></i>Add Deck</a>';
                                                        }
                                                    }
                                                    ?>
                                                    <tr class="<?php echo esc_attr($row_class); ?>">
                                                        <td class="text-center fw-bold"><?php echo $display_pos; ?></td>
                                                        <td>
                                                            <?php
                                                            $avatar = get_avatar($user_id ? $user_id : 0, 24, 'mp', '', array('class' => 'rounded-circle me-2', 'style' => 'width: 24px; height: 24px;'));

                                                            if ($player_profile_url) {
                                                                echo '<a href="' . esc_url($player_profile_url) . '" class="text-decoration-none text-reset d-flex align-items-center">' . $avatar . esc_html($display_name) . '</a>';
                                                            } else {
                                                                echo '<div class="d-flex align-items-center">' . $avatar . esc_html($display_name) . '</div>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="fst-italic">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center"><?php echo $icon_html; ?>
                                                                    <div><?php echo $deck_display; ?></div>
                                                                </div>
                                                                <?php echo $action_btn; ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-center fw-bold"><?php echo esc_html($points); ?></td>
                                                        <td class="text-center d-none d-sm-table-cell">
                                                            <span
                                                                class="badge bg-success bg-opacity-10 text-success"><?php echo esc_html($win); ?></span>
                                                            <span class="">-</span>
                                                            <span
                                                                class="badge bg-secondary bg-opacity-10 text-secondary"><?php echo esc_html($draw); ?></span>
                                                            <span class="">-</span>
                                                            <span
                                                                class="badge bg-danger bg-opacity-10 text-danger"><?php echo esc_html($lose); ?></span>
                                                        </td>
                                                        <td class="text-center d-none d-md-table-cell small">
                                                            <?php echo esc_html($via); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                                    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                                        return new bootstrap.Popover(popoverTriggerEl)
                                    })
                                });
                            </script>

                            <?php if (is_user_logged_in()):
                                $current_user_decks = get_posts([
                                    'post_type' => 'deck',
                                    'author' => get_current_user_id(),
                                    'posts_per_page' => -1,
                                    'post_status' => 'publish',
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ]);
                                ?>
                                <!-- Select2 Local Assets Enqueued in functions.php -->


                                <!-- Deck Selection Modal -->
                                <div class="modal fade" id="deckSelectionModal" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?php esc_html_e('Select Your Deck', 'bootscore'); ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="deck-selection-form">
                                                    <input type="hidden" id="modal-row-index" name="row_index" value="">
                                                    <input type="hidden" id="modal-event-id" name="event_id"
                                                        value="<?php echo get_the_ID(); ?>">
                                                    <div class="mb-3">
                                                        <label for="deck-select"
                                                            class="form-label"><?php esc_html_e('Choose Deck', 'bootscore'); ?></label>
                                                        <select class="form-select" id="deck-select" name="deck_id"
                                                            style="width: 100%;">
                                                            <option value="">
                                                                <?php esc_html_e('-- Select Deck --', 'bootscore'); ?>
                                                            </option>
                                                            <?php foreach ($current_user_decks as $deck):
                                                                $cmdr = get_field('commander', $deck->ID);
                                                                $partner = get_field('partner', $deck->ID);
                                                                $cmdr_img = get_commander_image($deck->ID);
                                                                $partner_img = get_partner_image($deck->ID);

                                                                $cmdr_text = $cmdr ? esc_attr($cmdr) : '';
                                                                if ($partner)
                                                                    $cmdr_text .= ' + ' . esc_attr($partner);
                                                                ?>
                                                                <option value="<?php echo esc_attr($deck->ID); ?>"
                                                                    data-cmdr="<?php echo $cmdr_text; ?>"
                                                                    data-cmdr-img="<?php echo esc_url($cmdr_img); ?>"
                                                                    data-partner-img="<?php echo esc_url($partner_img); ?>">
                                                                    <?php echo esc_html($deck->post_title); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal"><?php esc_html_e('Cancel', 'bootscore'); ?></button>
                                                <button type="button" class="btn btn-primary"
                                                    id="save-deck-btn"><?php esc_html_e('Add', 'bootscore'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    jQuery(document).ready(function ($) {
                                        // Initialize Select2 with custom template
                                        function formatState(state) {
                                            if (!state.id) {
                                                return state.text;
                                            }

                                            var $option = $(state.element);
                                            var cmdr = $option.data('cmdr');
                                            var cmdrImg = $option.data('cmdr-img');
                                            var partnerImg = $option.data('partner-img');

                                            var $imgHtml = '';
                                            if (cmdrImg && partnerImg) {
                                                $imgHtml = '<div class="deck-option-img-split">' +
                                                    '<img src="' + cmdrImg + '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; left: 0; top: 0; clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);">' +
                                                    '<img src="' + partnerImg + '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; left: 0; top: 0; clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);">' +
                                                    '</div>';
                                            } else if (cmdrImg) {
                                                $imgHtml = '<img src="' + cmdrImg + '" class="deck-option-img-single" />';
                                            } else {
                                                // Fallback or placeholder if needed
                                                $imgHtml = '<div class="deck-option-img-single bg-secondary"></div>';
                                            }

                                            var $state = $(
                                                '<div class="d-flex align-items-center">' + $imgHtml +
                                                '<div><div class="fw-bold">' + state.text + '</div>' +
                                                '<div class="small ">(' + cmdr + ')</div></div></div>'
                                            );
                                            return $state;
                                        };

                                        $('#deck-select').select2({
                                            dropdownParent: $('#deckSelectionModal'),
                                            templateResult: formatState,
                                            templateSelection: formatState,
                                            width: '100%'
                                        });

                                        var deckModal = new bootstrap.Modal(document.getElementById('deckSelectionModal'));

                                        $('.open-deck-modal').on('click', function (e) {
                                            e.preventDefault();
                                            var rowIndex = $(this).data('row-index');
                                            var currentDeckId = $(this).data('deck-id');

                                            $('#modal-row-index').val(rowIndex);
                                            $('#deck-select').val(currentDeckId);

                                            deckModal.show();
                                        });

                                        $('#save-deck-btn').on('click', function () {
                                            var $btn = $(this);
                                            var data = {
                                                action: 'update_player_deck_ranking',
                                                nonce: '<?php echo wp_create_nonce('update_player_deck_nonce'); ?>',
                                                event_id: $('#modal-event-id').val(),
                                                row_index: $('#modal-row-index').val(),
                                                deck_id: $('#deck-select').val()
                                            };

                                            $btn.prop('disabled', true).text('Saving...');

                                            $.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                                                if (response.success) {
                                                    location.reload();
                                                } else {
                                                    alert('Error: ' + (response.data || 'Unknown error'));
                                                    $btn.prop('disabled', false).text('<?php esc_html_e('Add', 'bootscore'); ?>');
                                                }
                                            });
                                        });
                                    });
                                </script>
                            <?php endif; ?>

                            <?php
                            // Survey Section
                            $survey = get_field('survey');
                            $participant_count = is_array($survey) ? count($survey) : 0;

                            // Check if event is closed
                            $event_date = get_field('field_event_date');
                            $is_event_closed = false;
                            if ($event_date && strtotime($event_date) < current_time('timestamp')) {
                                $is_event_closed = true;
                            }

                            if (is_user_logged_in()):
                                $current_user_id = get_current_user_id();
                                $participated = false;

                                if (is_array($survey)) {
                                    foreach ($survey as $row) {
                                        $u_id = is_array($row['user']) ? $row['user']['ID'] : (is_object($row['user']) ? $row['user']->ID : $row['user']);
                                        if ($u_id == $current_user_id) {
                                            $participated = true;
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <div class="event-survey mb-5 p-4 bg-light rounded border text-center">
                                    <h4><?php esc_html_e('Are you participating in this event?', 'bootscore'); ?></h4>
                                    <p class=" mb-3 survey-count-text">
                                        <?php printf(esc_html__('Currently there are %d confirmed participants.', 'bootscore'), $participant_count); ?>
                                    </p>

                                    <?php if (!$is_event_closed): ?>
                                        <button id="btn-event-participation"
                                            class="btn <?php echo $participated ? 'btn-danger' : 'btn-success'; ?>"
                                            data-event-id="<?php echo get_the_ID(); ?>"
                                            data-participated="<?php echo $participated ? '1' : '0'; ?>">
                                            <?php echo $participated ? esc_html__('Cancel participation', 'bootscore') : esc_html__('Yes, I\'m participating', 'bootscore'); ?>
                                        </button>
                                        <div id="participation-message" class="mt-2 fw-bold"></div>
                                    <?php else: ?>
                                        <div class="alert alert-secondary d-inline-block">
                                            <?php
                                            if ($participated) {
                                                esc_html_e('You participated in this event.', 'bootscore');
                                            } else {
                                                esc_html_e('Registration for this event is closed.', 'bootscore');
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($participant_count > 0): ?>
                                        <div class="survey-participants mt-4">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                                <?php
                                                if (is_array($survey)) {
                                                    foreach ($survey as $row) {
                                                        $u_id = is_array($row['user']) ? $row['user']['ID'] : (is_object($row['user']) ? $row['user']->ID : $row['user']);
                                                        $user_info = get_userdata($u_id);
                                                        if ($user_info) {
                                                            echo '<a href="' . esc_url(get_author_posts_url($u_id)) . '" title="' . esc_attr($user_info->display_name) . '" class="text-decoration-none">';
                                                            echo get_avatar($u_id, 40, '', esc_attr($user_info->display_name), array('class' => 'rounded-circle border'));
                                                            echo '</a>';
                                                        }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <script>
                                    jQuery(document).ready(function ($) {
                                        $('#btn-event-participation').on('click', function () {
                                            var $btn = $(this);
                                            var eventId = $btn.data('event-id');

                                            $btn.prop('disabled', true);

                                            $.ajax({
                                                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                                type: 'POST',
                                                data: {
                                                    action: 'toggle_event_participation',
                                                    event_id: eventId,
                                                    nonce: '<?php echo wp_create_nonce('event_participation_nonce'); ?>'
                                                },
                                                success: function (response) {
                                                    if (response.success) {
                                                        if (response.data.action === 'added') {
                                                            $btn.data('participated', 1);
                                                            $btn.removeClass('btn-success').addClass('btn-danger');
                                                            $btn.text('<?php esc_html_e('Cancel participation', 'bootscore'); ?>');
                                                            $('#participation-message').html('<span class="text-success"><?php esc_html_e('Participation confirmed!', 'bootscore'); ?></span>');
                                                        } else {
                                                            $btn.data('participated', 0);
                                                            $btn.removeClass('btn-danger').addClass('btn-success');
                                                            $btn.text('<?php esc_html_e('Yes, I\'m participating', 'bootscore'); ?>');
                                                            $('#participation-message').html('<span class="text-warning"><?php esc_html_e('Participation cancelled.', 'bootscore'); ?></span>');
                                                        }
                                                        $('.survey-count-text').text('Attualmente ci sono ' + response.data.count + ' partecipanti confermati.');
                                                    } else {
                                                        alert(response.data.message || 'Errore');
                                                    }
                                                },
                                                complete: function () {
                                                    $btn.prop('disabled', false);
                                                }
                                            });
                                        });
                                    });
                                </script>
                            <?php else: ?>
                                <div class="event-survey mb-5 p-4 bg-light rounded border text-center">
                                    <h4><?php esc_html_e('Event Participation', 'bootscore'); ?></h4>
                                    <p class="">
                                        <?php printf(esc_html__('Currently there are %d confirmed participants.', 'bootscore'), $participant_count); ?>
                                    </p>

                                    <?php if (!$is_event_closed): ?>
                                        <p class="small mb-0">
                                            <?php esc_html_e('Log in to confirm your participation.', 'bootscore'); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="small mb-0 text-danger">
                                            <?php esc_html_e('Registrations are closed.', 'bootscore'); ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if ($participant_count > 0): ?>
                                        <div class="survey-participants mt-4">
                                            <div class="d-flex justify-content-center flex-wrap gap-2">
                                                <?php
                                                if (is_array($survey)) {
                                                    foreach ($survey as $row) {
                                                        $u_id = is_array($row['user']) ? $row['user']['ID'] : (is_object($row['user']) ? $row['user']->ID : $row['user']);
                                                        echo get_avatar($u_id, 40, '', '', array('class' => 'rounded-circle border'));
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <footer class="entry-footer mt-5 pt-4 border-top">
                                <div class="text-center">
                                    <a href="<?php echo esc_url(get_post_type_archive_link('event')); ?>"
                                        class="btn btn-primary">
                                        <i
                                            class="fas fa-arrow-left me-2"></i><?php esc_html_e('Back to Events', 'bootscore'); ?>
                                    </a>
                                </div>
                            </footer>

                        </article>

                    <?php endwhile; ?>

                </div>
            </div>
        </div>

    </main>
</div>



<?php get_footer(); ?>