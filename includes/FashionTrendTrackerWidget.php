<?php
/*------------------------------------------------------------------------------
Fashion Trend Tracker Widget: rotates chunks of content.
------------------------------------------------------------------------------*/
class FashionTrendTrackerWidget extends WP_Widget
{
    public $name = 'Fashion Trend Tracker';
    public $description = 'Rotates chunks of content on a periodic basis';
    /* List all controllable options here along with a default value.
    The values can be distinct for each instance of the widget. */
    public $control_options = array(
        'title'                 => 'Fashion Trend Tracker',
        'color_value' => ''
    );

//  public static $this_plugin_dir = WP_PLUGIN_DIR . dirname(dirname(__FILE__));
    
    //!!! Magic Functions   
    // The constructor. 
    function __construct()  
    {
        $widget_options = array(
            'classname'     => __CLASS__,
            'description'   => $this->description,
        );
        
        parent::__construct( __CLASS__, $this->name,$widget_options,$this->control_options);
    }
    
    //!!! Public Functions
    /*------------------------------------------------------------------------------
    Displays the widget in the manager
    OUTPUT: prints form elements 
    ------------------------------------------------------------------------------*/
    public function form( $instance )
    {
        $placeholders = array();
        
        foreach ( $this->control_options as $key => $val )
        {
            $placeholders[ $key .'.id' ]    = $this->get_field_id( $key );
            $placeholders[ $key .'.name' ]  = $this->get_field_name( $key );
            // This helps us avoid "Undefined index" notices.
            if ( isset($instance[ $key ] ) )
            {
                $placeholders[ $key .'.value' ] = esc_attr( $instance[ $key ] );
            }
            // Use the default (for new instances)
            else 
            {
                $placeholders[ $key .'.value' ] = $this->control_options[ $key ];
            }
            // $placeholders[ $key .'.label' ]  = __( FashionTrendTracker::beautify($key) );
        }
    
        $tpl = file_get_contents( dirname(dirname(__FILE__)) .'/tpls/widget_controls.tpl');
        
        print FashionTrendTracker::parse($tpl, $placeholders);
        wp_enqueue_script('jquery_color_picker_1','https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js');
        wp_enqueue_script('jquery_color_picker_2','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js');
        wp_enqueue_style('jquery_color_picker_css1','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/ui-lightness/jquery-ui.css');
        wp_enqueue_style('fashion_trend_tracker-color-picker1', plugins_url( 'css/jquery.colorpicker.css', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js1', plugins_url( 'js/jquery.colorpicker.js', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js2', plugins_url( 'js/i18n/jquery.ui.colorpicker-nl.js', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js3', plugins_url( 'js/swatches/jquery.ui.colorpicker-pantone.js', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js4', plugins_url( 'js/parts/jquery.ui.colorpicker-rgbslider.js', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js5', plugins_url( 'js/parts/jquery.ui.colorpicker-memory.js', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js6', plugins_url( 'js/parsers/jquery.ui.colorpicker-cmyk-parser.js', dirname(__FILE__)));
        wp_enqueue_script( 'colorpicker_js7', plugins_url( 'js/parsers/jquery.ui.colorpicker-cmyk-percentage-parser.js', dirname(__FILE__)));
        echo '<script>$(function() {$(".cp-revert").colorpicker({revert: true,parts: "full",showNoneButton: true});});</script>';
        // echo '<input type="text" class="cp-revert" value="" style="text-align: right"/>';
        // also echo the color picker

    }
    
    /*------------------------------------------------------------------------------
    Perform the updating of widget parameters after the manager clicks "Save". 
    ------------------------------------------------------------------------------*/ 
    public function update( $new_instance, $old_instance )
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['color_value'] = $new_instance['color_value'];
        // write code to update new changed color to disk
        echo 'changed color value '.$new_instance['color_value'];

        // insert value into post meta data the color of the widget
        $colorpicker_values = get_post_meta( 1, 'colorpicker_value', true );

        // // if the above value exists update it or else add it
        // if( ! empty( $colorpicker_values ) )
        // {
        //   update_post_meta(1, 'colorpicker_value', $new_instance['color_value']);
        // }
        // else
        // {
        //     add_post_meta(1, 'colorpicker_value', $new_instance['color_value']);
        // }

        $option_name = 'colorpicker_value' ;
        if ( get_option( $option_name ) !== false )
        {
            // The option already exists, so we just update it.
            update_option( $option_name, $new_instance['color_value'] );
        }
        else
        {
            // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
            $deprecated = null;
            $autoload = 'no';
            add_option( $option_name, $new_instance['color_value'], $deprecated, $autoload );
        }
        return $instance;

        return $instance;
    }
    
    /*------------------------------------------------------------------------------
    Displays content to the front-end, using the tpls/widget.tpl template.
    OUTPUT: prints a formated template.
    ------------------------------------------------------------------------------*/
    public function widget($args, $instance)
    {   
        extract($args, EXTR_SKIP);
        
        echo $before_widget;
        
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);     

        if (!empty($title))
          echo $before_title . $title . $after_title;;
    
        // print widget data
        echo FashionTrendTracker::get_widget_data();
        echo $after_widget;
            
        }
    
    
    //!!! Static Functions
    static function register_this_widget()
    {
        register_widget(__CLASS__);
    }
    
}
/* EOF */
