<{if $block.template|default:'' == 'normal'}>
    <{if $block.latestnews_scroll|default:false}>
        <marquee behavior='scroll' align='center' direction='<{$block.scrolldir}>' height='<{$block.scrollheight}>' scrollamount='3' scrolldelay='<{$block.scrollspeed}>' onmouseover='this.stop()' onmouseout='this.start()'>
    <{/if}>
    <{section name=i loop=$block.columns}>
        <ul>
            <{foreach item=item from=$block.columns[i]}>
                <li> <{$item.title}><br>
    <{if $item.display_item_image|default:false}>
         <{if $item.item_image|default:'' != ''}>
            <a href="<{$item.itemurl}>"><img src="<{$item.item_image}>" title="<{$item.alt}>" alt="<{$item.alt}>" width="<{$block.imgwidth}>" height="<{$block.imgheight}>" style="margin<{$block.margin}>: 10px; padding: 2px; border:<{$block.border}>px solid #<{$block.bordercolor}>"></a><br >
            <{else}>
            <a href="<{$item.itemurl}>"><img src="<{$block.publisher_url}>thumb.php?src=<{$block.publisher_url}>/assets/images/default_image.jpg&w=<{$block.imgheight}>" title="<{$item.alt}>" alt="<{$item.alt}>" width="<{$block.imgwidth}>" height="<{$block.imgheight}>" style="margin<{$block.margin}>: 10px; padding: 2px; border:<{$block.border}>px solid #<{$block.bordercolor}>"></a>
         <{/if}>
     <{/if}>

                    <{if $item.display_summary|default:false}>
                           <{$item.text}> <br>
                    <{/if}>
                <{$item.more}><br><br>
                                <{if $item.topic_title|default:false}>                 
                                    <span><{$block.lang_category}> : <{$item.topic_title}> | </span>
                                <{/if}>
                                <{if $item.poster|default:false}>
                                    <span class="itemPoster"><{$block.lang_poster}> <{$item.poster}> |</span>
                                <{/if}>
                                <{if $item.posttime|default:false}>
                                    <span class="itemPostDate"><{$item.posttime}> |</span>
                                <{/if}>
                                <{if $item.read|default:false}>
                                    <span><{$item.read}> <{$block.lang_reads}> |</span>
                                <{/if}>
                                <{if $item.comment|default:false && $item.cancomment|default:false && $item.comment|default:0 != -1}>
                                    <span><{$item.comment}></span>
                                <{/if}>
               
                <p class="itemPermaLink" align="right">
                <{$item.email}><{$item.print}><{$item.pdf}>
                <{if $item.display_adminlink|default:false}> 
                <{$item.admin}><{/if}>
                </p>

                </li>
            <{/foreach}>
        </ul>
    <{/section}>
    <br><{$block.topiclink}><{$block.morelink}><{$block.archivelink}><{$block.submitlink}>

    <{if $block.latestnews_scroll|default:false}>
        </marquee>
    <{/if}>
<{/if}>

