	jQuery(document).ready(function($){
	
		//var pathname = window.location.pathname;	
		//$('.boardAll').get(pathname + ' .boardAll > *');
		//window.onpopstate = function(event) {
		//	if(event && event.state) {
		//		location.reload();
		//	}
		//}		
		
		var hash = window.location.hash.substr(1);
		if(hash != false && hash != 'undefined'){
			$('#thread'+hash+'').addClass('current');
		};
		var hash = window.location.hash.substr(1);
		if(hash != false && hash != 'undefined'){
			$('#thread'+hash+'').addClass('current');
		};	
		var load_data = $('#activity').attr('data');
		$('#activity').load(load_data + ' div.thread');
		$('.reply_to_this_comment').on('click', function() {
			$("#post_comment_parent").val($(this).attr('data'));
		});
		$(document).on('click','.reload',function(){
			var regbo_relurl = $(this).attr('data');
			var regbo_relid  = $(this).attr('xdata');
			$('.omitted' + regbo_relid + '').load(regbo_relurl + ' .omitted'+ regbo_relid + '');
		});
		$(document).on('click','.rb_yt',function(e){
			e.preventDefault();
			var youtube_id = $(this).attr('data');
			$(this).empty();
			$('#'+youtube_id+'').html("<iframe src=\"//www.youtube.com/embed/"+youtube_id+"?autoplay=1&amp;playlist="+youtube_id+"&amp;controls=1&amp;showinfo=0&amp;autohide=1\" width=\"100%\" height=\"315\" frameborder=\"0\" allowfullscreen></iframe>");
		});
		$(document).on('click','.quickreply',function(e){
			e.preventDefault();
			var quick_action = $(this).attr('href');
			var post_id = $(this).attr('data');
			var child_id = $(this).attr('childid');
			$('#replyto' + post_id + '').load(quick_action + ' div#reply');
			$('#replyto' + post_id + '').toggleClass('hidden');
		});
		
		$(document).on('click','.post_action a',function(e){
			e.preventDefault();
			var post_action = $(this).attr('href');
			var this_id = $(this).attr('data');
			$('#load'+this_id+'').load(post_action + ' div#post_action');
		});
		$(document).on('click','.loadme',function(){
			var regbo_id = $(this).attr('id');
			var regbo_url = $(this).attr('data');
			var regbo_target = $(this).attr('grab');
			$(this).addClass('hidden');
			$('#load' + regbo_id + '').load(regbo_url + ' div.' + regbo_target + regbo_id + '');
			$('#' + regbo_id + '.hideme').removeClass('hidden');
		});
		$(document).on('click','.hideme',function(){
			var regbo_id = $(this).attr('id');
			var regbo_url = $(this).attr('data');
			$(this).addClass('hidden');
			$('#load'+regbo_id+'').empty();
			$('#'+regbo_id+'.loadme').removeClass('hidden');
		});
		$(document).on('click','.srcme',function(){
			var regbo_id = $(this).attr('id');
			$(this).addClass('hidden');
			$('.src'+regbo_id+'').removeClass('hidden');
			$('#'+regbo_id+'.srchideme').removeClass('hidden');
		});
		$(document).on('click','.srchideme',function(){
			var regbo_id = $(this).attr('id');
			$(this).addClass('hidden');
			$('.src'+regbo_id+'').addClass('hidden');
			$('#'+regbo_id+'.srcme').removeClass('hidden');
		});				
		$(document).on('click','.imageEXPAC',function(e){
			e.preventDefault();
			$('img.image').toggleClass('hidden');
			$('.fa-plus').toggleClass('fa-minus');
			$(this).toggleClass('imageEXPAND');
		});
		$(document).on('click','.imageOP',function(e){
			e.preventDefault();
			$(this).toggleClass('imageEXPAND');
		});
		
		
		$('.regularboard_form').ajaxForm(function() { 
			var data = $('.regularboard_form').attr('data');
			$('.boardAll').load(data + ' .boardAll > *');
			history.pushState('data', '', data);
		}); 
		$('.regularboard_post').ajaxForm(function() { 
			var data  = $('.regularboard_post').attr('data');
			var xdata = $('.regularboard_post').attr('xdata'); 
			$('.' + xdata + '').load(data + ' .' + xdata + '');
		});
		
		
	});	