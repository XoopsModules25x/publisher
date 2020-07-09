<{foreach item=items from=$block.items}>
    <{if $block.display_item_image == '1'}>
       <a href="<{$block.url}>"><img src="<{$block.item_image}>" alt="<{$block.alt}>" title="<{$block.alt}>" style="padding:5px;" align=left></a><br>
    <{/if}>
    <strong><{$block.titlelink}></strong><br>
         <{if $block.display_summary == '1'}><{$block.content}><br><{/if}>
      <small>
          <{if $block.display_categorylink == '1'}> <{$block.lang_category}> : <{$block.categorylink}> |<{/if}>
          <{if $block.display_poster == '1'}> <{$block.lang_poster}> <{$block.poster}> | <{/if}>
          <{if $block.display_date == '1'}> <{$block.date}> | <{/if}>
          <{if $block.display_hits == '1'}> <{$block.hits}> <{$block.lang_hits}> | <{/if}>
          <{if $block.display_comment == '1' && $block.cancomment && $block.comment != -1}> <{$block.comment}> <{/if}>
      </small>

    <{if $block.display_lang_fullitem == '1'}>
      <div align="right">
        <a href='<{$block.url}>'><{$block.lang_fullitem}></a>
      </div>
    <{/if}>
<{/foreach}>
