<?php
/**
 * Template to render popups on frontend
 *
 * @var $popups
 */
global $post;

if (!$popups)
    return;

$map_post = $post;
global $mantra_audience_custom_css;
foreach ($popups as $post): setup_postdata($post);

    $id = get_the_id();

    // whether to exclude the popup
    if (!$this->is_popup_visible($id)) {
        continue;
    }

    $style = mantra_audience_get('popup_style', 'classic');

    $atts = array(
        'id' => "mantra-audience-{$id}",
        'class' => 'mantra-audience style-' . $style,
        'object-id' => $id,
        'trigger' => mantra_audience_get('popup_trigger', 'timedelay'),
        'data-style' => $style,
        'style' => 'display: none;', // hide the popup by default
    );

    if ($style == 'classic') {
        $atts['class'] .= ' mfp-hide';
        $atts['data-position'] = mantra_audience_get('popup_classic_position', 'center-center');
        $atts['data-close-overlay'] = mantra_audience_check('popup_overlay_as_close') ? 'yes' : 'no';
    } elseif ($style == 'fullscreen') {
        $atts['class'] .= ' mfp-hide';
    } elseif ($style == 'slide-out') {
        $atts['class'] .= ' ' . mantra_audience_get('popup_slide_out_position', 'bottom-right');
    }

    if ($atts['trigger'] == 'default') {
        $atts['view-count'] = mantra_audience_get('popup_page_view', 0);
    } elseif ($atts['trigger'] == 'timedelay') {
        $atts['time-delay'] = mantra_audience_get('popup_trigger_time_delay', 5);
    } elseif ($atts['trigger'] == 'scroll') {
        $atts['scroll-position'] = mantra_audience_get('popup_trigger_scroll_position', 0);
        $atts['scroll-on'] = mantra_audience_get('popup_trigger_scroll_on', 'px');
    }
    $atts['animation'] = mantra_audience_get('popup_animation', 'bounce');
    $atts['animation-exit'] = mantra_audience_get('popup_animation_exit', 'fadeOut');

    if (mantra_audience_check('popup_show_once')) {
        $atts['cookie-expiration'] = mantra_audience_get('popup_cookie_expiration', 0);
    }

    if (mantra_audience_check('popup_auto_close')) {
        $atts['auto-close'] = mantra_audience_get('popup_auto_close_delay', 5);
    }

    if (mantra_audience_check('popup_limit_count')) {
        $atts['limit-count'] = mantra_audience_get('popup_limit_count', 1);
    }

    ?>
    <div<?php echo $this->get_element_attributes($atts); ?>>
        <?php the_content(); ?>
    </div>
    <?php
    include $this->get_view_path('styles.php');

endforeach;
wp_reset_postdata();
$post = $map_post;