<div class="outer" cellspacing="1">
    <div><h4><{$block.lang_articles_from_to|default:''}></h4></div>
    <div>
        <ul>
            <{foreach item=item from=$block.items|default:false}>
                <li><{$item.itemlink}></li>
            <{/foreach}>
        </ul>
    </div>
</div>
