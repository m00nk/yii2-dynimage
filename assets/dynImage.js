/**
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * Date: 29.09.16, Time: 3:36
 */

var dynImage = {
	sizes: false,

	init: function(sizes)
	{
		dynImage.sizes = sizes;
		$('img-dyn').each(function(i, o)
		{
			o = $(o);
			var container = o.parent();

			var attributes = '';
			$.each(o[0].attributes, function()
			{
				if(this.name.indexOf('data-dyn-') != 0)
					attributes += this.name + '="' + this.value + '" ';
			});

			var biggest = 0, w = container.width();
			if(Array.isArray(dynImage.sizes))
			{
				$.each(dynImage.sizes, function(idx, val)
				{
					if(val >= w)
					{
						w = val;
						return false;
					}
					biggest = val;
				})
			}

			if(w > biggest) w = biggest;

			var url = o.attr('data-dyn-src') + '=' + w + 'x0x' + o.attr('data-dyn-quality') + '.' + o.attr('data-dyn-ext');
			var img = $('<img src="' + url + '" ' + attributes + ' />').css('opacity', 0).load(function(e){ $(e.target).animate({opacity: 1}, 400)});
			o.after(img);
			o.remove();
		});
	}
};

