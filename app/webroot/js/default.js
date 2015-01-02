function pad(n){
	return n<10 ? '0'+n : n;
}

$(document).ready(function(){
	$('table').tablesorter();
	
	$('.datepicker').datepicker()
		.on('changeDate',function(ev){
			$('.game_list').each(function(){
				$(this).addClass('hidden');
			});
			$('#' + ev.date.getFullYear() + '-' + pad(ev.date.getMonth()+1) + '-' + pad(ev.date.getDate())).removeClass('hidden');
		});
		
	$('.datepicker').click();
	
	$('.uploadform').click(function(){
		$('.teamname').eq(0).text($(this).parents('.game_selector').children('span').eq(0).text());
		$('.teamname').eq(1).text($(this).parents('.game_selector').children('span').eq(1).text());
		
		$('.fileupload').eq(0).attr({name: $(this).parents('.game_selector').children('span').eq(0).attr('class')});
		$('.fileupload').eq(1).attr({name: $(this).parents('.game_selector').children('span').eq(1).attr('class')});
		
		$('#game_id').val($(this).attr('id'));
		$('#uploadform').removeClass('hidden');
	});
	
	$('.one_pass,.two_pass').hover(
		function(){
			$(this).children('.pass,.shot').show();
		},
		function(){
			$(this).children('.pass,.shot').hide();
		}
	);
	
	$('#sc').click(function(){
		if($('input:checked').length > 0){
			$('.situation').each(function(){
				h = $(this).attr('href');
				if(h.charAt(h.length - 1) == 0){
					$(this).attr({href: h.substr(0,h.length - 1) + '1'});
				} else {
					$(this).attr({href: h + '/1'});
				}
			});
		} else {
			$('.situation').each(function(){
				h = $(this).attr('href');
				if(h.charAt(h.length - 1) == 1){
					$(this).attr({href: h.substr(0,h.length - 1) + '0'});
				} else {
					$(this).attr({href: h + '/0'});
				}
			});
		}
	});
});