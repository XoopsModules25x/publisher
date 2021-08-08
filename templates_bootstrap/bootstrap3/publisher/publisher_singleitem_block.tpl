       <div class="article_full">
     <{if $block.display_categorylink|default:false}>                 
        <div class="article_full_category">
          <{$item.category}>
        </div>
     <{/if}>


            <{if $item.image_path|default:''}>
            <div class="article_full_img_div">
                <a href="<{$item.itemurl}>" title="<{$item.title}>">
                <img src="<{$item.image_path}>" alt="<{$item.title}>">
                </a>
            </div>
            <{else}>
               <div class="article_full_img_div">
                <a href="<{$item.itemurl}>" title="<{$item.title}>">
                <img src="<{$block.publisher_url}>/assets/images/default_image.jpg" alt="<{$item.title}>">
                </a>
                </div>
            <{/if}>
     
    <div style="padding: 10px;">
        <h4><{$item.titlelink}></h4>
        <span style="font-size: 11px; padding: 0 0 0 16px; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
                   
                   <{if $block.display_who_link|default:false}> 
                    <span>
                        <span class="glyphicon glyphicon-user"></span>&nbsp;<{$item.who}>
                    </span>
                    <{/if}>
                    <{if $block.display_when_link|default:false}>
                    <span>
                        <span class="glyphicon glyphicon-calendar"></span>&nbsp;<{$item.when}>
                    </span>
                    <{/if}>
                    <{if $block.display_reads|default:false}>
                    <span>
                        <span class="glyphicon glyphicon-ok-circle"></span>&nbsp;<{$item.counter}> <{$block.lang_reads}>
                    </span>
                    <{/if}>
                    <{if $block.display_comment_link|default:false && $item.cancomment|default:false && $item.comments|default:0 != -1}>
                    <span>
                        <span class="glyphicon glyphicon-comment"></span>&nbsp;<{$item.comments}>
                    </span>
                    <{/if}>
         </span>
        <div style="margin-top:10px;">
            <{$item.summary}>
            <{if $block.truncate|default:false}>
                  <{if $block.display_readmore|default:false}>
                    <div class="pull-right" style="margin-top: 15px;">
                        <a href="<{$item.itemurl}>" class="btn btn-primary btn-xs">
                            <{$block.lang_readmore}>
                        </a>
                    </div>
                   <{/if}>
            <{/if}>
        </div>
        <div class="pull-left" style="margin-top: 15px;">
            <{if $op != 'preview'}>
                 <{if $block.display_adminlink|default:false}>
                    <span style="float: right; text-align: right;"><{$item.adminlink}></span>
                 <{/if}>
            <{else}>
                <span style="float: right;">&nbsp;</span>
            <{/if}>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
