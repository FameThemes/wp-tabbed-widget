<?php
/*
Plugin Name: WP Tabbed Widget
Plugin URI: http://famethemes.com
Description:
Version: 1.0
Author: FameThemes
Author URI: http://famethemes.com
*/

define( 'WP_TABBED_WIDGET_URL', trailingslashit( plugins_url('', __FILE__) ) );
define( 'WP_TABBED_WIDGET', trailingslashit( plugin_dir_path( __FILE__) ) );






class WP_Tabbed {

    function __construct(){
        add_action( 'wp_ajax_wp_tabbed_get_settings_form', array( __CLASS__ , 'ajax_form' ) );
        //add_action( 'wp_ajax_wp_tabbed_get_settings_form', array( __CLASS__ , 'get_settings_form' ) );
    }

    public static function ajax_form() {

        $widget =  $_REQUEST['widget'];
        echo self::get_form_settings( $widget );

        die();
       // wp_die(); // this is required to terminate immediately and return a proper response
    }

    static function get_form_settings(  $widget_class , $data = array() ){
        $widget = false;
        if ( is_string( $widget_class ) ) {
            $widget =  new $widget_class;
        } if ( is_object( $widget_class ) ) {
            $widget = $widget_class;
        }

        if ( ! method_exists( $widget, 'form' ) ) {
            return  false;
        }

        $widget->number = uniqid( );
        $widget->id_base ='tab-anonymous';

        ob_start();
        ob_end_clean();
        ob_start();
        $widget->form( $data );
        $form = ob_get_clean();

        return $form;
    }

}

if ( is_admin() ) {
    new WP_Tabbed();
}




/**
 * Adds Slider widget.
 */
