	jQuery(document).ready(function($){
		var hash = window.location.hash.substr(1);
		if(hash != false && hash != 'undefined'){
			$('#thread'+hash+'').addClass('current');
		};
		var hash = window.location.hash.substr(1);
		if(hash != false && hash != 'undefined'){
			$('#thread'+hash+'').addClass('current');
		};
		$(document).on('click','.reload',function(){
			var regbo_relurl = $(this).attr('data');
			$('#omitted').load(regbo_relurl + ' #omitted .thread');
		});
		$(document).on('click','.rb_yt',function(e){
			e.preventDefault();
			var youtube_id = $(this).attr('data');
			$(this).empty();
			$('#'+youtube_id+'').html("<iframe src=\"//www.youtube.com/embed/"+youtube_id+"?autoplay=1&amp;loop=1&amp;playlist="+youtube_id+"&amp;controls=0&amp;showinfo=0&amp;autohide=1\" width=\"100%\" height=\"315\" frameborder=\"0\" allowfullscreen></iframe>");
		});
		$(document).on('click','a.newtopic',function(e){
			e.preventDefault();
			var newtopic_href = $(this).attr('href');
			$(this).addClass('hidden');
			$('span.notopic').removeClass('hidden');
			$('p.newtopic').load(newtopic_href + ' div.reply');
			$('div.reply').hide();
		});
		$(document).on('click','.post_action a',function(e){
			e.preventDefault();
			var post_action = $(this).attr('href');
			var post_id = $(this).attr('data');
			$('#load' + post_id + '').load(post_action + ' div#post_action');
		});		
		$(document).on('click','span.notopic',function(e){
			e.preventDefault();
			$(this).addClass('hidden');
			$('a.newtopic').removeClass('hidden');
			$('p.newtopic').empty();
			$('div.reply').show();
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
	});	