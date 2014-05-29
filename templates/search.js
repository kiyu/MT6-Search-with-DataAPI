$(function(){
	nowsearching = false;
	$('span#search').click(function(){
		if(!nowsearching){
			nowsearching = true;
			input_word = $('#search_word').val();
			$.ajax({
				url: 'search.php',
				type: 'GET',
				dataType: 'json',
				data: {
					word: input_word
				},
				success: function(d){
					console.log(d);
				},
				error: function(e){
					console.log(e);
				},
				complete: function( jqXHR, textStatus ) {
					nowsearching = false;
				}
			});
		}
		return false;
	});
});