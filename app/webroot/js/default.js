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
	
	
});