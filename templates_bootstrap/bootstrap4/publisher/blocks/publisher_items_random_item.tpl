<{foreach item=item from=$block.items}>
    <{if $item.display_item_image|default:false === '1'}>
        <a href="<{$item.url}>"><img class="img-fluid" src="<{$item.image_path}>" alt="<{$item.alt}>" title="<{$item.alt}>" ></a>
    <{/if}>
    <{$item.titlelink}><br>
    <{if $item.display_summary|default:false === '1'}>
        <{$item.content}><br>
    <{/if}>
    <{if $item.display_categorylink|default:false === '1'}>
        <span style="font-size: 11px; padding: 0; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
                    <span class="fa fa-tag"></span>&nbsp;<{$item.categorylink}>
                </span>
    <{/if}>
    <{if $item.display_poster|default:false === '1'}>
        <span style="font-size: 11px; padding: 0 0 0 16px; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
                    <span class="fa fa-user"></span>&nbsp;<{$item.poster}>
                </span>
    <{/if}>
    <{if $item.display_date|default:false === '1'}>
        <span style="font-size: 11px; padding: 0 0 0 16px; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
                    <span class="fa fa-calendar"></span>&nbsp;<{$item.date}>
                </span>
    <{/if}>
    <{if $item.display_comment|default:'' == '1' && $item.cancomment|default:false && $item.comment|default:0 != -1}>
        <span style="font-size: 11px; padding: 0 0 0 16px; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
                    <span class="fa fa-comment"></span>&nbsp;<{$item.comment}>
                </span>
    <{/if}>
    <{if $item.display_hits|default:false === '1'}>
        <span style="font-size: 11px; padding: 0 0 0 16px; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
                    <span class="fa fa-check-circle-o"></span>&nbsp;<{$item.hits}>
                </span>
    <{/if}>


    <{if $item.display_lang_fullitem|default:'' == '1'}>
        <div align="right" style="padding: 15px 0 0 0;">
            <a class="btn btn-primary btn-xs" href='<{$item.url}>'><{$item.lang_fullitem}></a>
        </div>
    <{/if}>
<{/foreach}>
