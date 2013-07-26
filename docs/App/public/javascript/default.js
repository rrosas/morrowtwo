$(function(){
	$('.function-wrapper').click(function(){
		$('.well', this).toggle();
	}).find('.well').hide();
});