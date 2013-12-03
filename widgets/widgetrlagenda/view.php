<?php

echo $before_widget;
			
echo $before_title;
echo empty( $instance['title'] ) ? _e( 'Agenda' ) : $instance['title'];
echo $after_title;

foreach ($events as $event):
		$data_inicial = get_post_meta($event->ID, '_data_inicial', true);
		if ($data_inicial)
				$data_inicial = mysql2date(get_option('date_format'), $data_inicial, true);

		$data_final = get_post_meta($event->ID, '_data_final', true);
		if ($data_final)
				$data_final = mysql2date(get_option('date_format'), $data_final, true);
		?>
		<p>
				<span class="date">
						<?php echo $data_inicial; ?> 
						<?php if ($data_inicial != $data_final): ?>
								a <?php echo $data_final; ?>
						<?php endif; ?>
				</span><br/>
				<a href="<?php echo get_permalink($event->ID); ?>" title="<?php echo esc_attr($event->post_title); ?>"><?php echo $event->post_title; ?></a>
		</p>
		<?php
endforeach;
?>
<p class="textright"><a href="<?php echo get_post_type_archive_link('agenda') ?>" class="all">veja o calendário completo</a></p>

<?php
echo $after_widget;

?>
