/* --- Галерея --- */

jQuery.fn.spbrogatka = function()
{
    var spbrogatkagal = $(this);
    var count = $(this).size();

    spbrogatkagal.click(function(){
        var img = new Image();

        var w = $(window).width();

        var element = $(this);
        var thisid = spbrogatkagal.index(this);
        var src = element.attr('href');

        var prev = '<div id=\"prev\" class=\"gallerynav\" style=\"cursor: pointer; width: 50%; height: 100%; position: fixed; top: 0px; left: 0px; z-index: 3333; background: url(/public/src/images/gprev.png) 41px 41px no-repeat\"></div>';

        var background = $('<div>');
        background.addClass('closegallery');
        background.css({'z-index':'1111','position':'fixed','top':'0','left':'0','display':'block','width':'100%','height':'100%','background-color':'#000','opacity':'0.7','cursor':'pointer'});
        $('body').append(background);

        var div = $('<div>');
        div.addClass('blockgallery');
        div.css({'z-index':'4444','position':'fixed','display':'none','top':'50%','left':'50%','background-color':'#000','background-image':'url(/public/src/images/loading.gif)','background-position':'center center','background-repeat':'no-repeat','margin-top':'-120px','margin-left':'-160px','width':'320px','height':'240px'});

        var div1 = $('<div>');
        div1.css({'z-index':'4444','position':'absolute','top':'0px','left':'0px','background':'none','cursor' : 'pointer'});
        div1.attr('id','next').addClass('gallerynav');

        var div2 = $('<div>');
        div2.css({'z-index':'3333','position':'absolute','top':'0px','left':'0px','background':'none','cursor' : 'pointer'});

        div.append(div1);
        div.append(div2);


        $('body').append(div);
        div.css({'display':'block'});

        $('body').append(prev);

        var close = '<div class=\"closegallery\" id=\"closegallery\" style=\"cursor: pointer; position: fixed; top: 0px; right: 0px; z-index: 4444; width: 100px; height: 100px; background: url(/public/src/images/gcloses.png) center center no-repeat\"></div>';
        $('body').append(close);

        $('.closegallery').click(function(){
            div.remove();
            background.remove();
            $('#closegallery').remove();
            $('#prev').remove();
        });
        img.onload = function() {
            var imgwidth = img.width;
            var imgheigth = img.height;

            if( imgwidth > $(window).width() )
            {
                var w = $(window).width()-200;
                var k = imgwidth/w;
                imgwidth = imgwidth/k;
                imgheigth = imgheigth/k;
            }
            if( imgheigth > $(window).height() )
            {
                var h = $(window).height()-50;
                var k = imgheigth/h;
                imgwidth = imgwidth/k;
                imgheigth = imgheigth/k;
            }
            var top = imgheigth/2-imgheigth;
            var left = imgwidth/2-imgwidth;

            var margintop = top+'px';
            var marginleft = left+'px';
            var width = imgwidth+'px';
            var height = imgheigth+'px';
            if(element.attr('title')) var title = '<div class="gal_text">'+element.attr('title')+'</div>';
            else var title = '';

            div1.css({'width':width,'height':height});
            div2.css({'width':width,'height':height});
            div.css({'width':width,'height':height,'margin-top':margintop,'margin-left':marginleft});
            div2.html('<img src=\"' + src + '\" alt=\"\" style=\"width:'+width+'; height:'+height+'\"/>'+title);
        }
        img.src = src;
        $('.closegallery').mouseover(function(){
            $('#closegallery').css({'background-image':'url(/public/src/images/gcloses1.png)'});
        });
        $('.closegallery').mouseout(function(){
            $('#closegallery').css({'background-image':'url(/public/src/images/gcloses.png)'});
        });
        $('#prev').mouseover(function(){
            $(this).css({'background-image':'url(/public/src/images/gprev1.png)'});
        });
        $('#prev').mouseout(function(){
            $(this).css({'background-image':'url(/public/src/images/gprev.png)'});
        });

        $('.gallerynav').click(function(){

            var img = new Image();

            if( $(this).attr('id')=='prev' )
            {
                thisid = thisid-1;
                if(thisid<0) thisid=count-1;
            }
            if( $(this).attr('id')=='next' )
            {
                thisid = thisid+1;
                if(thisid>count-1) thisid=0;
            }
            element = spbrogatkagal.eq(thisid);
            var src = element.attr('href');
            div2.html('');

            img.onload = function() {
                var imgwidth = img.width;
                var imgheigth = img.height;

                if( imgwidth > $(window).width() )
                {
                    var w = $(window).width()-200;
                    var k = imgwidth/w;
                    imgwidth = imgwidth/k;
                    imgheigth = imgheigth/k;
                }
                if( imgheigth > $(window).height() )
                {
                    var h = $(window).height()-50;
                    var k = imgheigth/h;
                    imgwidth = imgwidth/k;
                    imgheigth = imgheigth/k;
                }
                var top = imgheigth/2-imgheigth;
                var left = imgwidth/2-imgwidth;

                var margintop = top+'px';
                var marginleft = left+'px';
                var width = imgwidth+'px';
                var height = imgheigth+'px';
                if(element.attr('title')) var title = '<div class="gal_text">'+element.attr('title')+'</div>';
                else var title = '';

                div1.css({'width':width,'height':height});
                div2.css({'width':width,'height':height});
                div.css({'width':width,'height':height,'margin-top':margintop,'margin-left':marginleft});
                div2.html('<img src=\"' + src + '\" alt=\"\" style=\"width:'+width+'; height:'+height+'\"/>'+title);
            }
            img.src = src;
        });

        return false;
    });
};

$(document).ready(function(){
    $('[rel=gallery]').spbrogatka();
    $('[data-rel=gallery]').spbrogatka();
    $('[rel=gal]').spbrogatka();
    $('.gallery').spbrogatka();
});
/* --- // --- */