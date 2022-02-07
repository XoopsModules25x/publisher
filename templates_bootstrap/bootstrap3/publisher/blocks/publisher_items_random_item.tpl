<{foreach item=item from=$block.items}>
    <{if $item.display_item_image|default:false === '1'}>
        <a href="<{$item.url}>"><img src="<{$item.item_image}>" alt="<{$item.alt}>" title="<{$item.alt}>" style="padding:5px;" align="left"></a>
    <{/if}>

    <{$item.titlelink}><br>
    <{if $item.display_summary|default:false === '1'}><{$item.content}><br><{/if}>
    <span style="font-size: 11px; padding: 0 0 0 16px; margin: 0; line-height: 12px; opacity:0.8;-moz-opacity:0.8;">
      <{if $item.display_poster|default:false === '1'}><span class="glyphicon glyphicon-user"></span>&nbsp; <{$item.poster}> <{/if}>
        <{if $item.display_date|default:false === '1'}> <span class="glyphicon glyphicon-calendar"></span>&nbsp; <{$item.date}> <{/if}>
        <{if $item.display_categorylink|default:false === '1'}><span class="glyphicon glyphicon-tag"></span>&nbsp;<{$item.categorylink}> <{/if}>
        <{if $item.display_hits|default:false === '1'}><span class="glyphicon glyphicon-ok-circle"></span>&nbsp;<{$item.hits}><{/if}>
        <{if $item.display_comment|default:'' == '1' && $item.cancomment|default:false && $item.comment|default:0 != -1}> <span class="glyphicon glyphicon-comment"></span>&nbsp;<{$item.comment}> <{/if}>
      </span>


    <{if $item.display_lang_fullitem|default:'' == '1'}>
        <div align="right" style="padding: 15px 0 0 0;">
            <a class="btn btn-primary btn-xs" href='<{$item.url}>'><{$item.lang_fullitem}></a>
        </div>
    <{/if}>
<{/foreach}>
