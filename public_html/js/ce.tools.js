/* tools */

function ePrompt(o,m,e,p,a,t)
{
	if ( !$(o).is(':visible') ) { return false; }
	var id = $(o).attr("id");
	if ( typeof id == 'undefined' )
	{
		$(o).attr("id", 'ref_field_prompt_' + ($('.field_prompt').length + 1) );
	}
	id = $(o).attr("id")+'prompt';
	
	if ($('#'+id).length > 0) $('#'+id).remove();
	
	var prompt = $('<div>'), cs = (e) ? e : 'error';
	$(prompt).attr('class', 'field_prompt ' + cs)
	$(prompt).attr('id', id);
	p = (!p) ? 'bl' : p;
	a = (a) ? a : true;
	if (!t && e) t = 2;
	
	if (a) m += '<em class="'+p+'"></em>'
	
	$(prompt).html(m.replace(/\n/g,"<br />"));
	$("body").append(prompt);
	
	var od = _exdim(o), pd = _exdim(prompt);
	
	switch (p)
	{
		default:
		case "tr":
			od.left += od.width - 60;
			od.top = od.top - pd.height - ((a)?10:5);
			break;
		case "tl":
			od.top = od.top - pd.height - ((a)?10:5);
			break;
		case "tc":
			od.top += -pd.height - ((a)?10:5);
			od.left += (od.width/2) - (pd.width/2);
			break;
		case "bl":
			od.top += od.height + ((a)?15:8);
			break;
		case "br":
			od.left += od.width - 60;
			od.top += od.height + ((a)?12:8);
			break;
		case "bc":
			od.top += od.height + ((a)?12:8);
			od.left += (od.width/2) - (pd.width/2);
			break;
	}
	$(prompt).css({
		top:od.top+'px',
		left:od.left+'px',
		display:'block',
		marginTop:'-5px',
		opacity:.4
	}).animate({marginTop:'0px',opacity:1}, 200);
	$(prompt).bind('click', function() 
	{ 
		$(this).animate({opacity:0.01}, 200, function() 
		{ 
			$(this).remove(); 
		}); 
	});
	
	if (t) { setTimeout(function() { $(prompt).click(); }, t*1000); }
	$(o).bind('focus', function() { $(prompt).click(); });
	return prompt;
}
function _exdim(o)
{
	var offset = $(o).offset();
	return {
		width:$(o).width() + parseInt($(o).css('paddingLeft')) + parseInt($(o).css('paddingRight')),
		height:$(o).height() + parseInt($(o).css('paddingTop')) + parseInt($(o).css('paddingBottom')),
		top: offset.top,
		left:offset.left
	}
}
function loadToggle(e,t)
{
	$(e).css({visibility:((t)?'visible':'hidden')});
}

function ePromptAll(errors, pos, scroll, arrow, form_id)
{
	var iserr = false;
	var up = 1000;
	var to = 5;
	arrow = ( typeof arrow == 'undefined' ) ? true : arrow;
	form_id = ( form_id ) ? form_id : '';
	for (var i in errors)
	{
		var e = '#'+i;
		if ( form_id != '' )
		{
			e = '#'+form_id+' input[name="'+i+'"]';
			if ( !$(e).length ) e = '#'+form_id+' select[name="'+i+'"]';
			if ( !$(e).length ) e = '#'+form_id+' textarea[name="'+i+'"]';
		}
		if ($(e).length > 0)
		{
			to+=0.1;
			ePrompt($(e),errors[i], 'error', pos, arrow, to);
			iserr = true;
			var of = $(e).offset();
			up = (up < of.top) ? up : of.top;
		}
	}
	
	if (scroll && iserr)
	{
		$("html:not(:animated),body:not(:animated)").animate({
			scrollTop: (up-80)
		}, 1100);
	}
}

function ePromptX(o)
{
	if (!o)
	{
		$('.field_prompt').remove()
		return;
	}
	var id = $(o).attr("id") + 'prompt';
	if ($('#'+id).length > 0)
		$('#'+id).remove();
}

