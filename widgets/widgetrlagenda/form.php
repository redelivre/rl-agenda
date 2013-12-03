<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">
				<?php _e('Título'); ?><br/>
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo isset($title) ? $title : 'Agenda'; ?>"/>
		</label>
</p>
<p>
		<label for="<?php echo $this->get_field_id('post_name'); ?>">
				<?php _e('Número de eventos na lista'); ?> 
				<input class="widefat" id="<?php echo $this->get_field_id('num_posts'); ?>" name="<?php echo $this->get_field_name('num_posts'); ?>" type="text" value="<?php echo $num_posts; ?>" />
		</label>
</p>
