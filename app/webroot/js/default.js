function pad(n){
	return n<10 ? '0'+n : n;
}

$(document).ready(function(){
	$('.datepicker').datepicker()
		.on('changeDate',function(ev){
			$('.game_list').each(function(){
				$(this).addClass('hidden');
			});
			$('#' + ev.date.getFullYear() + '-' + pad(ev.date.getMonth()+1) + '-' + pad(ev.date.getDate())).removeClass('hidden');
		});
});