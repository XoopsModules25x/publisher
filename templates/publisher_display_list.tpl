<{include file='db:publisher_header.tpl'}>

<!-- Display type is bullet list -->
<h4><{$lang_category_summary}></h4>
<span class="publisher_collaps_info"><{$category.description}><br></span><!-- if the option is set, add the last item column -->
<{if $category|default:false && $displaylastitem|default:0 == 1 && $items|default:false}>
    <div id="publisher_bullet_lastitem">
        <strong><{$smarty.const._MD_PUBLISHER_LAST_SMARTITEM}> : <{$category.last_title_link}>
    </div>
<{/if}>
<br>

<!-- if we are on the index page OR inside a category that has subcats OR (inside a category with no subcats AND $display_category_summary is set to TRUE, let's display the summary table ! //-->
<{if $indexpage || $category.subcats || ($category && $display_category_summary)}>
    <{include file='db:publisher_categories_table.tpl'}><br>
<{/if}><!-- End of if !$category || $category.subcats || ($category && $display_category_summary) //-->

<{if $items|default:false}>
    <{if $collapsable_heading|default:0 == 1}>
        <div class="publisher_collaps_title">
            <a href='javascript:' onclick="toggle('bottomtable'); toggleIcon('bottomtableicon')"><img id='bottomtableicon' src='<{$publisher_url}>/assets/images/links/close12.gif'
                                                                                                      alt=''></a>&nbsp;<{$lang_items_title}>
        </div>
        <div id='bottomtable'>
    <{else}>
        <{if $subcats|default:false}>
            <div class="publisher_collaps_title"><strong><{$lang_items_title}></strong></div>
        <{/if}><!-- Content under the collapsable bar //-->    
    <{/if}>

        <!-- Start item loop -->
       
            <{foreach item=item from=$items}>
            
            <div class="itemText" style="padding-left: 5px; padding-top: 5px;">
                <div>
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
                   <{if $display_mainimage|default:0 == 1}>
                      <{if $item.image_path|default:'' != ''}>
                      <a href="<{$item.itemurl}>"><img src="<{$item.image_path}>" title="<{$item.title}>" alt="<{$item.title}>" align="left" width="120" style="padding:5px"></a>
                      <{else}>
                       <a href="<{$item.itemurl}>"><img src="<{$publisher_url}>/assets/images/default_image.jpg"  title="<{$item.title}>" alt="<{$item.title}>" align="left" width="120" style="padding:5px"></a>
                      <{/if}>
                  <{/if}>

              <{if $display_summary|default:0 == 1}><{$item.summary}><br><{/if}>
                    </div>
            </div>
            <div style="clear: both;"></div>
            
               
           <{if $display_readmore|default:0 == 1}>
               <div align="right">
                <a href="<{$item.more}>"><{$smarty.const._MD_PUBLISHER_READMORE}></a><br >
               </div><br >
           <{/if}>

             <div style="font-size: 10px; text-align: right; border-bottom: 1px dotted #000000;"></div>
            <{/foreach}>
        
        <!-- End item loop -->
      
    <div align="right"><{$navbar|default:''}></div>
    <{if $collapsable_heading|default:0 == 1}>
        </div>
    <{/if}>
<{/if}><!-- end of if $items -->

<{if $subcats|default:0 == 0 && $items|default:0 == 0}>
    <div class="publisher_infotext"><{$smarty.const._MD_PUBLISHER_EMPTY}></div>
<{/if}>

<{include file='db:publisher_footer.tpl'}>
