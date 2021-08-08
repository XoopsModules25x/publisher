<table cellpadding="0" cellspacing="0" border="0">
    <{foreach item=newitems from=$block.newitems|default:null}>
        <tr class="<{cycle values="even,odd"}>">
            <{if $newitems.image|default:false}>
                <td>
                    <a href="<{$newitems.itemurl}>"><img style="padding: 1px; margin: 2px; border: 1px solid #c3c3c3;" width="50" src="<{$newitems.image}>" title="<{$newitems.alt}>" alt="<{$newitems.alt}>"></a>
                </td>
            <{/if}>
            <td>
                <strong><{$newitems.link}></strong>
                <{if $block.show_order|default:'' == '1'}>
                    (<{$newitems.new}>)
                <{/if}>
                <br>
                <{if $block.show_summary|default:'' == '1'}><{$newitems.summary}><{/if}>
                <br> 
                <small>
                <{if $block.show_poster|default:'' == '1'}><{$newitems.lang_poster}> <{$newitems.poster}> |<{/if}>
                <{if $block.show_date|default:'' == '1'}> <{$newitems.date}> <{/if}>
                <{if $block.show_category|default:'' == '1'}> | <{$newitems.lang_category}> : <{$newitems.categorylink}> <{/if}>
                <{if $block.show_hits|default:'' == '1'}>| <{$newitems.hits}> <{$newitems.lang_hits}> |<{/if}>
                <{if $block.show_comment|default:false == '1' && $newitems.cancomment|default:false && $newitems.comment|default:0 != -1}><{$newitems.comment}> |<{/if}>
                <{if $block.show_rating|default:'' == '1'}><{$newitems.rating}><{/if}>
                </small>

            </td>
        </tr>
    <{/foreach}>
</table>
