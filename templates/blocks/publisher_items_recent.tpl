<table class="outer" cellspacing="1">
    <tr>
        <td class="head"><{$block.lang_title}></td>
<{if $block.show_category|default:'' == '1'}> <td class="head" align="left"><{$block.lang_category}></td><{/if}>
<{if $block.show_date|default:'' == '1'}> <td class="head" align="right" width="120"><{$block.lang_date}></td><{/if}>
<{if $block.show_poster|default:'' == '1'}> <td class="head" align="center" width="100"><{$block.lang_poster}></td><{/if}>
    </tr>
    <{foreach item=item from=$block.items|default:false}>
        <tr class="<{cycle values=" even,odd"}>">
            <td>
            <{if $block.show_image|default:'' == '1'}>
             <a href="<{$newitems.itemurl}>"><img src="<{$item.item_image}>" alt="<{$item.alt}>" title="<{$item.alt}>" align="left" style="padding:5px;"></a><br>
             <{/if}>
            <{$item.itemlink}>
            <{if $block.show_summary|default:'' == '1'}><br><{$item.summary}><{/if}><br>
            <small>
            <{if $block.show_hits|default:'' == '1'}><{$item.hits}><{/if}> 
            <{if $block.show_comment|default:'' == '1' && $item.cancomment|default:false && $item.comment|default:0 != -1}> | <{$item.comment}><{/if}>
            </small>
            </td>

            <{if $block.show_category|default:'' == '1'}><td align="left"><{$item.categorylink}></td><{/if}>
            <{if $block.show_poster|default:'' == '1'}><td align="center"><{$item.poster}></td><{/if}>
            <{if $block.show_date|default:'' == '1'}><td align="right"><{$item.date}></td><{/if}>
         </tr>
    <{/foreach}>
</table>

   <{if $block.show_morelink|default:'' == '1'}>
      <div style="text-align:right; padding: 5px;">
       <a href="<{$block.publisher_url}>"><{$block.lang_visitItem}></a>
      </div>
   <{/if}>
