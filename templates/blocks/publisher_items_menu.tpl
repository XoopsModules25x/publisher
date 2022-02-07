<table cellspacing="0">
    <tr>
        <td id="mainmenu">
            <{if $block.currentcat|default:''}> <{$block.currentcat}> <{/if}>
            <{foreach item=category from=$block.categories|default:false}>
                <{$category.categoryLink}>
                <{if $category.items|default:''}>
                    <{foreach item=item from=$category.items|default:false}> <{$item.titleLink}> <{/foreach}> 
                <{/if}>
            <{/foreach}>
        </td>
    </tr>
</table>
