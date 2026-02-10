$('body').on('click', '.close', function () {
	let mod = $(this).closest('.mod');
	let i = mod.data('i');
	$('.black'+i+',.mod'+i).remove();
	$('body').removeClass('hiddens');
});
$('body').on('click', '.no', function () {
	let modal = $(this).closest('.modbox');
	let i = modal.data('i');
	$('.black'+i+',.mod'+i).remove();
	$('body').removeClass('hiddens');
});
$('body').on('click', '.dialog', function () {

	$('body').addClass('hiddens');

	let i = $('.black').length + 1;
	let black = $('<div>');
	black.addClass('black').addClass('black'+i).css({'z-index' : (i+100)}).attr('data-i',i);

	let mod = $('<div>');
	mod.addClass('mod').addClass('mod'+i).css({'z-index' : (i+101)}).attr('data-i',i);

	let modbox = $('<div>');
	modbox.addClass('modbox').addClass('modbox'+i).attr('data-i',i);

	let close = $('<div>');
	close.addClass('close').attr('data-i',i).addClass('close'+i);

	mod.append(close);
	mod.append(modbox);
	$('body').append(black);
	$('body').append(mod);

	mod.addClass('hidden');

	modbox.html('<img src=\"/priv/src/images/loading.gif\" alt=\"\" />');
	close.hide();
	win_auto(i);

	let fn = $(this).data('fn');
	let t = $(this).data('t');
	let tp = $(this).data('tp');
	let p = $(this).data('p');
	let ii = $(this).data('i');

	$.ajax({
		url: '/user/ajax',
		type: 'POST',
		data: {
			  'action': 'dialogLoad'
			, 'class' : fn
			, 'type' : t
			, 'tp' : tp
			, 'p' : p
			, 'i' : ii
		},
		success: function(ht){
			modbox.html(ht);
			close.show();
			setTimeout(function(){
				mod.removeClass('hidden');
				win_auto(i);

			},100);
			maskPhone();
			dots();
			externalLinks();

		}
	});

	return false;
});

function new_modal()
{
	let i = $('.black').length + 1;
	let black = $('<div>');
	black.addClass('black').addClass('black'+i).css({'z-index' : (i+100)}).attr('data-i',i);

	let mod = $('<div>');
	mod.addClass('mod').addClass('mod'+i).css({'z-index' : (i+101)}).attr('data-i',i);

	let modbox = $('<div>');
	modbox.addClass('modbox').addClass('modbox'+i).attr('data-i',i);

	let close = $('<div>');
	close.addClass('close').attr('data-i',i).addClass('close'+i);

	mod.append(close);
	mod.append(modbox);
	$('body').append(black);
	$('body').append(mod);

	return i;
}

function win_auto(i)
{
	let mod = $('.mod'+i);
	let modbox = $('.modbox'+i);

	let h = mod.height();
	
	if(h > ($(window).height()-50))
	{
		let w = mod.width();
		let w1 = w/2-w;
		mod.css({'margin-left' : w1});
		let w2 = w + 25;
		
		let h1 = $(window).height() - 50;
		mod.css({'height' : h1+'px', 'margin-top' : '25px', 'top' : '0px'});
		modbox.css({'height' : h1+'px', 'overflow-y' : 'scroll'});
	}
	else
	{
		mod.css({'height' : 'auto','top' : '50%'});
		modbox.css({'height' : 'auto', 'overflow-y' : 'inherit'});
	
		let w = mod.width();
		let w1 = w/2-w;
		mod.css({'margin-left' : w1});
		let h = mod.height();
		let h1 = h/2-h;
		mod.css({'margin-top' : h1});
	}
}

$(window).resize(function(){
	if($('.modbox').length)
	{
		$('.modbox').each(function(){
			let i = $(this).data('i');
			win_auto(i);
		});
	}
});