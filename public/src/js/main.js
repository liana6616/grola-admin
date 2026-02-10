"use strict";

// --- Замена target='_blank' на rel='external' --- //

function externalLinks() {
  if (!document.getElementsByTagName) return;
  var anchors = document.getElementsByTagName("a");
  for (var i = 0; i < anchors.length; i++) {
    if (anchors[i].getAttribute("href") && anchors[i].getAttribute("rel") == "external") {
      anchors[i].target = "_blank";
    }
  }
}
window.onload = externalLinks;

// --- // --- //

function isValidEmailAddress(emailAddress) {
  var pattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,63}$/;
  return pattern.test(emailAddress);
}

// Плавный переход к якроярм
/*
const anchors = document.querySelectorAll('a[href*="#"]')
for (let anchor of anchors) {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();

    const blockID = anchor.getAttribute('href').substr(1);

    let m = $('body').attr('data-m');
    if(m != 1) {
        top.location.href='/#'+blockID;
    }
    else {
        document.getElementById(blockID).scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        })
    }    
  })
}
*/
// --- // --- //

$.fn.size = function () {
  return this.length;
};
$.fn.hasAttr = function (name) {
  return this.attr(name) !== undefined;
};
!function (t, e) {
  function n(t, e, n) {
    var r = t.children(),
      o = !1;
    t.empty();
    for (var i = 0, d = r.length; d > i; i++) {
      var l = r.eq(i);
      if (t.append(l), n && t.append(n), a(t, e)) {
        l.remove(), o = !0;
        break;
      }
      n && n.detach();
    }
    return o;
  }
  function r(e, n, i, d, l) {
    var s = !1,
      c = "table, thead, tbody, tfoot, tr, col, colgroup, object, embed, param, ol, ul, dl, blockquote, select, optgroup, option, textarea, script, style",
      u = "script, .dotdotdot-keep";
    return e.contents().detach().each(function () {
      var f = this,
        h = t(f);
      if ("undefined" == typeof f || 3 == f.nodeType && 0 == t.trim(f.data).length) return !0;
      if (h.is(u)) e.append(h);else {
        if (s) return !0;
        e.append(h), l && e[e.is(c) ? "after" : "append"](l), a(i, d) && (s = 3 == f.nodeType ? o(h, n, i, d, l) : r(h, n, i, d, l), s || (h.detach(), s = !0)), s || l && l.detach();
      }
    }), s;
  }
  function o(e, n, r, o, d) {
    var c = e[0];
    if (!c) return !1;
    var f = s(c),
      h = -1 !== f.indexOf(" ") ? " " : "?",
      p = "letter" == o.wrap ? "" : h,
      g = f.split(p),
      v = -1,
      w = -1,
      b = 0,
      y = g.length - 1;
    for (o.fallbackToLetter && 0 == b && 0 == y && (p = "", g = f.split(p), y = g.length - 1); y >= b && (0 != b || 0 != y);) {
      var m = Math.floor((b + y) / 2);
      if (m == w) break;
      w = m, l(c, g.slice(0, w + 1).join(p) + o.ellipsis), a(r, o) ? (y = w, o.fallbackToLetter && 0 == b && 0 == y && (p = "", g = g[0].split(p), v = -1, w = -1, b = 0, y = g.length - 1)) : (v = w, b = w);
    }
    if (-1 == v || 1 == g.length && 0 == g[0].length) {
      var x = e.parent();
      e.detach();
      var T = d && d.closest(x).length ? d.length : 0;
      x.contents().length > T ? c = u(x.contents().eq(-1 - T), n) : (c = u(x, n, !0), T || x.detach()), c && (f = i(s(c), o), l(c, f), T && d && t(c).parent().append(d));
    } else f = i(g.slice(0, v + 1).join(p), o), l(c, f);
    return !0;
  }
  function a(t, e) {
    return t.innerHeight() > e.maxHeight;
  }
  function i(e, n) {
    for (; t.inArray(e.slice(-1), n.lastCharacter.remove) > -1;) e = e.slice(0, -1);
    return t.inArray(e.slice(-1), n.lastCharacter.noEllipsis) < 0 && (e += n.ellipsis), e;
  }
  function d(t) {
    return {
      width: t.innerWidth(),
      height: t.innerHeight()
    };
  }
  function l(t, e) {
    t.innerText ? t.innerText = e : t.nodeValue ? t.nodeValue = e : t.textContent && (t.textContent = e);
  }
  function s(t) {
    return t.innerText ? t.innerText : t.nodeValue ? t.nodeValue : t.textContent ? t.textContent : "";
  }
  function c(t) {
    do t = t.previousSibling; while (t && 1 !== t.nodeType && 3 !== t.nodeType);
    return t;
  }
  function u(e, n, r) {
    var o,
      a = e && e[0];
    if (a) {
      if (!r) {
        if (3 === a.nodeType) return a;
        if (t.trim(e.text())) return u(e.contents().last(), n);
      }
      for (o = c(a); !o;) {
        if (e = e.parent(), e.is(n) || !e.length) return !1;
        o = c(e[0]);
      }
      if (o) return u(t(o), n);
    }
    return !1;
  }
  function f(e, n) {
    return e ? "string" == typeof e ? (e = t(e, n), e.length ? e : !1) : e.jquery ? e : !1 : !1;
  }
  function h(t) {
    for (var e = t.innerHeight(), n = ["paddingTop", "paddingBottom"], r = 0, o = n.length; o > r; r++) {
      var a = parseInt(t.css(n[r]), 10);
      isNaN(a) && (a = 0), e -= a;
    }
    return e;
  }
  if (!t.fn.dotdotdot) {
    t.fn.dotdotdot = function (e) {
      if (0 == this.length) return t.fn.dotdotdot.debug('No element found for "' + this.selector + '".'), this;
      if (this.length > 1) return this.each(function () {
        t(this).dotdotdot(e);
      });
      var o = this;
      o.data("dotdotdot") && o.trigger("destroy.dot"), o.data("dotdotdot-style", o.attr("style") || ""), o.css("word-wrap", "break-word"), "nowrap" === o.css("white-space") && o.css("white-space", "normal"), o.bind_events = function () {
        return o.bind("update.dot", function (e, d) {
          e.preventDefault(), e.stopPropagation(), l.maxHeight = "number" == typeof l.height ? l.height : h(o), l.maxHeight += l.tolerance, "undefined" != typeof d && (("string" == typeof d || d instanceof HTMLElement) && (d = t("<div />").append(d).contents()), d instanceof t && (i = d)), g = o.wrapInner('<div class="dotdotdot" />').children(), g.contents().detach().end().append(i.clone(!0)).find("br").replaceWith("  <br />  ").end().css({
            height: "auto",
            width: "auto",
            border: "none",
            padding: 0,
            margin: 0
          });
          var c = !1,
            u = !1;
          return s.afterElement && (c = s.afterElement.clone(!0), c.show(), s.afterElement.detach()), a(g, l) && (u = "children" == l.wrap ? n(g, l, c) : r(g, o, g, l, c)), g.replaceWith(g.contents()), g = null, t.isFunction(l.callback) && l.callback.call(o[0], u, i), s.isTruncated = u, u;
        }).bind("isTruncated.dot", function (t, e) {
          return t.preventDefault(), t.stopPropagation(), "function" == typeof e && e.call(o[0], s.isTruncated), s.isTruncated;
        }).bind("originalContent.dot", function (t, e) {
          return t.preventDefault(), t.stopPropagation(), "function" == typeof e && e.call(o[0], i), i;
        }).bind("destroy.dot", function (t) {
          t.preventDefault(), t.stopPropagation(), o.unwatch().unbind_events().contents().detach().end().append(i).attr("style", o.data("dotdotdot-style") || "").data("dotdotdot", !1);
        }), o;
      }, o.unbind_events = function () {
        return o.unbind(".dot"), o;
      }, o.watch = function () {
        if (o.unwatch(), "window" == l.watch) {
          var e = t(window),
            n = e.width(),
            r = e.height();
          e.bind("resize.dot" + s.dotId, function () {
            n == e.width() && r == e.height() && l.windowResizeFix || (n = e.width(), r = e.height(), u && clearInterval(u), u = setTimeout(function () {
              o.trigger("update.dot");
            }, 100));
          });
        } else c = d(o), u = setInterval(function () {
          if (o.is(":visible")) {
            var t = d(o);
            (c.width != t.width || c.height != t.height) && (o.trigger("update.dot"), c = t);
          }
        }, 500);
        return o;
      }, o.unwatch = function () {
        return t(window).unbind("resize.dot" + s.dotId), u && clearInterval(u), o;
      };
      var i = o.contents(),
        l = t.extend(!0, {}, t.fn.dotdotdot.defaults, e),
        s = {},
        c = {},
        u = null,
        g = null;
      return l.lastCharacter.remove instanceof Array || (l.lastCharacter.remove = t.fn.dotdotdot.defaultArrays.lastCharacter.remove), l.lastCharacter.noEllipsis instanceof Array || (l.lastCharacter.noEllipsis = t.fn.dotdotdot.defaultArrays.lastCharacter.noEllipsis), s.afterElement = f(l.after, o), s.isTruncated = !1, s.dotId = p++, o.data("dotdotdot", !0).bind_events().trigger("update.dot"), l.watch && o.watch(), o;
    }, t.fn.dotdotdot.defaults = {
      ellipsis: "... ",
      wrap: "word",
      fallbackToLetter: !0,
      lastCharacter: {},
      tolerance: 0,
      callback: null,
      after: null,
      height: null,
      watch: !1,
      windowResizeFix: !0
    }, t.fn.dotdotdot.defaultArrays = {
      lastCharacter: {
        remove: [" ", "?", ",", ";", ".", "!", "?"],
        noEllipsis: []
      }
    }, t.fn.dotdotdot.debug = function () {};
    var p = 1,
      g = t.fn.html;
    t.fn.html = function (n) {
      return n != e && !t.isFunction(n) && this.data("dotdotdot") ? this.trigger("update", [n]) : g.apply(this, arguments);
    };
    var v = t.fn.text;
    t.fn.text = function (n) {
      return n != e && !t.isFunction(n) && this.data("dotdotdot") ? (n = t("<div />").text(n).html(), this.trigger("update", [n])) : v.apply(this, arguments);
    };
  }
}(jQuery);
function dots() {
  if (jQuery('.dots').length > 0) {
    jQuery('.dots').dotdotdot({});
  }
}
$(document).ready(function () {
  dots();
});
"use strict";

