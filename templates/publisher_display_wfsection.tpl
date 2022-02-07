<{include file='db:publisher_header.tpl'}>

<!--<{if $collapsable_heading|default:0 == 1}>
    <div class="publisher_collaps_title">
        <a href='javascript:;' onclick="toggle('toptable'); toggleIcon('toptableicon')">
            <img id='toptableicon' src='<{$publisher_url}>/assets/images/links/close12.gif' alt=''>
        </a>&nbsp;<{$lang_category_summary}>
    </div>
    <div id='toptable'>
        <span class="publisher_collaps_info""><{$lang_category_summary}></span>
<{/if}> -->

<{if $indexpage|default:false}>
    <div class="item">
        <!-- Start categories loop -->
        <{foreach item=category from=$categories}>
            <div class="publisher_category_index_list" style="clear: both;">
                <div class="publisher_categoryname"><{$category.categorylink}></div>
                <div>
                    <{if $category.image_path|default:''}>
                        <img class="publisher_category_image" src="<{$category.image_path}>" alt="<{$category.name}>" width="<{$category_list_image_width}>">
                    <{/if}> <{$category.description}>
                </div>
                <{if $category.subcats|default:false}>
                    <div class="publisher_subcats">
                        <div class="publisher_subcats_info"><{$category.lang_subcategories}></div>
                        <{foreach name=loop item=subcat from=$category.subcats}> <{$subcat.categorylink}><{if $smarty.foreach.loop.iteration < $category.subcatscount}> -<{/if}> <{/foreach}>
                    </div>
                <{/if}>
                <div style="clear: both;"></div>
            </div>
        <{/foreach}> <!-- End categories loop -->
    </div>
<{else}>
    <div>
        <!-- Start categories loop --> 
        <{foreach item=category from=$categories}>
            <div style="clear: both;">
                <div>
                    <{if $category.image_path|default:''}>
                        <img class="publisher_category_image" src="<{$category.image_path}>" alt="<{$category.name}>" width="<{$category_list_image_width}>">
                    <{/if}> <{$category.description}>
                </div>
                <div class="publisher_category_header">
                    <{$category.header}>
                </div>
                <div style="clear: both;"></div>
                <{if $category.subcats|default:false}>
                    <div class="publisher_subcats">
                        <div class="publisher_subcats_info"><{$category.lang_subcategories}></div>
                        <{foreach name=loop item=subcat from=$category.subcats}> <{$subcat.categorylink}><{if $smarty.foreach.loop.iteration < $category.subcatscount}> -<{/if}> <{/foreach}>
                    </div>
                <{/if}>
            </div>
        <{/foreach}> <!-- End categories loop -->
    </div>
<{/if}>

<!--<{if $collapsable_heading|default:0 == 1}>
    </div>
<{/if}>-->
<div class="publisher_items_list">
    <{if $items|default:false}>
        <{if $collapsable_heading|default:0 == 1}>
            <div class="publisher_collaps_title">
                <a href='javascript:' onclick="toggle('bottomtable'); toggleIcon('bottomtableicon')"><img id='bottomtableicon' src='<{$publisher_url}>/assets/images/links/close12.gif'
                                                                                                          alt=''></a>&nbsp;<{$lang_items_title}>
            </div>
            <div id='bottomtable'>
            <span class="publisher_collaps_info"><{$smarty.const._MD_PUBLISHER_ITEMS_INFO}></span>
        <{/if}>
        <div align="right"><{$navbar|default:''}></div>
        <div class="item">
            <{foreach item=item from=$items}>
               <div class="itemText" style="padding-left: 5px; padding-top: 5px;">
                <div> 
                      <{if $display_mainimage|default:0 == 1}>
                         <{if $item.image_path|default:'' != ''}>
                         <a href="<{$item.itemurl}>"><img src="<{$item.image_path}>" title="<{$item.title}>" alt="<{$item.title}>" width="120" style="padding:5px"></a>
                          <{else}>
                          <a href="<{$item.itemurl}>"><img src="<{$publisher_url}>/assets/images/default_image.jpg"  title="<{$item.title}>" alt="<{$item.title}>" width="120" style="padding:5px"></a>
                          <{/if}>
                      <{/if}>
                <br>
                   <{$item.titlelink}><br>
                   <{if $show_subtitle|default:false && $item.subtitle|default:false}>
                       <em><{$item.subtitle}><br></em>
                    <{/if}>
                    <small>
                 <{if $display_category|default:0 == 1}> <{$smarty.const._MD_PUBLISHER_CATEGORY}> : <{$item.category}> | <{/if}>
                 <{if $display_poster|default:0 == 1}> <{$smarty.const._MD_PUBLISHER_POSTER}> <{$item.who}><{/if}> 
                 <{if $display_date_col|default:0 == 1}> | <{$item.datesub}> <{/if}> <{if $display_hits_col|default:0 == 1}> | 
                 <{$item.counter}> <{$smarty.const._MD_PUBLISHER_TOTALHITS}> <{/if}> 
                 <{if $display_commentlink|default:0 == 1 && $item.cancomment|default:false && $item.comments|default:0 != -1}> | <{$item.comments}><{/if}>
                    </small>
                </div>
                <div>
<{if $display_summary|default:0 == 1}> <{$item.summary}><br> <{/if}>


                    </div>
            </div>
            <div style="clear: both;"></div>
            
              
           <{if $display_readmore|default:0 == 1}>
                <div align="right">
                <a href="<{$item.more}>"><{$smarty.const._MD_PUBLISHER_READMORE}></a> </div><br >
           <{/if}>

             <div style="font-size: 10px; text-align: right; border-bottom: 1px dotted #000000;"></div>
        
            <{/foreach}>
        </div>
        <div align="right"><{$navbar|default:''}></div>
        <{$press_room_footer}>

        <{if $collapsable_heading|default:0 == 1}>
            </div>
        <{/if}>
    <{/if}><!-- end of if $items -->
</div>

<{include file='db:publisher_footer.tpl'}>