function ePromptTip(css)
{
	$('.'+css).live('mouseover', function()
	{
		var title = $(this).attr('title'), data = $(this).data('title'), cur_pos = $(this).data('pos');
		if ( typeof title == 'undefined' )
		{
			if ( typeof data == 'undefined' )
				return;
		}
		else
		{
			if ( title == '' ) return;
			$(this).data('title', title).removeAttr('title');
			data = title;
		}
		cur_pos = ( typeof cur_pos == 'undefined' ) ? 'bl' : cur_pos;
		ePrompt($(this), data, 'info', cur_pos, true, 10);
	}).live('mouseleave', function() {
		var data = $(this).data('title');
		if ( typeof data == 'undefined' )
			return;
		ePromptX($(this));
	});
}

function Tabs(s)
{
	$(s+' a').bind('click', function()
	{
		$(s+' a').removeClass('cur_');
		var els = $(s+' a');
		for (var i = 0; i < els.length; i++)
		{
			$('#' + $(els[i]).attr('ref') ).hide();
		}
		$(this).addClass('cur_');
		$('#' + $(this).attr('ref') ).show();
	});
	var cur = ($(s+' a.cur_').length > 0) ? $(s+' a.cur_') : $($(s+' a')[0]);
	$(cur).addClass('cur_');
	$('#' + $(cur).attr('ref') ).show();
}

function formDefAction(p)
{
	if (typeof p == 'string') p = (p.indexOf('#') == -1 && p.indexOf('.') == -1) ? '#'+p : p;
	var fields = (typeof p == 'string') ? $(p) : p;
	fields.each(function()
	{
		var f = $(this);
		var def = f.attr('def');
		if (typeof(def) == 'string')
		{
			if (f.val() == '')
			{
				f.addClass('def');
				f.val(def);
			}
			f.focus(function(){
				if (f.val() == def)
				{
					f.removeClass('def');
					f.val('');
				}
			});
			f.blur(function(){
				if (f.val() == '')
				{
					f.addClass('def');
					f.val(def);
				}
			});
		}
	});
}
function eqDef(id)
{
	var f = $('#'+id);
	var def = f.attr('def');
	if (typeof(def) != 'string')
		return false;
	if (f.val() != def)
		return false;
	return true;
}
function cpInput(f,t)
{
	if ($('#'+t).val() == '' || eqDef(t))
	{
		$('#'+t).removeClass('def');
		$('#'+t).val($('#'+f).val());
	}
}
function cpSelect(f,t)
{
	try
	{
		if (aff.get(t).selectedIndex == 0)
			aff.get(t).selectedIndex = aff.get(f).selectedIndex;
	}
	catch(ex) { }
}


function MsgPopUp(opt)
{
	var cfg = {
		message: opt.message || '',
		ovcolor: opt.ovcolor || '#fff',
		ovopacity: opt.ovopacity || .7,
		close: opt.close || null
	};
	if ($('#msg-popup').length < 1)
	{
		$('body').append('<div id="msg-popup"><a class="close"></a><div class="pad"></div></div>');
	}
	$('#msg-popup').css({
		position: 'absolute',
		display: 'none',
		top:	  '0px',
		left:	  '0px',
		zIndex:   10000
	});
	if ( opt.css && typeof opt.css == 'object' ) $('#msg-popup').css(opt.css);
	
	$('#msg-popup .pad').html(cfg.message);
	
	overlay('msg-popup-overlay', 'next_overlay');
	center('msg-popup');
	
	$('#msg-popup-overlay').css({background:cfg.ovcolor,opacity:cfg.ovopacity}).fadeIn(300);
	$('#msg-popup').fadeIn(200);
	
	$('#msg-popup a.close').unbind('click').bind('click', function() { 
		$('#msg-popup').hide(); 
		$('#msg-popup-overlay').fadeOut(200); 
		if ( typeof cfg.close == 'function' ) { cfg.close(); }
	});
}