/* --- Галерея --- */

jQuery.fn.spbrogatka = function () {
  var spbrogatkagal = $(this);
  var count = $(this).size();
  spbrogatkagal.click(function () {
    var img = new Image();
    var w = $(window).width();
    var element = $(this);
    var thisid = spbrogatkagal.index(this);
    var src = element.attr('href');
    var prev = '<div id=\"prev\" class=\"gallerynav\" style=\"cursor: pointer; width: 50%; height: 100%; position: fixed; top: 0px; left: 0px; z-index: 3333; background: url(/public/src/images/gprev.png) 41px 41px no-repeat\"></div>';
    var background = $('<div>');
    background.addClass('closegallery');
    background.css({
      'z-index': '1111',
      'position': 'fixed',
      'top': '0',
      'left': '0',
      'display': 'block',
      'width': '100%',
      'height': '100%',
      'background-color': '#000',
      'opacity': '0.7',
      'cursor': 'pointer'
    });
    $('body').append(background);
    var div = $('<div>');
    div.addClass('blockgallery');
    div.css({
      'z-index': '4444',
      'position': 'fixed',
      'display': 'none',
      'top': '50%',
      'left': '50%',
      'background-color': '#000',
      'background-image': 'url(/public/src/images/loading.gif)',
      'background-position': 'center center',
      'background-repeat': 'no-repeat',
      'margin-top': '-120px',
      'margin-left': '-160px',
      'width': '320px',
      'height': '240px'
    });
    var div1 = $('<div>');
    div1.css({
      'z-index': '4444',
      'position': 'absolute',
      'top': '0px',
      'left': '0px',
      'background': 'none',
      'cursor': 'pointer'
    });
    div1.attr('id', 'next').addClass('gallerynav');
    var div2 = $('<div>');
    div2.css({
      'z-index': '3333',
      'position': 'absolute',
      'top': '0px',
      'left': '0px',
      'background': 'none',
      'cursor': 'pointer'
    });
    div.append(div1);
    div.append(div2);
    $('body').append(div);
    div.css({
      'display': 'block'
    });
    $('body').append(prev);
    var close = '<div class=\"closegallery\" id=\"closegallery\" style=\"cursor: pointer; position: fixed; top: 0px; right: 0px; z-index: 4444; width: 100px; height: 100px; background: url(/public/src/images/gcloses.png) center center no-repeat\"></div>';
    $('body').append(close);
    $('.closegallery').click(function () {
      div.remove();
      background.remove();
      $('#closegallery').remove();
      $('#prev').remove();
    });
    img.onload = function () {
      var imgwidth = img.width;
      var imgheigth = img.height;
      if (imgwidth > $(window).width()) {
        var w = $(window).width() - 200;
        var k = imgwidth / w;
        imgwidth = imgwidth / k;
        imgheigth = imgheigth / k;
      }
      if (imgheigth > $(window).height()) {
        var h = $(window).height() - 50;
        var k = imgheigth / h;
        imgwidth = imgwidth / k;
        imgheigth = imgheigth / k;
      }
      var top = imgheigth / 2 - imgheigth;
      var left = imgwidth / 2 - imgwidth;
      var margintop = top + 'px';
      var marginleft = left + 'px';
      var width = imgwidth + 'px';
      var height = imgheigth + 'px';
      if (element.attr('title')) var title = '<div class="gal_text">' + element.attr('title') + '</div>';else var title = '';
      div1.css({
        'width': width,
        'height': height
      });
      div2.css({
        'width': width,
        'height': height
      });
      div.css({
        'width': width,
        'height': height,
        'margin-top': margintop,
        'margin-left': marginleft
      });
      div2.html('<img src=\"' + src + '\" alt=\"\" style=\"width:' + width + '; height:' + height + '\"/>' + title);
    };
    img.src = src;
    $('.closegallery').mouseover(function () {
      $('#closegallery').css({
        'background-image': 'url(/public/src/images/gcloses1.png)'
      });
    });
    $('.closegallery').mouseout(function () {
      $('#closegallery').css({
        'background-image': 'url(/public/src/images/gcloses.png)'
      });
    });
    $('#prev').mouseover(function () {
      $(this).css({
        'background-image': 'url(/public/src/images/gprev1.png)'
      });
    });
    $('#prev').mouseout(function () {
      $(this).css({
        'background-image': 'url(/public/src/images/gprev.png)'
      });
    });
    $('.gallerynav').click(function () {
      var img = new Image();
      if ($(this).attr('id') == 'prev') {
        thisid = thisid - 1;
        if (thisid < 0) thisid = count - 1;
      }
      if ($(this).attr('id') == 'next') {
        thisid = thisid + 1;
        if (thisid > count - 1) thisid = 0;
      }
      element = spbrogatkagal.eq(thisid);
      var src = element.attr('href');
      div2.html('');
      img.onload = function () {
        var imgwidth = img.width;
        var imgheigth = img.height;
        if (imgwidth > $(window).width()) {
          var w = $(window).width() - 200;
          var k = imgwidth / w;
          imgwidth = imgwidth / k;
          imgheigth = imgheigth / k;
        }
        if (imgheigth > $(window).height()) {
          var h = $(window).height() - 50;
          var k = imgheigth / h;
          imgwidth = imgwidth / k;
          imgheigth = imgheigth / k;
        }
        var top = imgheigth / 2 - imgheigth;
        var left = imgwidth / 2 - imgwidth;
        var margintop = top + 'px';
        var marginleft = left + 'px';
        var width = imgwidth + 'px';
        var height = imgheigth + 'px';
        if (element.attr('title')) var title = '<div class="gal_text">' + element.attr('title') + '</div>';else var title = '';
        div1.css({
          'width': width,
          'height': height
        });
        div2.css({
          'width': width,
          'height': height
        });
        div.css({
          'width': width,
          'height': height,
          'margin-top': margintop,
          'margin-left': marginleft
        });
        div2.html('<img src=\"' + src + '\" alt=\"\" style=\"width:' + width + '; height:' + height + '\"/>' + title);
      };
      img.src = src;
    });
    return false;
  });
};
$(document).ready(function () {
  $('[rel=gallery]').spbrogatka();
  $('[data-rel=gallery]').spbrogatka();
  $('[rel=gal]').spbrogatka();
  $('.gallery').spbrogatka();
});
/* --- // --- */
"use strict";
"use strict";