<{if $block.template|default:'' == 'extended'}>

    <{php}>
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');
    <{/php}>

    <{if $block.latestnews_scroll|default:false}>
        <marquee behavior='scroll' align='center' direction='<{$block.scrolldir}>' height='<{$block.scrollheight}>' scrollamount='3' scrolldelay='<{$block.scrollspeed}>' onmouseover='this.stop()' onmouseout='this.start()'>
    <{/if}>
    <table width='100%' border='0'>
        <tr>
            <{section name=i loop=$block.columns}>
                <td width="<{$block.spec.columnwidth}>%">
                    <{foreach item=item from=$block.columns[i]}>
                        <div class="itemHead">
                            <span class="itemTitle"><{$item.title}></span>
                        </div>
                        
                            <div class="itemInfo">

                                <{if $item.topic_title|default:false}>                 
                                    <span><{$block.lang_category}> : <{$item.topic_title}> |</span>
                                 <{/if}>
                                <{if $item.poster|default:false}>
                                    <span class="itemPoster"><{$block.lang_poster}> <{$item.poster}> |</span>
                                <{/if}>
                                <{if $item.posttime|default:false}>
                                    <span class="itemPostDate"><{$item.posttime}> |</span>
                                <{/if}>
                               <{if $item.read|default:false}>
                                    <span><{$item.read}> <{$block.lang_reads}> |</span>
                                <{/if}>
                                <{if $item.comment|default:false && $item.cancomment|default:false && $item.comment|default:0 != -1}>
                                    <span><{$item.comment}></span>
                                <{/if}>
                             </div>

                 <{if $item.display_item_image|default:false}>
                       <{if $item.item_image|default:'' != ''}>
                       <a href="<{$item.itemurl}>"><img src="<{$item.item_image}>" title="<{$item.alt}>" alt="<{$item.alt}>" width="<{$block.imgwidth}>" height="<{$block.imgheight}>" style="margin<{$block.margin}>: 10px; padding: 2px; border:<{$block.border}>px solid #<{$block.bordercolor}>"></a>
                       <{else}>
                       <a href="<{$item.itemurl}>"><img src="<{$block.publisher_url}>thumb.php?src=<{$block.publisher_url}>/assets/images/default_image.jpg&w=<{$block.imgheight}>" title="<{$item.alt}>" alt="<{$item.alt}>" width="<{$block.imgwidth}>" height="<{$block.imgheight}>" style="margin<{$block.margin}>: 10px; padding: 2px; border:<{$block.border}>px solid #<{$block.bordercolor}>"></a>
                       <{/if}>
                 <{/if}>





                        <{if $block.letters|default:0 != 0}>
                            <div style="text-align:justify; padding:5px;">
                                    <{if $item.display_summary|default:false}>
                                      <{$item.text}> <br>
                                    <{/if}>
                                <{$item.more}>
                                <div style="clear:both;"></div>
                            </div>
                        <{/if}>
                        <div class="itemFoot">
                            <span class="itemPermaLink">
                            <{$item.print}><{$item.pdf}><{$item.email}>
                            <{if $item.display_adminlink|default:false}> <{$item.admin}>
                            <{/if}>
                            </span>
                        </div>
                    <{/foreach}>
                </td>
            <{/section}>
        </tr>
    </table>
    <{if $block.latestnews_scroll|default:false}></marquee><{/if}>
    <div><br ><{$block.morelink}><{$block.archivelink}><{$block.submitlink}><br ></div>
<{/if}>

<{if $block.template|default:'' == 'ticker'}>
    <marquee behavior='scroll' align='middle' direction='<{$block.scrolldir}>' height='<{$block.scrollheight}>' scrollamount='3' scrolldelay='<{$block.scrollspeed}>' onmouseover='this.stop()'
             onmouseout='this.start()'>
        <{section name=i loop=$block.columns}>
            <div style="padding:10px;">
                <{foreach item=item from=$block.columns[i]}> &nbsp;<{$item.title}>&nbsp; <{/foreach}>
            </div>
        <{/section}>
    </marquee>
<{/if}>


