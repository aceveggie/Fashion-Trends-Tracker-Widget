<?php
/*------------------------------------------------------------------------------
Helper functions
------------------------------------------------------------------------------*/
class FashionTrendTracker {

    public static $page = 'fashion-trends';
    
    /*------------------------------------------------------------------------------
    Adds a menu item inside the WordPress admin
    ------------------------------------------------------------------------------*/
    static function add_menu_item()
    {
        add_submenu_page(
            'plugins.php',                          // Menu page to attach to
            'Fashion Trend Tracker Configuration',        // page title
            'Fashion Trend Tracker',                      // menu title
            'manage_options',                       // permissions
            FashionTrendTracker::$page,                  // page-name (used in the URL)
            'FashionTrendTracker::generate_admin_page'   // clicking callback function
        );
    }

    /*------------------------------------------------------------------------------
    SYNOPSIS: takes a string e.g. my_title and makes it more humanly readable: My Title
    ------------------------------------------------------------------------------*/
/*
    static function beautify($str)
    {
        $str = preg_replace('/[-_]/',' ',$str);
        $str = ucwords(strtolower($str));   
        return $str;
    }
*/


    /*------------------------------------------------------------------------------
    Controller that generates admin page
    ------------------------------------------------------------------------------*/
    static function generate_admin_page()
    {

        $msg = ''; // used to display a success message on updates
        
        if ( !empty($_POST) && check_admin_referer('fashion_trend_tracker_admin_options_update') )
        {
            
            update_option('fashion_trend_tracker_content_separator', 
                stripslashes($_POST['separator']) );
            update_option('fashion_trend_tracker_content_block', 
                stripslashes($_POST['content_block']) );    

            $msg = '<div class="updated"><p>Your settings have been <strong>updated</strong></p></div>';
        }
        include('admin_page.php');

    }


    /*------------------------------------------------------------------------------
    Fetch and return a piece of random content
    ------------------------------------------------------------------------------*/
    static function get_widget_data()
    {        

        $args = array(
              'post_type' => 'post',
              'orderby'   => 'date',
              'order'     => 'ASC',
              'post_status' => 'publish',
              'posts_per_page' => -1,
            );
        $category_posts = new WP_Query( $args );
        $q_results = array();
        $q_title_id_map = array();
        
        $i= 0;
        if($category_posts->have_posts())
        {
            while($category_posts->have_posts())
            {
                $the_post = $category_posts->the_post();
                array_push($q_results, get_the_title());
                $q_title_id_map[get_the_title()] = get_the_ID();
                $i = $i +1;
            }
        }

        $time_elapsed = 120;
        $trend_name = 'pastel';
        // $html_data = self::get_basic_trend_count($q_title_id_map);
        // $html_data = self::get_title_with_random_rank($q_results);
        // $html_data = self::get_title_with_id($q_title_id_map);
        // $html_data = self::get_postid_date_trend("print");
        // $time_elapsed can take values 30, 60, 180
        
        // echo 'posts with '.$trend_name;
        // $html_data = self::get_specific_trend_list($trend_name);
        // $html_data = self::return_random_html();
        
        // $html_data = self::get_random_html_colorpicker();
        
        
        // $html_data = self::get_all_posts_with_trend_count_within($time_elapsed);
        // $html_data = self::get_formatted_trends_in_past($time_elapsed);
        $html_data = self::get_formatted_attractive_trends_in_past_with_tabs();

        return $html_data;
    }

