<div class="item">
    <div class="itemHead">
        <span class="itemTitle"><{$item.titlelink}></span>
        <{if $show_subtitle && $item.subtitle}>
                                <br><em><{$item.subtitle}></em>
                    <{/if}>
    </div>

    <{if $op|default:'' != 'preview'}>
       
            <div class="itemInfo">
                <span class="itemPoster">
                    <div class="publisher_item_head_who">
                       
  <{if $display_category == 1}> <{$smarty.const._MD_PUBLISHER_CATEGORY}> : <{$item.category}> | <{/if}>
  <{if $display_poster == 1}> <{$smarty.const._MD_PUBLISHER_POSTER}> <{$item.who}><{/if}> 
  <{if $display_date_col == 1}> | <{$item.datesub}> <{/if}> 
  <{if $display_hits_col == 1}> | <{$item.counter}> <{$smarty.const._MD_PUBLISHER_TOTALHITS}> <{/if}> 
         
                    </div>
                </span>
            </div>
        <{/if}>
    

    <div class="itemBody">
           <{if $display_mainimage == 1}>
                    <{if $item.image_path!=''}>
                    <a href="<{$item.itemurl}>"><img src="<{$item.image_path}>" title="<{$item.title}>" alt="<{$item.title}>" align="left" width="120" style="padding:5px"></a>
                   <{else}>
                    <a href="<{$item.itemurl}>"><img src="<{$publisher_url}>/assets/images/default_image.jpg"  title="<{$item.title}>" alt="<{$item.title}>" align="left" width="120" style="padding:5px"></a>
                    <{/if}>
            <{/if}>
         <{if $display_summary == 1}>
              <div class="itemText"><{$item.summary}><br> <br> </div>
          <{/if}>
        <br> <br>
    </div>

    <{if $op|default:'' != 'preview' && $item.summary!=''}>
        <div align="right">
           <a href="<{$item.itemurl}>"> <{$smarty.const._MD_PUBLISHER_VIEW_MORE}></a>&nbsp;
        </div>
    <{/if}>

    <div class="publisher_pre_itemInfo">
        <div class="itemInfo" style="height: 14px;">

            <{if $display_commentlink == 1 && $item.cancomment && $item.comments != -1}>
                <span style="float: left;"><a href="<{$item.itemurl}>"><{$item.comments}></a></span>
            <{else}>
                <span style="float: left;">&nbsp;</span>
            <{/if}>
      
            <div style="height: 0; display: inline; clear: both;"></div>
        </div>
    </div>
</div><br>
