<?php
/**
 * Plugin Name: Rede Livre Calendário CPT
 * Description: Modificação do código da função get_calendar do WordPress para suportar 
 * Version: 0.1
 * Author: João Paulo Apolinário Passos (contribuição de IV Draganov e Pippin Williamson)
 * Author URI: http://apolinariopassos.com.br/dev
 * License: MIT
 */

/**
 * CPT Calendar Widget Class
 */
class rl_cpt_calendar extends WP_Widget {


    /** constructor */
    function rl_cpt_calendar() {
        parent::WP_Widget(false, $name = 'Calendario Custom Post Types');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {	
        extract( $args );
        $title 			= apply_filters('widget_title', $instance['title']);
		    $posttype_enabled = $instance['posttype_enabled'];
        $posttype 		= $instance['posttype'];
        ?>
			<?php echo $before_widget; ?>
				<?php if ( $title )
					echo $before_title . $title . $after_title; ?>
					<div class="widget_calendar">
						<div id="calendar_wrap">
							<?php if($posttype_enabled == true) {
								ucc_get_calendar(array($posttype));

                function namespace_add_custom_types( $query ) {
                  if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
                    $query->set( 'post_type', array(
                      'post', $posttype
                    ));
                    return $query;
                  }
                }
                add_filter( 'pre_get_posts', 'namespace_add_custom_types' );
                
                } else {
								ucc_get_calendar();
							} ?>
						</div>
					</div>
			<?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {		
		  $instance = $old_instance;
		  $instance['title'] = strip_tags($new_instance['title']);
		  $instance['posttype_enabled'] = $new_instance['posttype_enabled'];
		  $instance['posttype'] = $new_instance['posttype'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	

		$posttypes = get_post_types('', 'objects');
	  
    if(isset($instance['title']))
      $title = esc_attr($instance['title']);
    else
      $title = '';

		if(isset($instance['posttype_enabled']))
      $posttype_enabled	= esc_attr($instance['posttype_enabled']);
    else 
      $posttype_enabled = 0;

		if(isset($instance['posttype']))
      $posttype	= esc_attr($instance['posttype']);
    
    ?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titulo:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
		<p>
          <input id="<?php echo $this->get_field_id('posttype_enabled'); ?>" name="<?php echo $this->get_field_name('posttype_enabled'); ?>" type="checkbox" value="1" <?php checked( '1', $posttype_enabled ); ?>/>
          <label for="<?php echo $this->get_field_id('posttype_enabled'); ?>"><?php _e('Mostrar apenas um post type?'); ?></label> 
        </p>
		<p>	
			<label for="<?php echo $this->get_field_id('posttype'); ?>"><?php _e('Escolha o Post Type para mostrar'); ?></label> 
			<select name="<?php echo $this->get_field_name('posttype'); ?>" id="<?php echo $this->get_field_id('posttype'); ?>" class="widefat">
				<?php
				foreach ($posttypes as $option) {
					echo '<option value="' . $option->name . '" id="' . $option->name . '"', $posttype == $option->name ? ' selected="selected"' : '', '>', $option->name, '</option>';
				}
				?>
			</select>		
		</p>
        <?php 
    }


} // 
// register CPT Calendar widget
add_action('widgets_init', create_function('', 'return register_widget("rl_cpt_calendar");'));



/* ucc_get_calendar() :: Extends get_calendar() by including custom post types.
 * Derived from get_calendar() code in /wp-includes/general-template.php.
 */

add_action( 'init', 'create_post_type' );
function create_post_type() {
  register_post_type( 'acme_product',
    array(
      'labels' => array(
        'name' => __( 'Products' ),
        'singular_name' => __( 'Product' )
      ),
    'public' => true,
    'has_archive' => true,
    )
  );
}

function ajax_calendar_handler() {
  wp_enqueue_script(
    'ajax-calendar-handler',
    plugins_url() . '/calendario-cpt/js/ajax-calendar.js',
    array( 'jquery' )
  );
  $template_url = array( 'plugin_url' => plugins_url() . '/calendario-cpt' );
  wp_localize_script( 'ajax-calendar-handler', 'dados', $template_url );
}
add_action( 'wp_enqueue_scripts', 'ajax_calendar_handler' );

function ucc_get_calendar( $post_types = '' , $initial = true , $echo = true ) {
  global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;
  if ( empty( $post_types ) || !is_array( $post_types ) ) {
    $args = array(
      'public' => true ,
      '_builtin' => false
    );
    $output = 'names';
    $operator = 'and';

    $post_types = get_post_types( $args , $output , $operator );
    $post_types = array_merge( $post_types , array( 'post' ) );
  } else {
    /* Trust but verify. */
    $my_post_types = array();
    foreach ( $post_types as $post_type ) {
      if ( post_type_exists( $post_type ) )
        $my_post_types[] = $post_type;
    }
    $post_types = $my_post_types;
  }
  $post_types_key = implode( '' , $post_types );
  $post_types = "'" . implode( "' , '" , $post_types ) . "'";

  $cache = array();
  $key = md5( $m . $monthnum . $year . $post_types_key );
  if ( $cache = wp_cache_get( 'get_calendar' , 'calendar' ) ) {
    if ( is_array( $cache ) && isset( $cache[$key] ) ) {
      remove_filter( 'get_calendar' , 'ucc_get_calendar_filter' );
      $output = apply_filters( 'get_calendar',  $cache[$key] );
      add_filter( 'get_calendar' , 'ucc_get_calendar_filter' );
      if ( $echo ) {
        echo $output;
        return;
      } else {
        return $output;
      }
    }
  }

  if ( !is_array( $cache ) )
    $cache = array();

  // Quick check. If we have no posts at all, abort!
  if ( !$posts ) {
    $sql = "SELECT 1 as test FROM $wpdb->posts WHERE post_type IN ( $post_types ) AND post_status IN ('publish','future') LIMIT 1";
    $gotsome = $wpdb->get_var( $sql );
    if ( !$gotsome ) {
      $cache[$key] = '';
      wp_cache_set( 'get_calendar' , $cache , 'calendar' );
      return;
    }
  }

  if ( isset( $_GET['w'] ) )
    $w = '' . intval( $_GET['w'] );

  // week_begins = 0 stands for Sunday
  $week_begins = intval( get_option( 'start_of_week' ) );

  // Let's figure out when we are
  if ( !empty( $monthnum ) && !empty( $year ) ) {
    $thismonth = '' . zeroise( intval( $monthnum ) , 2 );
    $thisyear = ''.intval($year);
  } elseif ( !empty( $w ) ) {
    // We need to get the month from MySQL
    $thisyear = '' . intval( substr( $m , 0 , 4 ) );
    $d = ( ( $w - 1 ) * 7 ) + 6; //it seems MySQL's weeks disagree with PHP's
    $thismonth = $wpdb->get_var( "SELECT DATE_FORMAT( ( DATE_ADD( '${thisyear}0101' , INTERVAL $d DAY ) ) , '%m' ) " );
  } elseif ( !empty( $m ) ) {
    $thisyear = '' . intval( substr( $m , 0 , 4 ) );
    if ( strlen( $m ) < 6 )
        $thismonth = '01';
    else
        $thismonth = '' . zeroise( intval( substr( $m , 4 , 2 ) ) , 2 );
  } else {
    $thisyear = gmdate( 'Y' , current_time( 'timestamp' ) );
    $thismonth = gmdate( 'm' , current_time( 'timestamp' ) );
  }

  $unixmonth = mktime( 0 , 0 , 0 , $thismonth , 1 , $thisyear);

  // Get the next and previous month and year with at least one post
  $previous = $wpdb->get_row( "SELECT DISTINCT MONTH( post_date ) AS month , YEAR( post_date ) AS year
    FROM $wpdb->posts
    WHERE post_date < '$thisyear-$thismonth-01'
    AND post_type IN ( $post_types ) AND post_status IN ('publish','future')
      ORDER BY post_date DESC
      LIMIT 1" );
  $next = $wpdb->get_row( "SELECT DISTINCT MONTH( post_date ) AS month, YEAR( post_date ) AS year
    FROM $wpdb->posts
    WHERE post_date > '$thisyear-$thismonth-01'
    AND MONTH( post_date ) != MONTH( '$thisyear-$thismonth-01' )
    AND post_type IN ( $post_types ) AND post_status IN ('publish','future')
      ORDER  BY post_date ASC
      LIMIT 1" );

  /* translators: Calendar caption: 1: month name, 2: 4-digit year */
  $calendar_caption = _x( '%1$s %2$s' , 'calendar caption' );
  $calendar_output = '<table id="wp-calendar" summary="' . esc_attr__( 'Calendar' ) . '">
  <caption>' . sprintf( $calendar_caption , $wp_locale->get_month( $thismonth ) , date( 'Y' , $unixmonth ) ) . '</caption>
  <thead>
  <tr>';

  $myweek = array();

  for ( $wdcount = 0 ; $wdcount <= 6 ; $wdcount++ ) {
    $myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
  }

  foreach ( $myweek as $wd ) {
    $day_name = ( true == $initial ) ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
    $wd = esc_attr( $wd );
    $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
  }

  $calendar_output .= '
  </tr>
  </thead>

  <tfoot>
  <tr>';

  if ( $previous ) {    $calendar_output .= "\n\t\t" . '<td colspan="3" id="prev"><a data-post-types="'.$post_types.'" href="' . get_month_link( $previous->year , $previous->month ) . '" title="' . sprintf( __( 'View posts for %1$s %2$s' ) , $wp_locale->get_month( $previous->month ) , date( 'Y' , mktime( 0 , 0 , 0 , $previous->month , 1 , $previous->year ) ) ) . '">&laquo; ' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $previous->month ) ) . '</a></td>';
  } else {
    $calendar_output .= "\n\t\t" . '<td colspan="3" id="prev" class="pad">&nbsp;</td>';
  }

  $calendar_output .= "\n\t\t" . '<td class="pad">&nbsp;</td>';

  if ( $next ) {    $calendar_output .= "\n\t\t" . '<td colspan="3" id="next"><a data-post-types="'.$post_types.'" href="' . get_month_link( $next->year , $next->month ) . '" title="' . esc_attr( sprintf( __( 'View posts for %1$s %2$s' ) , $wp_locale->get_month( $next->month ) , date( 'Y' , mktime( 0 , 0 , 0 , $next->month , 1 , $next->year ) ) ) ) . '">' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $next->month ) ) . ' &raquo;</a></td>';
  } else {
    $calendar_output .= "\n\t\t" . '<td colspan="3" id="next" class="pad">&nbsp;</td>';
  }