function MsgBox(opt)
{
	var cfg = {
		title: opt.title || '',
		content: opt.content || '',
		status: opt.status || false,
		timeout: opt.timeout || 0,
		url: opt.url || '',
		modal: opt.modal || false,
		ani: opt.ani || 'up',
		anispeed: opt.anispeed || 300,
		ovcolor: opt.ovcolor || '#000000',
		ovopacity: opt.ovopacity || .4
	};
	cfg.content = cfg.content.replace(/\n/g, '<br />');
	
	onSetMessage(
		cfg.title,
		cfg.content,
		cfg.status,
		cfg.timeout,
		cfg.url,
		cfg.modal,
		cfg.ani,
		cfg.anispeed,
		cfg.ovcolor,
		cfg.ovopacity
	);
}
/* messages / alerts */
var MsgBoxTo = null;
function onSetMessage(ttl,ctl,status,to,url,modal,ani,anispeed,ovcolor,ovopacity)
{
	status = (!status) ? false : true;
	anispeed = (anispeed) ? anispeed : 0;
	ovcolor = (ovcolor) ? ovcolor : '#000000';
	ovopacity = (ovopacity) ? ovopacity : .4;
	
	to = (!to) ? 0 : to*1000; to = (to > 0) ? to + anispeed : to;
	url = (!url) ? '' : url;
	ttl = (ttl == '') ? ttl : '<h5>'+ttl+'</h5>';
	ctl = (ctl == '') ? ctl : '<p>'+ctl+'</p>';
	var html = '<div class="msg-inside">' + ttl+ctl+'</div>';
	
	if ($('#msg').length < 1)
	{
		$('body').append('<div id="msg"></div>');
		$('#msg').css({
			position: 'absolute',
			display: 'none',
			top:	  '0px',
			left:	  '0px',
			zIndex:   10000
		});
	}
	$('#msg').html(html).removeClass('error');
	if (!status) $('#msg').addClass('error')
	
	var on = (url != '') 
		? function() { $('#msg-overlay').hide(); $('#msg').hide();  document.location.href=url; } 
		: function() { $('#msg-overlay').hide(); $('#msg').hide(); return false; };
	
	if (to > 0) 
	{
		MsgBoxTo = setTimeout(function() { on(); },to);
	}
	
	overlay('msg-overlay','next_overlay');
	center('msg');
	$('#msg-overlay').css({background:ovcolor,opacity: ovopacity}).show();
	
	if (ani && ani != '')
	{
		$('#msg').css({opacity:.1}).show();
		var ew = $('#msg').width(), eh = $('#msg').height(), et = parseInt($('#msg').css('top')), el = parseInt($('#msg').css('left'));
		
		if (ani == 'fade')
		{
			$('#msg').animate({opacity:1}, anispeed);
		}
		else if (ani == 'up')
		{
			$('#msg').css({top:et+60}).animate({top:et,opacity:1}, anispeed);
		}
		else if (ani == 'bounceup')
		{
			$('#msg').css({top:et+60}).animate({top:et,opacity:1}, anispeed, 'easeOutBounce');
		}
		else if (ani == 'down')
		{
			$('#msg').css({top:et-60}).animate({top:et,opacity:1}, anispeed);
		}
		else if (ani == 'bouncedown')
		{
			$('#msg').css({top:et-60}).animate({top:et,opacity:1}, anispeed, 'easeOutBounce');
		}
		else if (ani == 'ping')
		{
			$('#msg .msg-inside').hide();
			$('#msg').css({width:ew+120, height:eh+120, left:el-60, top:et-60})
				.animate({width:ew,height:eh,opacity:1,left:el,top: et}, anispeed, 'easeOutBounce', function(){ $('#msg .msg-inside').fadeIn(100); });
		}
	}
	else
	{
		$('#msg').show();
	}
	
	$('#msg').unbind('click');
	$('#msg-overlay').unbind('click');
	
	if (modal) return;
	$('#msg').bind('click', function() { if (MsgBoxTo) { clearTimeout(MsgBoxTo); } on(); })
	$('#msg-overlay').bind('click', function() { if (MsgBoxTo) { clearTimeout(MsgBoxTo); } on(); })
}

