<?php
/*
	  Plugin Name: Agenda Redelivre
    Plugin URI: http://www.ethymos.com.br
    Description:
    Author: Ethymos
    Version: 1.0
    Author URI:
    Text Domain:
    Domain Path:
*/

if(!defined('__DIR__')) {
    $iPos = strrpos(__FILE__, DIRECTORY_SEPARATOR);
    define("__DIR__", substr(__FILE__, 0, $iPos) . DIRECTORY_SEPARATOR);
}

require_once __DIR__.DIRECTORY_SEPARATOR.'rlagenda.php';
require_once __DIR__.DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR
	.'widgetrlagenda'.DIRECTORY_SEPARATOR.'widgetrlagenda.php';

RlAgenda::init();

add_action('pre_get_posts', 'campanha_agenda_query');
function campanha_agenda_query($wp_query) {
    
    if (is_admin()) return;
    
    if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] === 'agenda' && is_post_type_archive('agenda')) {
        
        
        if (!isset($wp_query->query_vars['meta_query']) || !is_array($wp_query->query_vars['meta_query'])) {
            $wp_query->query_vars['meta_query'] = array();
        }
        
        $wp_query->query_vars['orderby'] = 'meta_value';
        $wp_query->query_vars['order'] = 'ASC';
        $wp_query->query_vars['meta_key'] = '_data_inicial';
        
        if ($wp_query->query_vars['paged'] > 0 || (isset($_GET['eventos']) && $_GET['eventos'] == 'passados')) {
            array_push($wp_query->query_vars['meta_query'],
                array(
                    'key' => '_data_final',
                    'value' => date('Y-m-d'),
                    'compare' => '<=',
                    'type' => 'DATETIME'
                )
            );

        } else {
            $wp_query->query_vars['posts_per_page'] = -1;
            array_push($wp_query->query_vars['meta_query'],
                array(
                    'key' => '_data_final',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATETIME'
                )
            );
        }
    }
}


add_action('admin_init', function() {
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('ui-lightness', WPMU_PLUGIN_URL . '/css/ui-lightness/jquery-ui-1.8.21.custom.css');
});

add_action('admin_menu', function() {
    add_submenu_page('edit.php?post_type=agenda', 'Inserir no menu', 'Inserir link no menu', 'publish_posts', 'agenda_menu_page', 'agenda_menu_page');
});

function agenda_menu_page() {

    ?>
    
    <?php if( isset($_GET['action']) && $_GET['action'] == 'add_menu_item' ): ?>
            
        <?php 
        
        $menu = wp_get_nav_menu_object('main');
        $items = wp_get_nav_menu_items('main');
        $menuItem = null;
        
        if ($menu) {
            
            foreach ($items as $item) {
                if ($item->url == home_url('/agenda')) {
                    $menuItem = $item;
                }
            }
        
            if (!$menuItem) {
                wp_update_nav_menu_item($menu->term_taxonomy_id, 0, array(
                    'menu-item-title' => 'Agenda',
                    'menu-item-url' => home_url('/agenda'), 
                    'menu-item-status' => 'publish')
                );
                $msg = 'Entrada no menu inserida com sucesso!';
            } else {
                $msg = 'Já existe este item no menu!';
            }
        }
        
        ?>
        
        <div class="updated">
        <p>
        <?php echo $msg; ?>
        </p>
        </div>
   
    <?php endif; ?>
    
    <div class="wrap">
        
        
            <p>
            
            Sua agenda de eventos pode ser acessada através do endereço <a href="<?php echo site_url('agenda'); ?>"><?php echo site_url('agenda'); ?></a>.
            
            <input type="button" name="create_menu_item" value="Inserir item no menu" onClick="document.location = '<?php echo add_query_arg('action', 'add_menu_item'); ?>';" />
            
            </p>
        
        
    </div>
    <?php

}

function the_event_box() {
        
    $meta = get_metadata('post', get_the_ID());
    
    if (is_array($meta) && !empty($meta)) {
        ?>
        <div class="event-info clear">
            <h3>Informações do Evento</h3>
            <?php
            if ($meta['_data_inicial'][0]) echo '<p class="bottom"><span class="label">Data Inicial:</span> ', date('d/m/Y', strtotime($meta['_data_inicial'][0])), '</p>';
            if ($meta['_data_final'][0]) echo '<p class="bottom"><span class="label">Data Final:</span> ', date('d/m/Y', strtotime($meta['_data_final'][0])), '</p>';
            if ($meta['_horario'][0]) echo '<p class="bottom"><span class="label">Horário:</span> ', $meta['_horario'][0], '</p>';
            if ($meta['_onde'][0]) echo '<p class="bottom"><span class="label">Local:</span> ', $meta['_onde'][0], '</p>';
            if ($meta['_link'][0]) echo '<p class="bottom"><span class="label">Site:</span> ', $meta['_link'][0], '</p>';
            ?>
        </div>
        <?php
    }
}

?>