$('body').on('click', '.close', function () {
  var mod = $(this).closest('.mod');
  var i = mod.data('i');
  $('.black' + i + ',.mod' + i).remove();
  $('body').removeClass('hiddens');
});
$('body').on('click', '.no', function () {
  var modal = $(this).closest('.modbox');
  var i = modal.data('i');
  $('.black' + i + ',.mod' + i).remove();
  $('body').removeClass('hiddens');
});
$('body').on('click', '.dialog', function () {
  $('body').addClass('hiddens');
  var i = $('.black').length + 1;
  var black = $('<div>');
  black.addClass('black').addClass('black' + i).css({
    'z-index': i + 100
  }).attr('data-i', i);
  var mod = $('<div>');
  mod.addClass('mod').addClass('mod' + i).css({
    'z-index': i + 101
  }).attr('data-i', i);
  var modbox = $('<div>');
  modbox.addClass('modbox').addClass('modbox' + i).attr('data-i', i);
  var close = $('<div>');
  close.addClass('close').attr('data-i', i).addClass('close' + i);
  mod.append(close);
  mod.append(modbox);
  $('body').append(black);
  $('body').append(mod);
  mod.addClass('hidden');
  modbox.html('<img src=\"/priv/src/images/loading.gif\" alt=\"\" />');
  close.hide();
  win_auto(i);
  var fn = $(this).data('fn');
  var t = $(this).data('t');
  var tp = $(this).data('tp');
  var p = $(this).data('p');
  var ii = $(this).data('i');
  $.ajax({
    url: '/user/ajax',
    type: 'POST',
    data: {
      'action': 'dialogLoad',
      'class': fn,
      'type': t,
      'tp': tp,
      'p': p,
      'i': ii
    },
    success: function success(ht) {
      modbox.html(ht);
      close.show();
      setTimeout(function () {
        mod.removeClass('hidden');
        win_auto(i);
      }, 100);
      maskPhone();
      dots();
      externalLinks();
    }
  });
  return false;
});
function new_modal() {
  var i = $('.black').length + 1;
  var black = $('<div>');
  black.addClass('black').addClass('black' + i).css({
    'z-index': i + 100
  }).attr('data-i', i);
  var mod = $('<div>');
  mod.addClass('mod').addClass('mod' + i).css({
    'z-index': i + 101
  }).attr('data-i', i);
  var modbox = $('<div>');
  modbox.addClass('modbox').addClass('modbox' + i).attr('data-i', i);
  var close = $('<div>');
  close.addClass('close').attr('data-i', i).addClass('close' + i);
  mod.append(close);
  mod.append(modbox);
  $('body').append(black);
  $('body').append(mod);
  return i;
}
function win_auto(i) {
  var mod = $('.mod' + i);
  var modbox = $('.modbox' + i);
  var h = mod.height();
  if (h > $(window).height() - 50) {
    var w = mod.width();
    var w1 = w / 2 - w;
    mod.css({
      'margin-left': w1
    });
    var w2 = w + 25;
    var h1 = $(window).height() - 50;
    mod.css({
      'height': h1 + 'px',
      'margin-top': '25px',
      'top': '0px'
    });
    modbox.css({
      'height': h1 + 'px',
      'overflow-y': 'scroll'
    });
  } else {
    mod.css({
      'height': 'auto',
      'top': '50%'
    });
    modbox.css({
      'height': 'auto',
      'overflow-y': 'inherit'
    });
    var _w = mod.width();
    var _w2 = _w / 2 - _w;
    mod.css({
      'margin-left': _w2
    });
    var _h = mod.height();
    var _h2 = _h / 2 - _h;
    mod.css({
      'margin-top': _h2
    });
  }
}
$(window).resize(function () {
  if ($('.modbox').length) {
    $('.modbox').each(function () {
      var i = $(this).data('i');
      win_auto(i);
    });
  }
});