function psConfirm(opt)
{
	var ps_close = function() { $('#psconfirm, #psconfirm-overlay').fadeOut(200); };
	var cfg = {
		message: opt.message || 'Please Confirm.',
		yes: opt.yes || 'Continue',
		no: opt.no || 'Cancel',
		yesfunc: opt.yesfunc || function() { },
		nofunc: opt.nofunc || function() { },
		ovcolor: opt.ovcolor || '#000',
		ovopacity: opt.ovopacity || .5
	}
	
	if ( $('#psconfirm').length < 1 )
	{
		$('body').append('<div class="psconfirm" id="psconfirm"><div class="pad">\
			<div class="psctl" id="psctl"></div>\
			<div class="clear"></div>\
			<a id="psctl-yes" class="btn btn-primary" onclick="return false"></a><a id="psctl-no" class="btn" onclick="return false"></a>\
			<div class="clear"></div>\
		</div></div>');
	}
	$('#psconfirm #psctl').html( cfg.message );
	$('#psctl-yes').html( cfg.yes );
	$('#psctl-no').html( cfg.no );
	
	$('#psctl-yes').unbind('click').bind('click', function() {  cfg.yesfunc(); ps_close(); });
	$('#psctl-no').unbind('click').bind('click', function() { cfg.nofunc(); ps_close(); });
	
	overlay('psconfirm-overlay', 'next_overlay');
	$('#psconfirm-overlay').css({background:cfg.ovcolor,opacity:cfg.ovopacity}).fadeIn(300);
	
	$('#psconfirm').fadeIn(300);
	center('psconfirm');
}

var eSpinner = null;
function eLoad(opt)
{
	var cfg = {
		title: opt.title || 'Loading',
		content: opt.content || '',
		timeout: opt.timeout || 0,
		spincolor: opt.spincolor || '#fff',
		fade: opt.fade || 10
	};
	
	if ( $('#uiLoad').length < 1 )
	{
		$('body').append('<div id="uiLoad" style="display:none;"></div>');
	}
	
	var html = '<div class="load-spin" id="uiLoadSpin"></div>';
	if (cfg.title != '') html+= '<div class="load-title">'+cfg.title+'</div>';
	if (cfg.content != '') html+= '<div class="load-ctl">'+cfg.title+'</div>';
	
	
	$('#uiLoad').html(html).fadeIn(cfg.fade);
	center('uiLoad');
	
	var target = document.getElementById('uiLoadSpin');
	if ( typeof Spinner != 'undefined' )
	{
	   eSpinner = new Spinner({
		lines: 13, // The number of lines to draw
		length: 9, // The length of each line
		width: 4, // The line thickness
		radius: 10, // The radius of the inner circle
		corners: 1, // Corner roundness (0..1)
		rotate: 0, // The rotation offset
		color: cfg.spincolor, // #rgb or #rrggbb
		speed: 1, // Rounds per second
		trail: 60, // Afterglow percentage
		shadow: false, // Whether to render a shadow
		hwaccel: false, // Whether to use hardware acceleration
		className: 'spinner', // The CSS class to assign to the spinner
		zIndex: 2e9, // The z-index (defaults to 2000000000)
		top: 'auto', // Top position relative to parent in px
		left: 'auto' // Left position relative to parent in px
	   }).spin(target);
	}
}
function eLoadOff(opt)
{
	if ( $('#uiLoad').length < 1 ) return;
	
	var cfg = {
		fade: opt.fade || 0
	};
	
	if ( typeof Spinner != 'undefined' && eSpinner != null )
		eSpinner.stop();
	
	$('#uiLoad').fadeOut(cfg.fade, function() { $('#uiLoad').hide().remove(); });
}


function overlay(id, cls, opt)
{
	cls = (cls) ? 'overlay ' + cls : 'overlay';
	if ($('#overlay_page_end').length < 1)
		$('body').append('<div id="overlay_page_end" style="height:1px;"></div>');
	if ($('#'+id).length < 1)
		$('body').prepend('<div id="'+id+'" class="'+cls+'"></div>')
	$('#'+id).css({height:$('#overlay_page_end').offset().top+'px',position:'fixed'});
	
	if ( !opt || typeof opt != 'object' ) return true;
	if ( typeof opt.ovcolor != 'undefined' ) $('#'+id).css({background:opt.ovcolor});
	if ( typeof opt.ovopacity != 'undefined' ) $('#'+id).css({opacity:opt.ovopacity});
}

