function fancybox(){
  jQuery("#primary li a.idea-link").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	true
	});
	 jQuery("#primary li a.info-link").fancybox();
	 
	 /*jQuery('a.idea-link').click(function(){
//    console.log(jQuery(this).children('img').attr('src'));
	  jQuery('#big-bg').attr('src', jQuery(this).attr('href')); 
	 });*/
}

function masonWall(){

  var 
      speed = 1000,   // animation speed
      $wall = jQuery('#primary'),
      $columnWidth = 250 + 5
  ;

  $wall.masonry({
    columnWidth: $columnWidth, 
    // only apply masonry layout to visible elements
    //itemSelector: '(img,.box):not(.invis, .box img, .box-inner)',
    resizeable: true,
    itemSelector: '(li.idea):not(.invis, .invis img, li *)',
    animate: true,
    animationOptions: {
      duration: speed,
      queue: false
      //complete: masonFinished    
    }
  });
  // show all hidden boxes
  $wall.children('.idea-list-text')
      .toggleClass('invis').fadeOut(speed);
  $wall.masonry();
  
//  jQuery('#tag-list a').button();
  jQuery('#tag-list a').click(function(){
    var colorClass = '.' + jQuery(this).attr('id');
    
    if(colorClass === '.all') {
      // show all hidden boxes
      $wall.children('.invis.idea-list-img')
          .toggleClass('invis').fadeIn(speed);
    }
    else {    
      // hide visible boxes 
      $wall.children().not(colorClass).not('.invis')
          .toggleClass('invis').fadeOut(speed);
      // show hidden boxes
      $wall.children(colorClass+'.invis')
          .toggleClass('invis').fadeIn(speed);
    }
    if(colorClass !== '.idea-list-text'){
      $wall.children('.idea-list-text').not('.invis')
          .toggleClass('invis').fadeOut(speed);
    }
    $wall.masonry();
  
    return false;
  });
}

function imageHovers(){
  $('li, td').hover(  
   function() {  
    $(this).addClass('hover');  
   },  
   function() {  
    $(this).removeClass('hover');  
   }  
  ); 
}

function growImage(){
	$('a.idea-link').each(function() {
		var oheight = $(this).children(0).height();
		var owidth = $(this).children(0).width();
		var nheight = (oheight + (oheight * 0.25));
		var nwidth = (owidth + (owidth * 0.25));
		var top = ((oheight - nheight) / 2);
		var left = ((owidth - nwidth) / 2);

		$(this).mouseenter(function() {
			$(this).css('z-index', '2').children(0).css('z-index','3').stop().animate({
					'height' : nheight+'px',
					'width' : nwidth+'px',
					'left' : top+'px',
					'top' : left+'px'}, 210);
		});

		$(this).mouseleave(function() {
			$(this).children(0).css('z-index','1').stop().animate({
					'left' : '0px',
					'top' : '0px',
					'height' : oheight+'px',
					'width' : owidth+'px'}, 150, function() {
						$(this).css('height', oheight+'px').parent().css('z-index', '1');
					});
		});

	});
}

function projectSelect(){
  var slideSpeed = 400;
  var mouseOver = false;
  var currentMenu = '';
  jQuery("#project-list").mouseover(function() { mouseOver = true; }).mouseout(function() { mouseOver = false; });

  jQuery('#project-select').click(function(event){
    event.preventDefault();
    if(jQuery('#project-list:visible').length != 0){
      jQuery('#project-list').hide("slide", { direction: "up" }, slideSpeed, function(){
        jQuery(this).removeClass('on');
        this.blur();
      });
    }
    else{
      jQuery('#project-list').show("slide", { direction: "up" }, slideSpeed, function(){
        jQuery(this).addClass('on');
      });
    }  
  });
  
  jQuery('#project-select').blur(function(event){
    if(jQuery('#project-list:visible').length != 0 && !mouseOver){
      jQuery('#project-list:visible').hide("slide", { direction: "up" }, slideSpeed);
      jQuery(this).removeClass('on');
    }
  });

}

jQuery(document).ready(function() {
  masonWall();
	fancybox();
	imageHovers();
	growImage();
	projectSelect();
});