<{if $block.template|default:'' == 'slider1'}>

    <{php}>$GLOBALS['xoTheme']->addScript('browse.php?Frameworks/jquery/jquery.js');
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.css');
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');
    <{/php}>
    <script type="text/javascript">
        jQuery(document).ready(function () {

            //Execute the slideShow, set 4 seconds for each images
            slideShow(5000);

        });

        function slideShow(speed) {


            //append a LI item to the UL list for displaying caption
            $('ul.pub_slideshow1').append('<LI id=pub_slideshow1-caption class=caption><DIV class=pub_slideshow1-caption-container><H3></H3><P></P></DIV></LI>');

            //Set the opacity of all images to 0
            $('ul.pub_slideshow1 li').css({opacity: 0.0});

            //Get the first image and display it (set it to full opacity)
            $('ul.pub_slideshow1 li:first').css({opacity: 1.0});

            //Get the caption of the first image from REL attribute and display it
            $('#pub_slideshow1-caption h3').tpl($('ul.pub_slideshow1 a:first').find('img').attr('title'));
//        $('#pub_slideshow1-caption').find('h3').html($('ul.pub_slideshow1 a:first').find('img').attr('title')); //suggested by PhpStorm

            $('#pub_slideshow1-caption p').html($('ul.pub_slideshow1 a:first').find('img').attr('alt'));

            //Display the caption
            $('#pub_slideshow1-caption').css({opacity: 0.7, bottom: 0});

            //Call the gallery function to run the slideshow
            var timer = setInterval('gallery()', speed);

            //pause the slideshow on mouse over
            $('ul.pub_slideshow1').hover(
                    function () {
                        clearInterval(timer);
                    },
                    function () {
                        timer = setInterval('gallery()', speed);
                    }
            );

        }

        function gallery() {


            //if no IMGs have the show class, grab the first image
            var current = ($('ul.pub_slideshow1 li.show') ? $('ul.pub_slideshow1 li.show') : $('#ul.pub_slideshow1 li:first'));

            //Get next image, if it reached the end of the slideshow, rotate it back to the first image
            var next = ((current.next().length) ? ((current.next().attr('id') == 'pub_slideshow1-caption') ? $('ul.pub_slideshow1 li:first') : current.next()) : $('ul.pub_slideshow1 li:first'));

            //Get next image caption
            var title = next.find('img').attr('title');
            var desc = next.find('img').attr('alt');

            //Set the fade in effect for the next image, show class has higher z-index
            next.css({opacity: 0.0}).addClass('show').animate({opacity: 1.0}, 1000);

            //Hide the caption first, and then set and display the caption
            $('#pub_slideshow1-caption').animate({bottom: -70}, 300, function () {
                //Display the content
                $('#pub_slideshow1-caption h3')._createTrPlaceholder(title);
                $('#pub_slideshow1-caption p').tpl(desc);
                $('#pub_slideshow1-caption').animate({bottom: 0}, 500);
            });

            //Hide the current image
            current.animate({opacity: 0.0}, 1000).removeClass('show');

        }
    </script>
    <{section name=i loop=$block.columns}>

        <ul class="pub_slideshow1">
        <{foreach item=item from=$block.columns[i]}>
            <li>
                <a href="<{$item.itemurl}>"><img src="<{$item.item_image}>" width="100%" height="<{$block.imgheight}>" title="<{$item.alt}>" alt="<{$item.text}>"></a>
            </li>
        <{/foreach}>
        </ul><{/section}>

<{/if}>

<{if $block.template|default:'' == 'slider2'}>

    <{php}>$GLOBALS['xoTheme']->addScript('browse.php?Frameworks/jquery/jquery.js');
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.css');
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/jquery.popeye.style.css');
        $GLOBALS['xoTheme']->addStylesheet(PUBLISHER_URL . '/assets/css/publisher.css');
        $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/jquery.easing.js');
        $GLOBALS['xoTheme']->addScript(PUBLISHER_URL . '/assets/js/script.easing.js');<{/php}>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('#lofslidecontent45').lofJSidernews({
                interval: 4000,
                direction: 'opacity',
                duration: 1000,
                easing: 'easeInOutSine'
            });
        });

    </script>
   <{section name=i loop=$block.columns}>
        <div id="lofslidecontent45" class="lof-slidecontent">

            <div class="lof-main-outer">
                <ul class="lof-main-wapper">
                    <{foreach item=item from=$block.columns[i]}>
                        <li>
                            <img src="<{$item.item_image}>" alt="<{$item.alt}>" width="<{$block.imgwidth}>" height="<{$block.imgheight}>">
                        </li>
                    <{/foreach}>
                </ul>
            </div>

            <div class="lof-navigator-outer">
                <ul class="lof-navigator">
                    <{foreach item=item from=$block.columns[i]}>
                        <li>
                            <div>
                                <img src="<{$item.item_image}>" alt="" width="60" height="60">

                                <h3><a href="<{$item.itemurl}>"> <{$item.alt}> </a></h3>
                            </div>
                        </li>
                    <{/foreach}>
                </ul>
            </div>
        </div>
        <script type="text/javascript">

        </script>
    <{/section}>

<{/if}>