class WP_Tabbed_Widget extends WP_Widget {
    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'wp-tabbed-widget', // Base ID
            __( 'WP Tabbed Widget', 'wp-tabbed-widget' ), // Name
            array(
                'description' => __( 'Display a tabbed widget', 'wp-tabbed-widget' ),
                'classname' => 'wp-tabbed-widget'
            ), // Args
            array(
                'width' => 630
            )
        );
    }

    public function item( $name, $value = array(), $closed = true ){

    }

    public function form( $instance ) {
        $this_widget = get_class( $this );
        global $wp_widget_factory;


        wp_enqueue_script('jquery');
        wp_enqueue_script(' jquery-ui-sortable');
        wp_enqueue_script('wp-tabbed-admin', WP_TABBED_WIDGET_URL.'assets/js/admin-tabs.js', array( 'jquery' ), '1.0', 'true' );
        wp_enqueue_style('wp-tabbed-admin', WP_TABBED_WIDGET_URL.'assets/css/admin.css' );


        wp_localize_script( 'wp-tabbed-admin', 'WP_Tabbed_Widget_Settings', array(
            'id'      => $this->id_base,
            'untitled' => __( 'Untitled', 'wp-tabbed-widget' ),
            'nonce'   => wp_create_nonce( 'wp-tabbed-widget' ),
            'tab_tpl'     => $this->_tab_content(),
            'title_tpl'   => $this->_tab_title(),
        ) );

        $instance =  wp_parse_args( $instance, array(
            'title'             => '',
            'current_active'    => 0,
            'tabs'              => array(),
        ) );

        if ( ! is_array( $instance['tabs'] ) ) {
            $instance['tabs'] =  array();
        }

        $id = uniqid( 'wptw-' );

        $tabs_html = '';

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-coupon' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
        </p>

        <div class="wp-tw-tabs" id="<?php echo esc_attr( $id ); ?>">
            <input class="current_active" name="<?php echo $this->get_field_name( 'current_active' ); ?>" type="hidden" value="<?php echo esc_attr( $instance['current_active'] ); ?>">
            <ul class="wp-tw-nav">
                <li class="ui-state-disabled add-new-tab">
                    <span class="dashicons dashicons-plus"></span>
                </li>
                <?php
                foreach( $instance['tabs'] as $k => $data ) {
                    if ( ! isset ( $data['settings'] ) ) {
                        $data['settings'] = array();
                    }
                    $title = isset( $data['settings']['title']  ) ? $data['settings']['title'] : '';
                    echo  $this->_tab_title( $title );
                    $tabs_html .=  $this->_tab_content( $data['widget_class'], $data['settings'] );
                }
                ?>
            </ul>
            <div class="wp-tw-tab-contents">
                <?php
                print( $tabs_html );
                ?>
            </div>
        </div>
        <script type="text/javascript">
            jQuery( document).ready( function( $ ){
                new WP_Tabbed_Widget( "#<?php  echo esc_js( $id ); ?>" );
            } );
        </script>
    <?php
    }

    function _tab_title( $title = '' ){
        if ( $title == '' ){
            $title = __( 'Untitled', 'wp-tabbed-widget' );
        }
        return '<li class="wp-tw-title">
                    <span class="wp-tw-label">'.esc_html( $title ).'</span>
                    <input type="hidden" class="tab-value" name="'.$this->get_field_name( 'tabs[]' ).'" >
                    <a href="#" class="wp-tw-remove"><span class="dashicons dashicons-no-alt"></span></a>
                </li>';
    }

    function _tab_content( $widget_class = '', $data = array() ){
        global $wp_widget_factory;
        $this_widget = get_class( $this );
        ob_start();
        ?>
        <div class="wp-tw-tab-content">
            <label for="widget-wp-tabbed-widget-2-nav_menu"><?php _e( 'Select widget:', 'wp-tabbed-widget' ); ?></label>
            <select class="widget_type" name="widget_class">
                <option value=""><?php _e( '— Select —', 'wp-tabbed-widget' ); ?></option>
                <?php foreach( $wp_widget_factory->widgets as $k => $widget ) {
                    if ( $k == $this_widget ) {
                        continue;
                    }
                    ?>
                    <option <?php selected( $widget_class, $k ); ?> value="<?php echo esc_attr( $k ) ?>"><?php echo esc_html( $widget->name ); ?></option>
                    <?php
                } ?>
            </select>
            <div class="tabbed-widget-settings">
                <?php

                if ( $widget_class != '' ){
                    echo WP_Tabbed::get_form_settings( $widget_class, $data );
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {

        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }

        $instance =  wp_parse_args( $instance, array(
            'title'             => '',
            'tabs'              => array(),
        ) );
        ?>
        <ul class="wp-tabbed-tabs">
            <?php

            foreach( $instance['tabs'] as $k => $data ) {
                if ( ! isset ( $data['settings'] ) ) {
                    $data['settings'] = array();
                }

                if ( ! isset( $data['settings']['title'] ) ) {
                    $data['settings']['title'] = __( 'Untitled', 'wp-tabbed-widget' );
                }

                ?>
                <li><a data-tab="tab-<?php echo esc_attr( $k ) ?>" href="#"><?php echo esc_html( $data['settings']['title'] ); ?></a></li>
                <?php

            }

            ?>
        </ul>
        <div class="wp-tabbed-contents">
            <?php
            global $wp_widget_factory;
            foreach( $instance['tabs'] as $k => $data ) {
                if ( ! isset ( $data['settings'] ) ) {
                    $data['settings'] = array();
                }

                if ( isset( $data['settings']['title']  ) ) {
                    unset( $data['settings']['title'] );
                }

                $widget_class = isset( $data['widget_class'] ) ? $data['widget_class'] : false;
                echo '<div class="wp-tabbed-cont" data-tab-id="tab-'.esc_attr( $k ).'">';
                if ( isset( $wp_widget_factory->widgets[ $widget_class ] ) ) {
                    $wp_widget_factory->widgets[ $widget_class ]->widget( array(
                        'before_widget' => '',
                        'before_title' => '',
                        'after_title' => '',
                        'after_widget' => '',
                    ),  $data['settings'] );

                }
                echo '</div>';

                ?>
            <?php
            }
            ?>
        </div>
        <?php
        echo $args['after_widget'];

    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        global $wp_widget_factory;
        if ( isset( $new_instance['tabs'] ) ) {
            foreach ( $new_instance['tabs'] as $k => $tab ) {
                $settings =  wp_parse_args( $tab, array( 'widget_class' => '', 'widget-tab-anonymous' => '') );
                $keys = array_keys( $settings );

                $key = array_search( 'widget_class' , $keys );
                if (false !== $key) {
                    unset( $keys[$key] );
                }

                $data =  array();

                foreach ( $keys as $key ) {
                    if ( isset( $settings[ $key ] ) ) {
                        $data = current( $settings[ $key ] );
                    }
                }


                if ( isset( $wp_widget_factory->widgets[ $settings['widget_class'] ] ) ) {
                    $data = $wp_widget_factory->widgets[ $settings['widget_class'] ]->update( $data, array() );
                    $data[ 'isset' ] = 1;
                    if ( $data[ 'title' ] == '' ){
                        $data['title'] = $wp_widget_factory->widgets[ $settings['widget_class'] ]->name;
                    }
                }

                $instance['tabs'][ $k ]['widget_class'] = $settings['widget_class'];
                $instance['tabs'][ $k ]['settings'] = $data;
            }
        }

        $instance['current_active'] = isset( $new_instance['current_active'] ) ? intval( $new_instance['current_active'] ) : 0;

        return $instance;
        // return $instance;
    }

} // class Popular_Store

