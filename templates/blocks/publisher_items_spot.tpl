<{if $block.display_cat_image|default:false}>
<{if $block.category|default:false && $block.category.image_path|default:'' != ''}>
    <div align="center">
        <a href="<{$block.category.categoryurl}>" title="<{$block.category.name}>">
            <img src="<{$block.category.image_path}>" width="185" height="80" alt="<{$block.category.name}>">
        </a>
    </div>
<{/if}>
<{/if}>

<{if $block.display_type|default:'' == 'block'}>
    <{foreach item=item from=$block.items|default:false}>
        <{include file="db:publisher_singleitem_block.tpl" item=$item}>
    <{/foreach}>

<{else}>
    <{foreach item=item from=$block.items name=spotlight}>
        <{if $item.summary|default:''}>
            <div class="itemText" style="padding-left: 5px; padding-top: 5px;">
                <div>
                    <img style="vertical-align: middle;" src="<{$block.publisher_url}>/assets/images/links/doc.png" alt="">&nbsp;<{$item.titlelink}>
                    <br>
                    <small> 
                    <{if $block.display_who_link|default:false}> <{$block.lang_poster}> <{$item.who}><{/if}> <{if $block.display_when_link|default:false}> | <{$item.when}><{/if}> <{if $block.display_reads|default:false}> | <{$item.counter}> <{$block.lang_reads}> <{/if}> <{if $block.display_categorylink|default:false}> | <{$block.lang_category}> : <{$item.categorylink}><{/if}>
                    </small>
                </div>

                <div>
                       <{if $block.display_item_image|default:false}>
                          <{if $item.image_path|default:''}>
                          <a href="<{$item.itemurl}>"><img class="publisher_item_image" src="<{$item.image_path}>" align="left" alt="<{$item.clean_title}>" title="<{$item.clean_title}>" style="width:120px"></a>
                          <{else}>
                          <a href="<{$item.itemurl}>"><img class="publisher_item_image" src="<{$block.publisher_url}>/assets/images/default_image.jpg" align="left" alt="<{$item.clean_title}>" title="<{$item.clean_title}>" style="width:120px"></a>
                          <{/if}>
                       <{/if}>
                  <{$item.summary}><br>

            <{if $block.display_comment_link|default:false && $item.cancomment|default:false && $item.comments|default:0 != -1}>
                <span style="font-size: 10px; float: left;"><a href="<{$item.itemurl}>"><{$item.comments}></a></span>
            <{else}>
                <span style="float: left;">&nbsp;</span>
            <{/if}>
                    <{if $block.display_adminlink|default:false}>
                    <span style="float: right; text-align: right;"><{$item.adminlink}></span>
                    <{/if}>
                </div>
            </div>
            <div style="clear: both;"></div>
            <{if $item.showline|default:false}>
                <div style="font-size: 10px; text-align: right; border-bottom: 1px dotted #000000;"></div>
            <{/if}>
            <{if $block.truncate|default:false}>
              <{if $block.display_readmore|default:false}>
                <div style="font-size: 10px; text-align: right;">
                    <a href="<{$item.itemurl}>"><{$block.lang_readmore}></a></div>
               <{/if}>
            <{/if}>
        <{/if}>
    <{/foreach}>
<{/if}>

<{if $block.lang_displaymore|default:''}>
    <div class="clear"></div>
    <br><div class="col-xs-12 right"><a class="btn-readmore" href="<{$block.publisher_url}>" title="<{$block.lang_displaymore}>"><{$block.lang_displaymore}></a></div>
<{/if}>