    static function register_jquery_scripts()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui.js');
    }

    static function get_title_with_random_rank($q_results)
    {
        //code to get page title with up or down arrow (rank) and return them as html.
        $html_data = '<div id = "trend_widget">';
        $html_data .= '<ul>';
        $i =1;
        $arrow_up_url = plugins_url( 'images/up.jpg' , dirname(__FILE__) );
        $arrow_down_url = plugins_url( 'images/down.jpg' , dirname(__FILE__) );
        foreach ($q_results as &$value)
        {
            if($i%2==0)
                $html_data .= "<li>$i. ".$value.'<img src = "'.$arrow_up_url.'" height = "12" width = "12"></li>';
            else
                $html_data .= "<li>$i. ".$value.'<img src = "'.$arrow_down_url.'" height = "12" width = "12"></li>';
            $i += 1;
        }
        $html_data .= '</ul>';
        $html_data .= '</div>';
        return $html_data;
    }

    static function get_title_with_id($q_title_id_map)
    {
        // code to get page title with its associate id and return them as html
        $html_data ='<b>title<====>ID</b>';
        $html_data .='<br/><br/><br/><ul>';
        foreach($q_title_id_map as $title_name=>$id_name )
        {
            $html_data .='<li>'.$title_name.'<====>'.$id_name.'</li>';
        }

        $html_data .= '</ul>';
        return $html_data;
    }

    static function get_basic_trend_count($q_title_id_map)
    {
        
        $html_data = "<ul>";
        // code to count trend in the database and return them as html
        
        $c_count = array();

        // initialize c_count to zero
        foreach($q_title_id_map as $title_name => $id_name )
        {
            $meta_values = get_post_meta( $id_name, 'trend_name', true);

            // remove extra white spaces
            $meta_values = preg_replace( '/\s+/', ' ', $meta_values );

            // take each meta value and split it at ', '
            $trend_list = explode(",", $meta_values);
            foreach($trend_list as &$eachTrend)
            {
                $eachTrend = trim($eachTrend);
                $eachTrend = ltrim($eachTrend);
                $eachTrend = rtrim($eachTrend);
                if(strlen($eachTrend) == 0)
                    continue;
                $c_count[$eachTrend] = 0;
            }
        }
        
        // increment c_count per occurence
        foreach($q_title_id_map as $title_name => $id_name )
        {
            $meta_values = get_post_meta( $id_name, 'trend_name', true);
            // take each meta value and split it at ', '
            $trend_list = explode(",", $meta_values);
            foreach($trend_list as &$eachTrend)
            {
                $eachTrend = trim($eachTrend);
                $eachTrend = ltrim($eachTrend);
                $eachTrend = rtrim($eachTrend);
                if(strlen($eachTrend) == 0)
                    continue;
                $c_count[$eachTrend] += 1;
            }
        }

        foreach($c_count as $key => $value)
        {
            $html_data .='<li>'.$key.'--->&nbsp;'.$value.'</li>';
        }

        $html_data .= "</ul>";
        return $html_data;
    }

    static function get_postid_date_trend($mode)
    {
        $html_data = '<ul>';
        global $wpdb;

        $result1 = $wpdb->get_results('SELECT * FROM `wp_postmeta` WHERE meta_key = \'trend_create_date\' or meta_key = \'trend_name\';');
        
        //$html_data .= $wpdb->show_errors();

        $post_id_array = array();
        foreach($result1 as $row)
        {
            $d1 = array(
                "date" => "",
                "tags" => "",
                );
            $post_id_array[$row->post_id] = $d1;
        }

        foreach ($result1 as $row)
        {
            $meta_key = $row->meta_key;
            $meta_value = $row->meta_value;
            if($meta_key == "trend_name")
                $post_id_array[$row->post_id]["tags"] = $row->meta_value;
            else if($meta_key == "trend_create_date")
                $post_id_array[$row->post_id]["date"] = date('m/d/y', strtotime($row->meta_value));
            else
                continue;
        }

        foreach ($post_id_array as $key => $value)
            $html_data .= "<li>postid: ".$key."<-->post date: ".$post_id_array[$key]["date"]."<-->tags: ".$post_id_array[$key]["tags"]."</li>";

        // $html_data .= '<li>still getting data</li>';
        // $html_data .= '<li>still getting data</li>';
        $html_data .= '</ul>';

        if($mode=="print")
            return $html_data;
        else if($mode == "data")
            return $post_id_array;
    }

    static function get_all_posts_with_trend_count_within($elapsed)
    {
        
        $post_id_array = self::get_postid_date_trend("data");
        $filtered_post_id_array = array();

        $html_data = "<ul><b>posts made in past ".$elapsed." days</b><br/>";
        $date1 = new DateTime();
        // now only select certain number of posts before today's date depending on number of months
        foreach ($post_id_array as $key => $value)
        {
            // $key is the post id
            // $post_id_array[$key]["date"] is the date
            // $post_id_array[$key]["tags"] are the tags
            
            $date2 = $post_id_array[$key]["date"];
            if(self::get_days_diff($date1, $date2) <$elapsed)
            {
                if (array_key_exists('tags', $post_id_array[$key]))
                {
                    $filtered_post_id_array[$key] = $post_id_array[$key];
                }
                
            }
            // $elapsed_time = self::get_date_diff($date1, $date2, $mode);
            // $html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."<--> elapsed ".$elapsed_time."</li>";
        }

        $trend_list = array();
        $trend_dict = array();
        foreach ($filtered_post_id_array as $key => $value)
        {
            //$html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
            $trends = $filtered_post_id_array[$key]["tags"];
            $tag_list = explode(",", $trends);
            foreach ($tag_list as &$value)
            {
                $value = trim($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if(strlen($value)<=0)
                    continue;
                //$html_data .= "<li>".$value."<-->".strlen($value)."</li>";
                $trend_dict[$value] = 0;
            }
        }

        foreach ($filtered_post_id_array as $key => $value)
        {
            //$html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
            $trends = $filtered_post_id_array[$key]["tags"];
            $tag_list = explode(",", $trends);

            foreach ($tag_list as &$value)
            {
                $value = trim($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if(strlen($value)<=0)
                    continue;
                // $html_data .= "<li>".$value."</li>";
                $trend_dict[$value] += 1;
            }
        }
        // "<li>$i. ".$value.'<img src = "'.$arrow_up_url.'" height = "12" width = "12"></li>';
        
        $arrow_up_url = plugins_url( 'images/up.jpg' , dirname(__FILE__) );
        $arrow_down_url = plugins_url( 'images/down.jpg' , dirname(__FILE__) );

      
        foreach ($trend_dict as $key => $value)
        {
            //$html_data .= "<li>".$key." = ".$value."</li>";
            if($value >2)
                $html_data .= '<li> <a href="?s='.$key.'"\>'.$key.'&nbsp;&nbsp;</a>&nbsp;&nbsp;('.$value.') <img src = "'.$arrow_up_url.'" height = "12" width = "12"> </li>';
            else
                $html_data .= '<li> <a href="?s='.$key.'"\>'.$key.'&nbsp;&nbsp;</a>&nbsp;&nbsp;('.$value.') <img src = "'.$arrow_down_url.'" height = "12" width = "12"></li>';
        }

        $html_data .= "</ul>";

       return $html_data;
    }

    static function return_random_html()
    {
        $html_data ='<script>
  $(function() {
    $( "#accordion" ).accordion({ header: "h3", collapsible: true, active: false });
  });</script>';
        $html_data .= '<div id="accordion">
          <h3>Section 1</h3>
          <div>
            <p>
            <ul>
            <li>ptr1</li>
            <li>ptr2</li>
            <li>ptr3</li>
            </ul>
            </p>
          </div>
          <h3>Section 2</h3>
          <div>
            <p>
            Sed non urna. Donec et ante. Phasellus eu ligula. Vestibulum sit amet
            purus. Vivamus hendrerit, dolor at aliquet laoreet, mauris turpis porttitor
            velit, faucibus interdum tellus libero ac justo. Vivamus non quam. In
            suscipit faucibus urna.
            </p>
          </div>
          <h3>Section 3</h3>
          <div>
            <p>
            Nam enim risus, molestie et, porta ac, aliquam ac, risus. Quisque lobortis.
            Phasellus pellentesque purus in massa. Aenean in pede. Phasellus ac libero
            ac tellus pellentesque semper. Sed ac felis. Sed commodo, magna quis
            lacinia ornare, quam ante aliquam nisi, eu iaculis leo purus venenatis dui.
            </p>
            <ul>
              <li>List item one</li>
              <li>List item two</li>
              <li>List item three</li>
            </ul>
          </div>
          <h3>Section 4</h3>
          <div>
            <p>
            Cras dictum. Pellentesque habitant morbi tristique senectus et netus
            et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in
            faucibus orci luctus et ultrices posuere cubilia Curae; Aenean lacinia
            mauris vel est.
            </p>
            <p>
            Suspendisse eu nisl. Nullam ut libero. Integer dignissim consequat lectus.
            Class aptent taciti sociosqu ad litora torquent per conubia nostra, per
            inceptos himenaeos.
            </p>
          </div>
        </div>';
        return $html_data;
    }

    static function add_color_picker_jss()
    {


    }

    static function add_js_css_to_post_page()
    {
        // wp_deregister_script( 'jquery' );
        wp_enqueue_script( 'jquery2', "http://code.jquery.com/jquery-1.9.1.js");
        wp_enqueue_script( 'jquery1', "http://code.jquery.com/ui/1.10.3/jquery-ui.js");
        wp_enqueue_style('fashion_trend_tracker-page-load-css', 'http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
        wp_enqueue_script( 'accordion_latest_colorpicker', plugins_url( '/js/jquery.colorpicker.js', dirname(__FILE__) ));
        wp_enqueue_style('fashion_trend_tracker-page-load-css-2', plugins_url('/css/jquery.colorpicker.css'),dirname(__FILE__));

        ?><script type="text/javascript"> $(function() {$( "#accordion" ).accordion();});</script><?php
        
    }

    static function add_js_to_admin_page()
    {

    }


    static function query_db_for_trends()
    {
        wp_enqueue_script( 'ajax-script', plugins_url( 'js/getTrendLinks.js', dirname(__FILE__) ), array('jquery') );
    }

    static function get_specific_trend_list($trend_name)
    {
        global $wpdb;
        $result1 = $wpdb->get_results('SELECT post_id FROM `wp_postmeta` WHERE meta_key = \'trend_name\' and meta_value like \'%'.$trend_name.'%\';');
        $post_id_array = array();
        

        $html_data = '<ul>';

        foreach($result1 as $row)
        {
            $html_data .= '<li>'.$row->post_id.'</li>';
            //$row->post_id];
        }

        $html_data .= '</ul>';
        $html_data .= "<h1> this is so cool</h1>";
        return $html_data;
    }

    static function get_formatted_trends_in_past($elapsed)
    {
        $html_data = '<script>$(function() {
    $( "#accordion" ).accordion({
      event: "click hoverintent"
    });
  });
 
  /*
   * hoverIntent | Copyright 2011 Brian Cherne
   * http://cherne.net/brian/resources/jquery.hoverIntent.html
   * modified by the jQuery UI team
   */
  $.event.special.hoverintent = {
    setup: function() {
      $( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
    },
    teardown: function() {
      $( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
    },
    handler: function( event ) {
      var currentX, currentY, timeout,
        args = arguments,
        target = $( event.target ),
        previousX = event.pageX,
        previousY = event.pageY;
 
      function track( event ) {
        currentX = event.pageX;
        currentY = event.pageY;
      };
 
      function clear() {
        target
          .unbind( "mousemove", track )
          .unbind( "mouseout", clear );
        clearTimeout( timeout );
      }
 
      function handler() {
        var prop,
          orig = event;
 
        if ( ( Math.abs( previousX - currentX ) +
            Math.abs( previousY - currentY ) ) < 7 ) {
          clear();
 
          event = $.Event( "hoverintent" );
          for ( prop in orig ) {
            if ( !( prop in event ) ) {
              event[ prop ] = orig[ prop ];
            }
          }
          // Prevent accessing the original event since the new event
          // is fired asynchronously and the old event is no longer
          // usable (#6028)
          delete event.originalEvent;
 
          target.trigger( event );
        } else {
          previousX = currentX;
          previousY = currentY;
          timeout = setTimeout( handler, 100 );
        }
      }
 
      timeout = setTimeout( handler, 100 );
      target.bind({
        mousemove: track,
        mouseout: clear
      });
    }
  };</script>';

        $html_data .= '<script>$(function() { $( document ).tooltip();  });</script>';
        $html_data .= '<script> $(function() { $( "#accordion" ).accordion({ header: "h3", collapsible: true, active: false,heightStyle: "content" });  });</script>';
        $html_data .= '<script> $(function() { $(".ui-accordion-header").css("background","#9999E0"); });</script>';
        $html_data .= '<script> $(function() { $(".ui-accordion-header.ui-state-active").css("background","#FF6633"); });</script>';

        $post_id_array = self::get_postid_date_trend("data");

        $filtered_post_id_array = array();

        $posts_to_trends = array();

        // $html_data = "<ul><b>posts made in past ".$elapsed." days</b><br/>";
        $date1 = new DateTime();
        // now only select certain number of posts before today's date depending on number of months

        //$html_data .= '<h3 style="color:sienna;margin-left:20px;">Fashion Trends in past '.$elapsed.' days</h3>';
        $html_data .= '<h2 style="background:#f9f9f9;border-bottom:6px solid #dfdfdf;padding:25px 30px 15px 15px;width:275px;float:left">Fashion Trends in past '.$elapsed.' days</h2>';
        foreach ($post_id_array as $key => $value)
        {
            
            // $key is the post id
            // $post_id_array[$key]["date"] is the date
            // $post_id_array[$key]["tags"] are the tags
            
            $date2 = $post_id_array[$key]["date"];
            if(self::get_days_diff($date1, $date2) <$elapsed)
                if (array_key_exists('tags', $post_id_array[$key]))
                {
                    $filtered_post_id_array[$key] = $post_id_array[$key];
                }
                    
        }

        $trend_list = array();
        $trend_dict = array();
        foreach ($filtered_post_id_array as $key => $value)
        {

            //$html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
            $trends = $filtered_post_id_array[$key]["tags"];
            $tag_list = explode(",", $trends);
            foreach ($tag_list as &$value)
            {
                $value = trim($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if(strlen($value)<=0)
                    continue;
                //$html_data .= "<li>".$value."<-->".strlen($value)."</li>";
                $trend_dict[$value] = 0;
                $posts_to_trends[$value] = array();
            }

        }

        foreach ($filtered_post_id_array as $key => $value)
        {
            // for each post written within specified time
            // $html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
            $trends = $filtered_post_id_array[$key]["tags"];
            $tag_list = explode(",", $trends);
            foreach ($tag_list as &$value)
            {
                $value = trim($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if(strlen($value)<=0)
                    continue;
                // $html_data .= "<li>".$value."</li>";
                $trend_dict[$value] += 1;
                $local_array_val = $posts_to_trends[$value];

                if(in_array($key, $local_array_val))
                {

                }
                else
                    array_push($local_array_val, $key);
                $posts_to_trends[$value] = $local_array_val;
            }
        }
        // "<li>$i. ".$value.'<img src = "'.$arrow_up_url.'" height = "12" width = "12"></li>';
        
        $arrow_up_url = plugins_url( '/images/up.jpg' , dirname(__FILE__) );
        $arrow_down_url = plugins_url( '/images/down.jpg' , dirname(__FILE__) );
        $html_data .= '<div id="accordion">';
        foreach ($trend_dict as $key => $value)
        {
            if($value >2)
            {
                $html_data .= '<h3>'.$key.'&nbsp;&nbsp('.$value.')&nbsp;&nbsp;<img src = "'.$arrow_up_url.'" height = "12" width = "12"></h3><div><p><ul>';

                foreach ($posts_to_trends as $ltrend_name => $list_of_posts_array)
                {
                    $lc_array = $posts_to_trends[$ltrend_name];
                    if($key == $ltrend_name)
                    {
                        foreach ($lc_array as &$each_post_id)
                        {
                            
                                $html_data .= '<li><p><a id="show-option" href="http://localhost/wordpress/?p='.$each_post_id.'" title="'.get_the_title($each_post_id).'">'.substr(get_the_title($each_post_id),0,14).'&nbsp;.&nbsp;.&nbsp;.&nbsp;.</a></p></li>';
                        }
                    }
                }
                $html_data .= '</ul></p></div>';
            }
                
            else
            {
                $html_data .= '<h3>'.$key.'&nbsp;&nbsp('.$value.')&nbsp;&nbsp;<img src = "'.$arrow_down_url.'" height = "12" width = "12"></h3><div><p><ul>';
                foreach ($posts_to_trends as $ltrend_name => $list_of_posts_array)
                {
                    $lc_array = $posts_to_trends[$ltrend_name];
                    if($key == $ltrend_name)
                    {
                        foreach ($lc_array as &$each_post_id)
                        {
                                $html_data .= '<li><p><a id="show-option" href="http://localhost/wordpress/?p='.$each_post_id.'" title="'.get_the_title($each_post_id).'">'.substr(get_the_title($each_post_id),0,14).'&nbsp;.&nbsp;.&nbsp;.&nbsp;.</a></p></li>';
                        }
                    }
                }
                $html_data .= '<br/><li><a id="show-option" title="'.$key.'" href = "http://www.pinterest.com/search/pins/?q=fashion%20'.$key.'" target="_blank">Search on Pinterest</a></li>';
                $html_data .= '</ul></p></div>';

            }
                
        }
        $html_data .= '</div>';
        
        return $html_data;
    }

    static function include_custom_ajax_call_scripts()
    {
        //enqueue all ajax call scripts
        wp_enqueue_script( 'ajax-script', plugins_url( 'js/getTrends.js', dirname(__FILE__) ), array('jquery') );
        //echo 'successfully include getTrends.js';
    }

    static function include_latest_accordion_css_js()
    {
        wp_enqueue_script( 'jquery3', "http://code.jquery.com/jquery-latest.min.js");
        wp_enqueue_script( 'accordion_latest', plugins_url( '/js/main.js', dirname(__FILE__) ));
        wp_enqueue_style('fashion_trend_tracker-page-load-css-1', plugins_url('/css/styles.css'),dirname(__FILE__));
        wp_enqueue_script( 'accordion_latest_colorpicker', plugins_url( '/js/jquery.colorpicker.js', dirname(__FILE__) ));
        wp_enqueue_style('fashion_trend_tracker-page-load-css-2', plugins_url('/css/jquery.colorpicker.css'),dirname(__FILE__));
    }

    static function get_formatted_attractive_trends_in_past_with_tabs()
    {
        $html_data = '<script>$(function() {
    $( "#accordion" ).accordion({
      event: "click hoverintent"
    });
  });
 
  /*
   * hoverIntent | Copyright 2011 Brian Cherne
   * http://cherne.net/brian/resources/jquery.hoverIntent.html
   * modified by the jQuery UI team
   */
  $.event.special.hoverintent = {
    setup: function() {
      $( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
    },
    teardown: function() {
      $( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
    },
    handler: function( event ) {
      var currentX, currentY, timeout,
        args = arguments,
        target = $( event.target ),
        previousX = event.pageX,
        previousY = event.pageY;
 
      function track( event ) {
        currentX = event.pageX;
        currentY = event.pageY;
      };
 
      function clear() {
        target
          .unbind( "mousemove", track )
          .unbind( "mouseout", clear );
        clearTimeout( timeout );
      }
 
      function handler() {
        var prop,
          orig = event;
 
        if ( ( Math.abs( previousX - currentX ) +
            Math.abs( previousY - currentY ) ) < 7 ) {
          clear();
 
          event = $.Event( "hoverintent" );
          for ( prop in orig ) {
            if ( !( prop in event ) ) {
              event[ prop ] = orig[ prop ];
            }
          }
          // Prevent accessing the original event since the new event
          // is fired asynchronously and the old event is no longer
          // usable (#6028)
          delete event.originalEvent;
 
          target.trigger( event );
        } else {
          previousX = currentX;
          previousY = currentY;
          timeout = setTimeout( handler, 100 );
        }
      }
 
      timeout = setTimeout( handler, 100 );
      target.bind({
        mousemove: track,
        mouseout: clear
      });
    }
  };</script>';

        $html_data = '<script>
                      $(function() {
                        $( "#accordion-1" ).accordion({
                          event: "click hoverintent"
                        });
                      });
                      $(function() {
                        $( "#accordion-2" ).accordion({
                          event: "click hoverintent"
                        });
                      });
                      /*
                       * hoverIntent | Copyright 2011 Brian Cherne
                       * http://cherne.net/brian/resources/jquery.hoverIntent.html
                       * modified by the jQuery UI team
                       */
                      $.event.special.hoverintent = {
                        setup: function() {
                          $( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
                        },
                        teardown: function() {
                          $( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
                        },
                        handler: function( event ) {
                          var currentX, currentY, timeout,
                            args = arguments,
                            target = $( event.target ),
                            previousX = event.pageX,
                            previousY = event.pageY;
                     
                          function track( event ) {
                            currentX = event.pageX;
                            currentY = event.pageY;
                          };
                     
                          function clear() {
                            target
                              .unbind( "mousemove", track )
                              .unbind( "mouseout", clear );
                            clearTimeout( timeout );
                          }
                     
                          function handler() {
                            var prop,
                              orig = event;
                     
                            if ( ( Math.abs( previousX - currentX ) +
                                Math.abs( previousY - currentY ) ) < 7 ) {
                              clear();
                     
                              event = $.Event( "hoverintent" );
                              for ( prop in orig ) {
                                if ( !( prop in event ) ) {
                                  event[ prop ] = orig[ prop ];
                                }
                              }
                              // Prevent accessing the original event since the new event
                              // is fired asynchronously and the old event is no longer
                              // usable (#6028)
                              delete event.originalEvent;
                     
                              target.trigger( event );
                            } else {
                              previousX = currentX;
                              previousY = currentY;
                              timeout = setTimeout( handler, 100 );
                            }
                          }
                     
                          timeout = setTimeout( handler, 100 );
                          target.bind({
                            mousemove: track,
                            mouseout: clear
                          });
                        }
                      };
                      $(function() {
                        $( "#tabs" ).tabs({
                          event: "mouseover"
                        });
                      });</script>';
        $html_data .= '<script> $(function() { $( "#accordion-1" ).accordion({ header: "h3", collapsible: true, active: false,heightStyle: "content", autoHeight: false });  });</script>';
        $html_data .= '<script> $(function() { $( "#accordion-2" ).accordion({ header: "h3", collapsible: true, active: false,heightStyle: "content", autoHeight: false });  });</script>';

        $html_data .= '<script>$(function() { $( document ).tooltip();  });</script>';
        $html_data .= '<script> $(function() { $(".ui-accordion-header").css("background","#9999E0"); });</script>';
        // get the color value of the widget. if it doesn't exist use color of #9999E0

        // $colorpicker_values = get_post_meta( 1, 'colorpicker_value', true );
        
        // echo '$colorpicker_values'. $colorpicker_values;

        // if( ! empty( $colorpicker_values ) )
        // {
        //     $html_data .= '<script> $(function() { $(".ui-accordion-header").css("background","#'.$colorpicker_values.'"); });</script>';
        // }
        // else
        // {
        //     $html_data .= '<script> $(function() { $(".ui-accordion-header").css("background","#9999E0"); });</script>';            
        // }

        $colorpicker_values = get_option( 'colorpicker_value', '9999E0' );
        $html_data .= '<script> $(function() { $(".ui-accordion-header").css("background","#'.$colorpicker_values.'"); });</script>';

        // $html_data .= '<script> $(function() { $(".ui-accordion-header.ui-state-active").css("background","#FF6633"); });</script>';

        // first get all data
        $post_id_array = self::get_postid_date_trend("data");

        $duration_window = array(60,120);
        $html_data .= '<div id="tabs"><ul>';
        $i = 0;
        foreach ($duration_window as &$elapsed)
        {
            $i += 1;
            $html_data .= '<li><a href="#tabs-'.$i.'">Past '.$elapsed.' day Trends</a></li>';
        }
        $html_data .= '</ul>';

        $i = 0;
        foreach ($duration_window as &$elapsed)
        {
            $i += 1;
            $html_data .= '<div id="tabs-'.$i.'">';
            $html_data .= '<div id="accordion-'.$i.'">';

            // closing accordion div
            # code...

            $filtered_post_id_array = array();

            $posts_to_trends = array();

            // $html_data = "<ul><b>posts made in past ".$elapsed." days</b><br/>";
            $date1 = new DateTime();
            // now only select certain number of posts before today's date depending on number of months

            //$html_data .= '<h3 style="color:sienna;margin-left:20px;">Fashion Trends in past '.$elapsed.' days</h3>';
            
            foreach ($post_id_array as $key => $value)
            {
                
                // $key is the post id
                // $post_id_array[$key]["date"] is the date
                // $post_id_array[$key]["tags"] are the tags
                
                $date2 = $post_id_array[$key]["date"];
                if(self::get_days_diff($date1, $date2) <$elapsed)
                    if (array_key_exists('tags', $post_id_array[$key]))
                    {
                        $filtered_post_id_array[$key] = $post_id_array[$key];
                    }
                        
            }

            $trend_list = array();
            $trend_dict = array();
            foreach ($filtered_post_id_array as $key => $value)
            {

                //$html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
                $trends = $filtered_post_id_array[$key]["tags"];
                $tag_list = explode(",", $trends);
                foreach ($tag_list as &$value)
                {
                    $value = trim($value);
                    $value = ltrim($value);
                    $value = rtrim($value);
                    if(strlen($value)<=0)
                        continue;
                    //$html_data .= "<li>".$value."<-->".strlen($value)."</li>";
                    $trend_dict[$value] = 0;
                    $posts_to_trends[$value] = array();
                }

            }

            foreach ($filtered_post_id_array as $key => $value)
            {
                // for each post written within specified time
                // $html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
                $trends = $filtered_post_id_array[$key]["tags"];
                $tag_list = explode(",", $trends);
                foreach ($tag_list as &$value)
                {
                    $value = trim($value);
                    $value = ltrim($value);
                    $value = rtrim($value);
                    if(strlen($value)<=0)
                        continue;
                    // $html_data .= "<li>".$value."</li>";
                    $trend_dict[$value] += 1;
                    $local_array_val = $posts_to_trends[$value];

                    if(in_array($key, $local_array_val))
                    {

                    }
                    else
                        array_push($local_array_val, $key);
                    $posts_to_trends[$value] = $local_array_val;
                }
            }
            // "<li>$i. ".$value.'<img src = "'.$arrow_up_url.'" height = "12" width = "12"></li>';
            
            $arrow_up_url = plugins_url( '/images/up.jpg' , dirname(__FILE__) );
            $arrow_down_url = plugins_url( '/images/down.jpg' , dirname(__FILE__) );
            foreach ($trend_dict as $key => $value)
            {
                if($value >2)
                {
                    $html_data .= '<h3>'.$key.'&nbsp;&nbsp('.$value.')&nbsp;&nbsp;<img src = "'.$arrow_up_url.'" height = "12" width = "12"></h3><div><p><ul>';

                    foreach ($posts_to_trends as $ltrend_name => $list_of_posts_array)
                    {
                        $lc_array = $posts_to_trends[$ltrend_name];
                        if($key == $ltrend_name)
                        {
                            foreach ($lc_array as &$each_post_id)
                            {
                                
                                    $html_data .= '<li><p><a id="show-option" href="http://localhost/wordpress/?p='.$each_post_id.'" title="'.get_the_title($each_post_id).'">'.substr(get_the_title($each_post_id),0,14).'&nbsp;.&nbsp;.&nbsp;.&nbsp;.</a></p></li>';
                            }
                        }
                    }
                    $html_data .= '</ul></p></div>';
                }
                    
                else
                {
                    $html_data .= '<h3>'.$key.'&nbsp;&nbsp('.$value.')&nbsp;&nbsp;<img src = "'.$arrow_down_url.'" height = "12" width = "12"></h3><div><p><ul>';
                    foreach ($posts_to_trends as $ltrend_name => $list_of_posts_array)
                    {
                        $lc_array = $posts_to_trends[$ltrend_name];
                        if($key == $ltrend_name)
                        {
                            foreach ($lc_array as &$each_post_id)
                            {
                                    $html_data .= '<li><p><a id="show-option" href="http://localhost/wordpress/?p='.$each_post_id.'" title="'.get_the_title($each_post_id).'">'.substr(get_the_title($each_post_id),0,14).'&nbsp;.&nbsp;.&nbsp;.&nbsp;.</a></p></li>';
                            }
                        }
                    }
                    $html_data .= '<br/><li><a id="show-option" title="'.$key.'" href = "http://www.pinterest.com/search/pins/?q=fashion%20'.$key.'" target="_blank">Search on Pinterest</a></li>';
                    $html_data .= '</ul></p></div>';

                }
                    
            }

            $html_data .= '</div>';
            //  close accordion
            $html_data .= '</div>';
            // close local tab-1, tab-2
        }

        $html_data .= '</div>';
        // closing outer tab div

        return $html_data;
    }



    static function get_random_htmlformatted_attractive_trends_in_past_with_tabs()
    {

        $html_data = '<script>$(function() {
    $( "#accordion" ).accordion({
      event: "click hoverintent"
    });
  });
 
  /*
   * hoverIntent | Copyright 2011 Brian Cherne
   * http://cherne.net/brian/resources/jquery.hoverIntent.html
   * modified by the jQuery UI team
   */
  $.event.special.hoverintent = {
    setup: function() {
      $( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
    },
    teardown: function() {
      $( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
    },
    handler: function( event ) {
      var currentX, currentY, timeout,
        args = arguments,
        target = $( event.target ),
        previousX = event.pageX,
        previousY = event.pageY;
 
      function track( event ) {
        currentX = event.pageX;
        currentY = event.pageY;
      };
 
      function clear() {
        target
          .unbind( "mousemove", track )
          .unbind( "mouseout", clear );
        clearTimeout( timeout );
      }
 
      function handler() {
        var prop,
          orig = event;
 
        if ( ( Math.abs( previousX - currentX ) +
            Math.abs( previousY - currentY ) ) < 7 ) {
          clear();
 
          event = $.Event( "hoverintent" );
          for ( prop in orig ) {
            if ( !( prop in event ) ) {
              event[ prop ] = orig[ prop ];
            }
          }
          // Prevent accessing the original event since the new event
          // is fired asynchronously and the old event is no longer
          // usable (#6028)
          delete event.originalEvent;
 
          target.trigger( event );
        } else {
          previousX = currentX;
          previousY = currentY;
          timeout = setTimeout( handler, 100 );
        }
      }
 
      timeout = setTimeout( handler, 100 );
      target.bind({
        mousemove: track,
        mouseout: clear
      });
    }
  };</script>';

        $html_data = '<script>
                      $(function() {
                        $( "#accordion-1" ).accordion({
                          event: "click hoverintent"
                        });
                      });
                      $(function() {
                        $( "#accordion-2" ).accordion({
                          event: "click hoverintent"
                        });
                      });
                      /*
                       * hoverIntent | Copyright 2011 Brian Cherne
                       * http://cherne.net/brian/resources/jquery.hoverIntent.html
                       * modified by the jQuery UI team
                       */
                      $.event.special.hoverintent = {
                        setup: function() {
                          $( this ).bind( "mouseover", jQuery.event.special.hoverintent.handler );
                        },
                        teardown: function() {
                          $( this ).unbind( "mouseover", jQuery.event.special.hoverintent.handler );
                        },
                        handler: function( event ) {
                          var currentX, currentY, timeout,
                            args = arguments,
                            target = $( event.target ),
                            previousX = event.pageX,
                            previousY = event.pageY;
                     
                          function track( event ) {
                            currentX = event.pageX;
                            currentY = event.pageY;
                          };
                     
                          function clear() {
                            target
                              .unbind( "mousemove", track )
                              .unbind( "mouseout", clear );
                            clearTimeout( timeout );
                          }
                     
                          function handler() {
                            var prop,
                              orig = event;
                     
                            if ( ( Math.abs( previousX - currentX ) +
                                Math.abs( previousY - currentY ) ) < 7 ) {
                              clear();
                     
                              event = $.Event( "hoverintent" );
                              for ( prop in orig ) {
                                if ( !( prop in event ) ) {
                                  event[ prop ] = orig[ prop ];
                                }
                              }
                              // Prevent accessing the original event since the new event
                              // is fired asynchronously and the old event is no longer
                              // usable (#6028)
                              delete event.originalEvent;
                     
                              target.trigger( event );
                            } else {
                              previousX = currentX;
                              previousY = currentY;
                              timeout = setTimeout( handler, 100 );
                            }
                          }
                     
                          timeout = setTimeout( handler, 100 );
                          target.bind({
                            mousemove: track,
                            mouseout: clear
                          });
                        }
                      };
                      $(function() {
                        $( "#tabs" ).tabs({
                          event: "mouseover"
                        });
                      });</script>';
        $html_data .= '<script> $(function() { $( "#accordion-1" ).accordion({ header: "h3", collapsible: true, active: false,heightStyle: "content", autoHeight: false });  });</script>';
        $html_data .= '<script> $(function() { $( "#accordion-2" ).accordion({ header: "h3", collapsible: true, active: false,heightStyle: "content", autoHeight: false });  });</script>';

        $html_data .= '<script>$(function() { $( document ).tooltip();  });</script>';
        $html_data .= '<script> $(function() { $(".ui-accordion-header").css("background","#9999E0"); });</script>';
        $html_data .= '<script> $(function() { $(".ui-accordion-header.ui-state-active").css("background","#FF6633"); });</script>';
        
        
        $html_data .= ' 
<div id="tabs">
    <ul>
        <li><a href="#tabs-1">Nunc tincidunt</a></li>
        <li><a href="#tabs-2">Proin dolor</a></li>
    </ul>
    <div id="tabs-1">
        <div id="accordion-1">
            <h3>Section 1</h3>
            <div>
                <a href="http://www.google.com" id="show-option" title = "this is so cool tooltip">A Fashion Trend</a>
            </div>
            <h3>Section 2</h3>
            <div>
                <a href="http://www.google.com" id="show-option" title = "this is so cool tooltip">A Fashion Trend</a>
            </div>
        </div>
    </div>
    <div id="tabs-2">
        <div id="accordion-2">
            <h3>Section 1</h3>
            <div>
                <a href="http://www.google.com" id="show-option" title = "this is so cool tooltip">A Fashion Trend</a>
            </div>
            <h3>Section 2</h3>
            <div>
                <a href="http://www.google.com" id="show-option" title = "this is so cool tooltip">A Fashion Trend</a>
            </div>
        </div>
    </div>
</div>';
        
        return $html_data;
    }

    static function get_formatted_attractive_trends_in_past($elapsed)
    {
        $html_data .= '<div id ="cssmenu"><ul>';
        $post_id_array = self::get_postid_date_trend("data");
        $filtered_post_id_array = array();
        $posts_to_trends = array();
        // $html_data = "<ul><b>posts made in past ".$elapsed." days</b><br/>";
        $date1 = new DateTime();
        // now only select certain number of posts before today's date depending on number of months
        foreach ($post_id_array as $key => $value)
        {
            // $key is the post id
            // $post_id_array[$key]["date"] is the date
            // $post_id_array[$key]["tags"] are the tags
            $date2 = $post_id_array[$key]["date"];
            if(self::get_days_diff($date1, $date2) <$elapsed)
                if (array_key_exists('tags', $post_id_array[$key]))
                {
                    $filtered_post_id_array[$key] = $post_id_array[$key];
                }
        }

        $trend_list = array();
        $trend_dict = array();
        foreach ($filtered_post_id_array as $key => $value)
        {

            //$html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
            $trends = $filtered_post_id_array[$key]["tags"];
            $tag_list = explode(",", $trends);
            foreach ($tag_list as &$value)
            {
                $value = trim($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if(strlen($value)<=0)
                    continue;
                //$html_data .= "<li>".$value."<-->".strlen($value)."</li>";
                $trend_dict[$value] = 0;
                $posts_to_trends[$value] = array();
            }

        }

        foreach ($filtered_post_id_array as $key => $value)
        {
            // for each post written within specified time
            // $html_data .= "<li>".$key."<-->".$post_id_array[$key]["date"]."</li>";
            $trends = $filtered_post_id_array[$key]["tags"];
            $tag_list = explode(",", $trends);
            foreach ($tag_list as &$value)
            {
                $value = trim($value);
                $value = ltrim($value);
                $value = rtrim($value);
                if(strlen($value)<=0)
                    continue;
                // $html_data .= "<li>".$value."</li>";
                $trend_dict[$value] += 1;
                $local_array_val = $posts_to_trends[$value];

                if(in_array($key, $local_array_val))
                {

                }
                else
                    array_push($local_array_val, $key);
                $posts_to_trends[$value] = $local_array_val;
            }
        }
        // "<li>$i. ".$value.'<img src = "'.$arrow_up_url.'" height = "12" width = "12"></li>';
        
        $arrow_up_url = plugins_url( '/images/up.jpg' , dirname(__FILE__) );
        $arrow_down_url = plugins_url( '/images/down.jpg' , dirname(__FILE__) );
        
        $html_data .= '<li>Trending in last '.$elapsed.'days</li>';

        foreach ($trend_dict as $key => $value)
        {
            if($value >2)
            {
                $html_data .= '<li>';
                $html_data .= '<span>'.$key.'&nbsp;&nbsp('.$value.')&nbsp;&nbsp;<img src = "'.$arrow_up_url.'" height = "12" width = "12"></span><ul>';
            
                foreach ($posts_to_trends as $ltrend_name => $list_of_posts_array)
                {
                    $lc_array = $posts_to_trends[$ltrend_name];
                    if($key == $ltrend_name)
                    {
                        foreach ($lc_array as &$each_post_id)
                        {
                            $html_data .= '<li><a href="http://localhost/wordpress/?p='.$each_post_id.'"\>'.$each_post_id.'</a></li>';
                        }
                    }
                }
                $html_data .= '</ul></li>';
            }
                
            else
            {
                $html_data .= '<li>';
                $html_data .= '<span>'.$key.'&nbsp;&nbsp('.$value.')&nbsp;&nbsp;<img src = "'.$arrow_down_url.'" height = "12" width = "12"></span><ul>';
            
                foreach ($posts_to_trends as $ltrend_name => $list_of_posts_array)
                {
                    $lc_array = $posts_to_trends[$ltrend_name];
                    if($key == $ltrend_name)
                    {
                        foreach ($lc_array as &$each_post_id)
                        {
                            $html_data .= '<li><a href="http://localhost/wordpress/?p='.$each_post_id.'"\>'.$each_post_id.'</a></li>';
                        }
                    }
                }
                $html_data .= '</ul></li>';
            }
                
        }
        $html_data .= '</ul></div>';
        
        return $html_data;
    }

    static function get_random_html_colorpicker()
    {
        // wp_enqueue_script('jquery_color_picker_1','https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js');
        // wp_enqueue_script('jquery_color_picker_2','https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.js');
        // wp_enqueue_style('jquery_color_picker_css1','http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/ui-lightness/jquery-ui.css');
        // wp_enqueue_style('fashion_trend_tracker-color-picker1', plugins_url( 'css/jquery.colorpicker.css', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js1', plugins_url( 'js/jquery.colorpicker.js', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js2', plugins_url( 'js/i18n/jquery.ui.colorpicker-nl.js', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js3', plugins_url( 'js/swatches/jquery.ui.colorpicker-pantone.js', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js4', plugins_url( 'js/parts/jquery.ui.colorpicker-rgbslider.js', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js5', plugins_url( 'js/parts/jquery.ui.colorpicker-memory.js', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js6', plugins_url( 'js/parsers/jquery.ui.colorpicker-cmyk-parser.js', dirname(__FILE__)));
        // wp_enqueue_script( 'colorpicker_js7', plugins_url( 'js/parsers/jquery.ui.colorpicker-cmyk-percentage-parser.js', dirname(__FILE__)));

        // $html_data = '<script>$(function() {$(".cp-revert").colorpicker({revert: true,parts: "full",showNoneButton: true});});</script>';
        // $html_data .= '<input type="text" class="cp-revert" value="" style="text-align: right"/>';

        // return $html_data;
    }



    static function get_days_diff($date1, $date2)
    {
        // returns days elapsed
        
        $d1 = $date1;
        $d2 = new DateTime($date2);
        
        $interval = $d1->diff($d2);
        $elapsed_years = $interval->y;
        $elapsed_months = $interval->m;
        $elapsed_days = $interval->d;
        $offset = 0;
        if($elapsed_years > 0)
            $offset += $elapsed_years*365;
        if($elapsed_months > 0)
            $offset += $elapsed_months*30;
        if($elapsed_days > 0)
            $offset += $elapsed_days;
        return $offset;
    }
    
    /*------------------------------------------------------------------------------
    SYNOPSIS: a simple parsing function for basic templating.
    INPUT:
        $tpl (str): a string containing [+placeholders+]
        $hash (array): an associative array('key' => 'value');
    OUTPUT
        string; placeholders corresponding to the keys of the hash will be replaced
        with the values and the string will be returned.
    ------------------------------------------------------------------------------*/
    static function parse($tpl, $hash)
    {
        foreach ($hash as $key => $value) {
            $tpl = str_replace('[+'.$key.'+]', $value, $tpl);
        }
        return $tpl;
    }
    
    //static function trim
    
}
/*EOF*/