// register Foo_Widget widget
function wp_register_tabbed_widget() {
    register_widget( 'WP_Tabbed_Widget' );
}
add_action( 'widgets_init', 'wp_register_tabbed_widget' );
















// -------- JUST FOR DEBUG WILL REMOVE WHEN COMPLETED ----------------------

// ==================for debug===============================
if(!function_exists('st_help_screen_help')){
    add_action( 'contextual_help', 'st_help_screen_help', 10, 3 );
    function st_help_screen_help( $contextual_help, $screen_id, $screen ) {
        // The add_help_tab function for screen was introduced in WordPress 3.3.
        if ( ! method_exists( $screen, 'add_help_tab' ) )
            return $contextual_help;
        global $hook_suffix;
        // List screen properties
        $variables = '<ul style="width:50%;float:left;"> <strong>Screen variables </strong>'
            . sprintf( '<li> Screen id : %s</li>', $screen_id )
            . sprintf( '<li> Screen base : %s</li>', $screen->base )
            . sprintf( '<li>Parent base : %s</li>', $screen->parent_base )
            . sprintf( '<li> Parent file : %s</li>', $screen->parent_file )
            . sprintf( '<li> Hook suffix : %s</li>', $hook_suffix )
            . '</ul>';
        // Append global $hook_suffix to the hook stems
        $hooks = array(
            "load-$hook_suffix",
            "admin_print_styles-$hook_suffix",
            "admin_print_scripts-$hook_suffix",
            "admin_head-$hook_suffix",
            "admin_footer-$hook_suffix"
        );
        // If add_meta_boxes or add_meta_boxes_{screen_id} is used, list these too
        if ( did_action( 'add_meta_boxes_' . $screen_id ) )
            $hooks[] = 'add_meta_boxes_' . $screen_id;
        if ( did_action( 'add_meta_boxes' ) )
            $hooks[] = 'add_meta_boxes';
        // Get List HTML for the hooks
        $hooks = '<ul style="width:50%;float:left;"> <strong>Hooks </strong> <li>' . implode( '</li><li>', $hooks ) . '</li></ul>';
        // Combine $variables list with $hooks list.
        $help_content = $variables . $hooks;
        // Add help panel
        $screen->add_help_tab( array(
            'id'      => 'wptuts-screen-help',
            'title'   => 'Screen Information',
            'content' => $help_content,
        ));
        return $contextual_help;
    }
}
/// ------------------------------

/*
$current = current_time( 'timestamp' );
$day = 24*3600;

$_day =  $current- $day;
echo date_i18n( 'Y-m-d H:i:s', $_day );
*/
