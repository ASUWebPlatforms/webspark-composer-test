

	var isBookinPageLoaded	=	false;
	//var filePath	=	"http://localhost:9999/";
	var filePath	=	"https://my.setmore.com/";

	var setmorePopup    =    function(k,isReschedule,e)
	{
		if( e )
		{
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
		}

			var templ		=	{};
			templ.overlay	=	'<div id="setmore-overlay"></div>';
			templ.popup		=	'<div id="setmore-fancy-box" style= " background-color: #FFFFFF;height: auto;left: 50%;position: fixed;top: 0;width: 545px;z-index: 9999;">'+
											'<div id="setmore-fancy-box-close-icon"></div>'+
											'<div id="setmore-fancy-box-content">'+
											'</div>'+
										'</div>';
				init	=	function(ck)
							{
								if( !isBookinPageLoaded )
								{
									isBookinPageLoaded	=	true;
									this.renderTempl();
								}
								else
								{
									this.loadIframe();
									this.positionPopup();
									this.showPopup();
								}
							};
			renderTempl	=	function()
							{
								jQuery("body").append( templ.overlay ).append( templ.popup );
								this.positionPopup();
								this.loadIframe();
								this.bindEvents();
							};
		loadIframe		=	function()
							{
								if(isReschedule)
								{
									jQuery("#setmore-fancy-box-content").html('<iframe id="setmore-fancy-box-iframe" frameborder="0" hspace="0" scrolling="auto" src="'+filePath+'rescheduleAppointment.do?cancellationKey='+k+'"></iframe>');
								}
								else
								{
									jQuery("#setmore-fancy-box-content").html('<iframe id="setmore-fancy-box-iframe" frameborder="0" hspace="0" scrolling="auto" src="'+filePath+'shortBookingPage/'+k+'"></iframe>');
								}
							};
		bindEvents		=	function()
							{
								var self	=	this;
								jQuery("#setmore-overlay , #setmore-fancy-box-close-icon").bind("click",function(){
									self.hidePopup();
								});
							};
		positionPopup	=	function()
							{
								var windowHeight		=	jQuery(window).height();
								var windowScrollHeight	=	jQuery(document).height();
								var windowScrollTop		=	jQuery(document).scrollTop();
								var popupWidth			=	jQuery("#setmore-fancy-box").width();
								var popupHeight			=	windowHeight - 100;

								jQuery("#setmore-overlay").height( windowScrollHeight+"px" );
//								jQuery("#setmore-fancy-box").css( { 'margin-left' : "-"+(popupWidth/2)+"px" , 'margin-top' : ( ( ( windowHeight - popupHeight ) / 2 ) + windowScrollTop ) +"px"  } );
								jQuery("#setmore-fancy-box").css( { 'margin-left' : "-"+(popupWidth/2)+"px" , 'top' : '60px'  } );
							};
			hidePopup	=	function()
							{
								jQuery("#setmore-overlay,#setmore-fancy-box").hide();
							};
			showPopup	=	function()
							{
								jQuery("#setmore-overlay,#setmore-fancy-box").show();
							}
			this.init(k);
	}

	//include required css file
	loadCss	=	function()
	{
		//var cssFilePath		=	'<link href="'+filePath+'css/setmorePopup.css" rel="stylesheet" type="text/css" />';
		var cssFilePath		=	'<link href="/sites/all/themes/access/css/setmorePopup.css" rel="stylesheet" type="text/css" />';

		if( typeof jQuery !== "undefined" )
		{
			jQuery("head").append( cssFilePath );
		}
		else
		{
		    var script = document.createElement("SCRIPT");
		    script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
		    script.type = 'text/javascript';
		    document.getElementsByTagName("head")[0].appendChild(script);

		    var checkReady = function(callback)
		    {
		        if (window.jQuery)
		        {
		            callback(jQuery);
		        }
		        else
		        {
		            window.setTimeout(function() { checkReady(callback); }, 100);
		        }
		    };

		    checkReady( function(jQuery)
		    {
		    	jQuery("head").append( cssFilePath );
		    });
		}
	}
	loadCss();
