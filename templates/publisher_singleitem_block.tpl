<div class="item">
    <div class="itemHead">
        <span class="itemTitle"><{$item.titlelink}></span>
    </div>
    
        <div class="itemInfo">
            <span class="itemPoster">
                <div class="publisher_item_head_who">
                    <{if $block.display_who_link|default:false}> <{$block.lang_poster}> <{$item.who}><{/if}> <{if $block.display_when_link|default:false}> | <{$item.when}><{/if}> <{if $block.display_reads|default:false}> | <{$item.counter}> <{$block.lang_reads}> <{/if}> <{if $block.display_categorylink|default:false}> | <{$block.lang_category}> : <{$item.categorylink}><{/if}>
                </div>
            </span>
        </div>
    

    <div class="itemBody">
        <div class="itemText">
        <{if $block.display_item_image|default:false}>
            <{if $item.image_path|default:''}>
                <a href="<{$item.itemurl}>"><img class="publisher_item_image" src="<{$item.image_path}>" align="right" alt="<{$item.clean_title}>" title="<{$item.clean_title}>" width="120"></a>
            <{else}>
                <a href="<{$item.itemurl}>"><img class="publisher_item_image" src="<{$block.publisher_url}>/assets/images/default_image.jpg" align="right" alt="<{$item.clean_title}>" title="<{$item.clean_title}>" width="120"></a>

            <{/if}>
        <{/if}>
            <{$item.summary}>
            <{if $block.truncate|default:false}>
            <{if $block.display_readmore|default:false}>
            <div style="font-size: 10px; text-align: right;">
                    <a href="<{$item.itemurl}>"><{$block.lang_readmore}></a></div>
            <{/if}>
            <{/if}>
        </div>
    </div>
    <div style="clear: both;"></div>
    <div class="publisher_pre_itemInfo">
        <div class="itemInfo" style="height: 14px;">
            <{if $block.display_comment_link|default:false && $item.cancomment|default:false && $item.comments|default:0 != -1}>
                <span style="float: left;"><a href="<{$item.itemurl}>"><{$item.comments}></a></span>
            <{else}>
                <span style="float: left;">&nbsp;</span>
            <{/if}>
            <{if $block.display_adminlink|default:false}>
            <span style="float: right; text-align: right;"><{$item.adminlink}></span>
            <{/if}>
        </div>
    </div>
</div>


