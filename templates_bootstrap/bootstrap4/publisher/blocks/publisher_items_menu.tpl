<{if $block.currentcat}>
    <span class="label label-success" style="margin-right: 3px;">
        <{$block.currentcat}>
    </span>
<{/if}>

<{foreach item=category from=$block.categories|default:false}>
    <span class="label label-primary" style="margin-right: 3px;"><{$category.categoryLink}></span>
    <{if $category.items}>
        <{foreach item=item from=$category.items|default:false}>
            <span class="label label-primary" style="margin-right: 3px;"><{$item.titleLink}></span>
        <{/foreach}>
    <{/if}>
<{/foreach}>

