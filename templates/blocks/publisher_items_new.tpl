<table cellpadding="0" cellspacing="0" border="0">
    <{foreach item=newitems from=$block.newitems|default:null}>
        <tr class="<{cycle values="even,odd"}>">
            <{if $newitems.image}>
                <td>
                    <a href="<{$newitems.itemurl}>"><img style="padding: 1px; margin: 2px; border: 1px solid #c3c3c3;" width="50" src="<{$newitems.image}>" title="<{$newitems.alt}>" alt="<{$newitems.alt}>"></a>
                </td>
            <{/if}>
            <td>
                <strong><{$newitems.link}></strong>
                <{if $block.show_order == '1'}>
                    (<{$newitems.new}>)
                <{/if}>
                <br>
                <{if $block.show_summary == '1'}><{$newitems.summary}><{/if}>
                <br> 
                <small>
                <{if $block.show_poster == '1'}><{$newitems.lang_poster}> <{$newitems.poster}> |<{/if}>
                <{if $block.show_date == '1'}> <{$newitems.date}> <{/if}>
                <{if $block.show_category == '1'}> | <{$newitems.lang_category}> : <{$newitems.categorylink}> <{/if}>
                <{if $block.show_hits == '1'}>| <{$newitems.hits}> <{$newitems.lang_hits}> |<{/if}>
                <{if $block.show_comment == '1' && $newitems.cancomment && $newitems.comment != -1}><{$newitems.comment}> |<{/if}>
                <{if $block.show_rating == '1'}><{$newitems.rating}><{/if}>
                </small>

            </td>
        </tr>
    <{/foreach}>
</table>
