jQuery(document).ready(function(){
	/*jQuery(document).on("click", "#next", function(e){

		e.preventDefault();
	});*/
	jQuery(document).on("click", "#prev, #next", function(e){
		e.preventDefault();
		var data_escolhida = jQuery(this).children('a').attr('href');
		data_escolhida = data_escolhida.split("/");
		var array_length = data_escolhida.length;
		var monthnum = data_escolhida[array_length-2];
		var year = data_escolhida[array_length-3];
		var post_type = jQuery(this).children('a').data('post-types');
		jQuery.ajax({
		  url: dados.plugin_url+"/ajax-calendario.php?monthnum="+monthnum+"&year="+year+"&post_type="+post_type
		}).done(function(data) { // data what is sent back by the php page
		  console.log(data);
		  jQuery('#calendar_wrap').html(data); // display data
		});
	});
})