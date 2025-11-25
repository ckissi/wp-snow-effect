<?php
/**
 * WordPress Settings Framework
 *
 * @author Gilbert Pellegrom, James Kemp
 * @link https://github.com/gilbitron/WordPress-Settings-Framework
 * @license MIT
 */

/**
 * Define your settings
 *
 * The first parameter of this filter should be wpsf_register_settings_[options_group],
 * in this case "my_example_settings".
 *
 * Your "options_group" is the second param you use when running new WordPressSettingsFramework()
 * from your init function. It's importnant as it differentiates your options from others.
 *
 * To use the tabbed example, simply change the second param in the filter below to 'wpsf_tabbed_settings'
 * and check out the tabbed settings function on line 156.
 */

add_filter('wpsf_register_settings_snoweffect', 'snoweffect_settings');

function snoweffect_settings($wpsf_settings)
{

    // General Settings section

    $wpsf_settings[] = array(
        'section_id' => 'settings',
        'section_title' => 'Settings',
        'section_description' => '',
        'section_order' => 5,
        'fields' => array(
            array(
                'id' => 'flakes_num',
                'title' => 'Flakes Number',
                'desc' => 'Please specify the number of flakes. Default value : 30',
                'type' => 'text',
                'std' => '30'
            ),

            array(
                'id' => 'falling_speed_min',
                'title' => 'Minimal Falling Speed',
                'desc' => 'Please specify minimal falling speed. Default value : 1',
                'type' => 'text',
                'std' => '1'
            ),

            array(
                'id' => 'falling_speed_max',
                'title' => 'Maximal Falling Speed',
                'desc' => 'Please specify maximal falling speed. Default value : 3',
                'type' => 'text',
                'std' => '3'
            ),

            array(
                'id' => 'flake_min_size',
                'title' => 'Flake Minimal Size',
                'desc' => 'Please specify minimal size for flake. Default value : 10',
                'type' => 'text',
                'std' => '10'
            ),

            array(
                'id' => 'flake_max_size',
                'title' => 'Flake Maximal Size',
                'desc' => 'Please specify maximum size for flake. Default value : 20',
                'type' => 'text',
                'std' => '20'
            ),


            array(
                'id' => 'vertical_size',
                'title' => 'Snow Vertical Size',
                'desc' => 'Please specify snow vertical size (in pixels). Default value : 1000',
                'type' => 'text',
                'std' => '1000'
            ),

            array(
                'id' => 'fade_away',
                'title' => 'Fade Away ?',
                'desc' => 'Please specify if you want flakes to fade away at the bottom. Default value : Yes',
                'type' => 'checkbox',
                'std' => 'true'
            ),
            array(
                'id' => 'show_on',
                'title' => 'Show On',
                'desc' => 'Please chose where to show the snow flakes',
                'type' => 'checkboxes',
                'std' => array(
                    'home',
                    'pages',
                    'posts',
                    'archives'
                ),
                'choices' => array(
                    'home' => 'Home Page',
                    'pages' => 'Pages',
                    'posts' => 'Posts',
                    'archives' => 'Archives',
                    'mobile' => 'Mobile Devices'
                )
            ),

            array(
                'id' => 'flake_type',
                'title' => 'Flake Type',
                'desc' => 'Please specify Flake type.',
                'type' => 'select',
                'std' => '#10053',
                'choices' => array(
                    'bull' => '&bull;',
//                    '#10052' => '&#10052;',
                    '#10053' => '&#10053;',
                    '#10054' => '&#10054;',
                )
            ),
            array(
                'id' => 'flake_zindex',
                'title' => 'Z-Index',
                'desc' => 'You may experience visibility issues on some themes. If you\'ve experienced some, please try change Z-Index to larger number.',
                'type' => 'select',
                'std' => '100000',
                'choices' => array(
                    'auto' => 'auto',
                    '10000' => '10000',
                    '100000' => '100000',
                    '1000000' => '1000000',
                    '10000000' => '10000000',
                )
            ),
            array(
                'id' => 'flake_color',
                'title' => 'Flake Color',
                'desc' => 'Default color: #ffffff',
                'type' => 'color',
                'std' => '#ffffff'
            ),

        )
    );

    return $wpsf_settings;
}
