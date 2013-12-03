<?php
/*
 * Plugin Name: Widget de Lista da Agenda
 */

class WidgetRlAgenda extends WP_Widget {

	function WidgetRlAgenda() {
		$widget_ops = array('classname' => __CLASS__, 'description' => 'Adiciona uma lista dos eventos');
		parent::WP_Widget('widget_agenda', 'Agenda Redelivre - Lista', $widget_ops);
	}

	function update($new_instance, $old_instance) {
		 $instance = $old_instance;
		 $instance['title'] = $new_instance['title'];
		 $instance['num_posts'] = $new_instance['num_posts'];
		 return $instance;
	}

	function form($instance) {
		$title = isset($instance['title']) ? $instance['title'] : '';
		$num_posts = isset($instance['num_posts']) ? $instance['num_posts'] : '';
		require dirname(__FILE__).DIRECTORY_SEPARATOR.'form.php';
	}

	function widget($args, $instance) {

		extract( $args, EXTR_SKIP );

		$num_posts = array_key_exists('num_posts', $instance) ? $instance['num_posts'] : 0;

		$qargs = array(
				'posts_per_page' => $num_posts,
				'post_type' => 'agenda',
				'orderby' => 'meta_value',
				'meta_key' => '_data_inicial',
				'order' => 'ASC',
				'meta_query' => array(
						array(
								'key' => '_data_final',
								'value' => date('Y-m-d'),
								'compare' => '>=',
								'type' => 'DATETIME'
						)
				)
		);

		remove_filter('pre_get_posts', array('Agenda','pre_get_posts'));

		$events_query = new WP_Query($qargs);
		$events = $events_query->posts;

		require dirname(__FILE__).DIRECTORY_SEPARATOR.'view.php';
	}
}

function registerWidgetRlAgendaLista() {
    register_widget("WidgetRlAgenda");
}

add_action('widgets_init', 'registerWidgetRlAgendaLista');