  $calendar_output .= '
  </tr>
  </tfoot>

  <tbody>
  <tr>';

  // Get days with posts
  $dayswithposts = $wpdb->get_results( "SELECT DISTINCT DAYOFMONTH( post_date )
    FROM $wpdb->posts WHERE MONTH( post_date ) = '$thismonth'
    AND YEAR( post_date ) = '$thisyear'
    AND post_type IN ( $post_types ) AND post_status IN ('publish','future')", ARRAY_N );
  /* AND post_date < '" . current_time( 'mysql' ) . '\''*/
  if ( $dayswithposts ) {
    foreach ( (array) $dayswithposts as $daywith ) {
      $daywithpost[] = $daywith[0];
    }
  } else {
    $daywithpost = array();
  }

  if ( strpos( $_SERVER['HTTP_USER_AGENT'] , 'MSIE' ) !== false || stripos( $_SERVER['HTTP_USER_AGENT'] , 'camino' ) !== false || stripos( $_SERVER['HTTP_USER_AGENT'] , 'safari' ) !== false )
    $ak_title_separator = "\n";
  else
    $ak_title_separator = ', ';

  $ak_titles_for_day = array();
  $ak_post_titles = $wpdb->get_results( "SELECT ID, post_title, DAYOFMONTH( post_date ) as dom "
    . "FROM $wpdb->posts "
    . "WHERE YEAR( post_date ) = '$thisyear' "
    . "AND MONTH( post_date ) = '$thismonth' "
    //. "AND post_date < '" . current_time( 'mysql' ) . "' "
    . "AND post_type IN ( $post_types ) AND post_status IN ('publish','future')"
  );
  if ( $ak_post_titles ) {
    foreach ( (array) $ak_post_titles as $ak_post_title ) {

        $post_title = esc_attr( apply_filters( 'the_title' , $ak_post_title->post_title , $ak_post_title->ID ) );

        if ( empty( $ak_titles_for_day['day_' . $ak_post_title->dom] ) )
          $ak_titles_for_day['day_'.$ak_post_title->dom] = '';
        if ( empty( $ak_titles_for_day["$ak_post_title->dom"] ) ) // first one
          $ak_titles_for_day["$ak_post_title->dom"] = $post_title;
        else
          $ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
    }
  }

  // See how much we should pad in the beginning
  $pad = calendar_week_mod( date( 'w' , $unixmonth ) - $week_begins );
  if ( 0 != $pad )
    $calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';

  $daysinmonth = intval( date( 't' , $unixmonth ) );
  for ( $day = 1 ; $day <= $daysinmonth ; ++$day ) {
    if ( isset( $newrow ) && $newrow )
      $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
    $newrow = false;

    if ( $day == gmdate( 'j' , current_time( 'timestamp' ) ) && $thismonth == gmdate( 'm' , current_time( 'timestamp' ) ) && $thisyear == gmdate( 'Y' , current_time( 'timestamp' ) ) )
      $calendar_output .= '<td id="today">';
    else
      $calendar_output .= '<td>';

    if ( in_array( $day , $daywithpost ) ) // any posts today?
        $calendar_output .= '<a href="' . get_day_link( $thisyear , $thismonth , $day ) . "\" title=\"" . esc_attr( $ak_titles_for_day[$day] ) . "\">$day</a>";
    else
      $calendar_output .= $day;
    $calendar_output .= '</td>';

    if ( 6 == calendar_week_mod( date( 'w' , mktime( 0 , 0 , 0 , $thismonth , $day , $thisyear ) ) - $week_begins ) )
      $newrow = true;
  }

  $pad = 7 - calendar_week_mod( date( 'w' , mktime( 0 , 0 , 0 , $thismonth , $day , $thisyear ) ) - $week_begins );
  if ( $pad != 0 && $pad != 7 )
    $calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';

  $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

  $cache[$key] = $calendar_output;
  wp_cache_set( 'get_calendar' , $cache, 'calendar' );

  remove_filter( 'get_calendar' , 'ucc_get_calendar_filter' );
  $output = apply_filters( 'get_calendar',  $calendar_output );
  add_filter( 'get_calendar' , 'ucc_get_calendar_filter' );

  if ( $echo )
    echo $output;
  else
    return $output;
}

function archive_meta_query( $query ) {
    if ( $query->is_archive){
      $query->query_vars["post_status"] = array('publish', 'future');
    }
}
add_action( 'pre_get_posts', 'archive_meta_query', 1 );

function ucc_get_calendar_filter( $content ) {
  $output = ucc_get_calendar( '' , '' , false );
  return $output;
}
add_filter( 'get_calendar' , 'ucc_get_calendar_filter' , 10 , 2 );