function scrollToElement(e, diff, tm)
{
	diff = (diff) ? diff : 0;
	tm = (tm) ? tm : 500;
	if ( !$(e).length ) return false;
	var of = $(e).offset();
	
	$("html:not(:animated),body:not(:animated)").animate({
		scrollTop: (of.top+diff)
	}, tm);
	
}
var autoCenterIsInit = false, autoCenterTo = null;
function center(id, minTop, isAbs)
{
	if (!minTop) minTop = 0;
	var mobile = ( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) );
	var pos = (isAbs || ($.browser.msie && $.browser.version=="6.0") || mobile ) ? 'absolute' : 'fixed';
	var scrollTop = (pos != 'fixed') ? $(window).scrollTop() : 0;
	var scrollLeft = (pos != 'fixed') ? $(window).scrollLeft() : 0;
	var top = (($(window).height() - $('#'+id).outerHeight()) / 2) + scrollTop;
	
	$('#'+id).css("left", (($(window).width() - $('#'+id).outerWidth()) / 2) + scrollLeft + "px");
	if (top < minTop) top = minTop
	$('#'+id).css({position:pos, top:top + "px"}).addClass('auto-center');
	
	if (!autoCenterIsInit && !mobile) 
	{
		$(window).bind('resize', function()
		{
			if (autoCenterTo) clearTimeout(autoCenterTo);
			autoCenterTo = setTimeout(function()
			{
				autoCenter();
			}, 500);
		});
	}
}
function autoCenter()
{
	$('.auto-center:visible').each(function()
	{
		var old = _exdim($(this)),
			pos = $(this).css('position');
		
		var scrollTop = (pos != 'fixed') ? $(window).scrollTop() : 0;
		var scrollLeft = (pos != 'fixed') ? $(window).scrollLeft() : 0;
		
		var ntop = (($(window).height() - $(this).outerHeight()) / 2) + scrollTop, 
			nleft = (($(window).width() - $(this).outerWidth()) / 2) + scrollLeft;
		ntop = (ntop < 0) ? 0 : ntop;
		if ( old.top == ntop && old.left == nleft ) return false;
		
		$(this).animate({top:ntop+'px',left:nleft+'px'}, 200);
	});
}

function validateEmail(str)
{
	var emailRegEx = /\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
	if(str.match(emailRegEx))
		return true;
	else
		return false;
}
function validateUSAPhone(str)
{
	var phoneRegEx = /^\d{3}[\-\.\_ ]\d{3}[\-\.\_ ]\d{4}$/;
	if(str.match(phoneRegEx))
		return true;
	return false;
}
function trim(v)
{
	v=v.replace(/^\s+/, '');
	return v.replace(/\s+$/, '');
}

function ismaxlength(o)
{
	var a = $(o).attr('maxlength'), m = parseInt(a);
	if (typeof m == 'undefined' || isNaN(m)) return false;
	$(o).bind('keyup', function(e) 
	{
		var v = $(this).val(), m = parseInt($(this).attr('maxlength')); 
		if (v.length > m)
			$(this).val(v.substring(0,m))
	});
}

function expSetEmail(cnt)
{
	var se_cf = /^([^\:]+)\:([^\:]+)\:([^\:]+)\:?([^\:]+)?\:?(.+)?$/;
	$(cnt + ' a').each(function()
	{
		var ih = $(this).html();
		if(ih.length>0 && se_cf.test(ih) && ih.indexOf('<') == -1 && ih.indexOf('>') == -1)
		{
			var adr = RegExp.$2 + '@' + RegExp.$3+'.'+RegExp.$1;
			if (RegExp.$4.length > 0 && RegExp.$4 == '!')
				adr = RegExp.$2+RegExp.$3+RegExp.$1;
			$(this).attr('href', 'mailto:'+adr+((RegExp.$4.length > 0)?'?subject='+RegExp.$4:'')+((RegExp.$5.length>0)?'&body='+RegExp.$5:''));
			$(this).html(adr)
		}
	});
}

$(document).ready(function()
{
//	window.onerror = function(msg, url, linenumber)
//	{
//		alert('Error message: '+msg+'\nURL: '+url+'\nLine Number: '+linenumber)
//		return true
//	}
});	