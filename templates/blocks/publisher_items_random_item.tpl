<{foreach item=item from=$block.items}>
    <{if $item.display_item_image|default:false === '1'}>
       <a href="<{$item.url}>"><img src="<{$item.item_image}>" alt="<{$item.alt}>" title="<{$item.alt}>" style="padding:5px;" align=left></a><br>
    <{/if}>
    <strong><{$item.titlelink}></strong><br>
         <{if $item.display_summary|default:false === '1'}><{$item.content}><br><{/if}>
      <small>
          <{if $item.display_categorylink|default:false === '1'}> <{$item.lang_category}> : <{$item.categorylink}> |<{/if}>
          <{if $item.display_poster|default:false === '1'}> <{$item.lang_poster}> <{$item.poster}> | <{/if}>
          <{if $item.display_date|default:false === '1'}> <{$item.date}> | <{/if}>
          <{if $item.display_hits|default:false === '1'}> <{$item.hits}> | <{/if}>
          <{if $item.display_comment|default:'' == '1' && $item.cancomment|default:false && $item.comment|default:0 != -1}> <{$item.comment}> <{/if}>
      </small>

    <{if $item.display_lang_fullitem|default:'' == '1'}>
      <div align="right">
        <a href='<{$item.url}>'><{$item.lang_fullitem}></a>
      </div>
    <{/if}>
    <br><br>
<{/foreach}>
