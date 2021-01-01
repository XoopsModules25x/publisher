<{include file='db:publisher_header.tpl'}>

<script src="<{xoAppUrl browse.php?Frameworks/jquery/jquery.js}>"></script>
<script src="<{$publisher_url}>/assets/js/jquery.popeye-2.1.js"></script>
<script src="<{$publisher_url}>/assets/js/publisher.js"></script>

<link rel="stylesheet" type="text/css" href="<{$publisher_url}>/assets/css/jquery.popeye.css">
<link rel="stylesheet" type="text/css" href="<{$publisher_url}>/assets/css/jquery.popeye.style.css">
<link rel="stylesheet" type="text/css" href="<{$publisher_url}>/assets/css/publisher.css">
<link rel="stylesheet" type="text/css" href="<{$publisher_url}>/assets/css/rating.css">

<div class="item">
    <h2>&nbsp;<{$item.title}></h2>
    <{if $show_subtitle && $item.subtitle}>
        <h3><{$item.subtitle}></h3>
    <{/if}>
    <{if $display_itemcategory}>
        <small>&nbsp;<{$smarty.const._MD_PUBLISHER_CATEGORY}> : <{$item.category}> </small>
    <{/if}>
    <{if $display_who_link}>
        <small>| <{$smarty.const._MD_PUBLISHER_POSTER}> <{$item.who}> </small>
    <{/if}>
    <{if $display_when_link}>
        <small><{$item.when}> </small>
    <{/if}>
    <{if $display_hits_link}>
        <small>(<{$item.counter}> <{$smarty.const._MD_PUBLISHER_READS}>)</small>
    <{/if}>

    <div class="itemBody">
        <{if $pagenav}>
            <div class="publisher_pagenav_top"><{$smarty.const._MD_PUBLISHER_PAGE}>: <{$pagenav}></div>
        <{/if}>
        <div class="itemText">
            <{if $item.image_path==''}>
                <{if $display_defaultimage}>
                    <img src="<{$publisher_url}>/assets/images/default_image.jpg" alt="<{$item.title}>" title="<{$item.title}>">
                <{/if}>
            <{/if}>
            <{if $item.image_path || $item.images}>
                <div class="ppy" id="ppy3">
                    <ul class="ppy-imglist">
                        <{if $item.image_path}>
                            <li>
                                <a href="<{$item.image_path}>">
                                    <img src="<{$item.image_thumb}>" alt="<{$item.image_name}>">
                                </a>
                            </li>
                        <{/if}>
                        <{foreach item=image from=$item.images}>
                            <li>
                                <a href="<{$image.path}>">
                                    <img src="<{$image.thumb}>" alt="<{$image.name}>">
                                </a>
                            </li>
                        <{/foreach}>
                    </ul>
                    <div class="ppy-outer">
                        <div class="ppy-stage">
                            <div class="ppy-nav">
                                <div class="nav-wrap">
                                    <a class="ppy-prev" title="<{$smarty.const._MD_PUBLISHER_PREVIOUSIMG}>"><{$smarty.const._MD_PUBLISHER_PREVIOUSIMG}></a>
                                    <a class="ppy-switch-enlarge" title="<{$smarty.const._MD_PUBLISHER_ENLARGEIMG}>"><{$smarty.const._MD_PUBLISHER_ENLARGEIMG}></a>
                                    <a class="ppy-switch-compact" title="<{$smarty.const._MD_PUBLISHER_CLOSE}>"><{$smarty.const._MD_PUBLISHER_CLOSE}></a>
                                    <a class="ppy-next" title="<{$smarty.const._MD_PUBLISHER_NEXTIMG}>"><{$smarty.const._MD_PUBLISHER_NEXTIMG}></a>
                                </div>
                            </div>

                            <div class="ppy-counter">
                                <strong class="ppy-current"></strong> <{$smarty.const._MD_PUBLISHER_OF}>
                                <strong class="ppy-total"></strong>
                            </div>
                        </div>
                        <div class="ppy-caption">
                            <span class="ppy-text  blockTitle"></span>
                        </div>
                    </div>
                </div>
            <{/if}>

            <p><{$item.maintext}></p>
        </div>
        <div style="clear:both;"></div>
        <{if $item.embeded_files}>
            <div id="publisher_embeded_files">
                <{foreach item=file from=$item.embeded_files}>
                    <div><{$file.content}></div>
                <{/foreach}>
            </div>
        <{/if}>


        <{if $pagenav}>
            <div class="publisher_pagenav_bottom"><{$smarty.const._MD_PUBLISHER_PAGE}>: <{$pagenav}></div>
        <{/if}>
        <{if $tagbar|default:false}>
            <p><{include file="db:tag_bar.tpl"}></p>
        <{/if}>
    </div>

    <{*    <{if $rating_enabled|default:false}>*}>
    <{*       <small><{$item.ratingbar}></small>*}>
    <{*    <{/if}>*}>



    <{*    ====== VOTING =========*}>
    <{if $displaylike}>
        <div class="clearfix"></div>
        <{*        <div class="pull-left">*}>

        <{include file='db:publisher_vote.tpl'}>

        <{*        </div>*}>
    <{/if}>
    <{*    ====== END VOTING =========*}>




    <{if $itemfooter}>
        <div class="publisher_itemfooter"><{$itemfooter}></div>
    <{/if}>

    <div class="publisher_pre_itemInfo">
        <div class="itemInfo" style="height: 14px;">
            <{if $display_comment_link && $item.cancomment && $item.comments != -1}>
                <span style="float: left;">
                    <a href="<{$item.itemurl}>"><{$item.comments}></a>
                </span>
            <{else}>
                <span style="float: left;">&nbsp;</span>
            <{/if}>
            <{if $perm_author_items && $item.uid != 0}>
                <span style="float: left; margin-left: 5px;"><a href="<{$publisher_url}>/author_items.php?uid=<{$item.uid}>"><{$smarty.const._MD_PUBLISHER_ITEMS_SAME_AUTHOR}></a></span>
            <{/if}>
            <span style="float: right; text-align: right;"><{$item.adminlink}></span>
            <span style="float: right; text-align: right;">
             <{if $display_print_link|default:0 !=0}>
                 <{$item.printlink}>
             <{/if}>
             <{if $display_pdf_button|default:0 !=0}>
                    <{$item.pdfbutton}>
                <{/if}>
             </span>
            <div style="height: 0; display: inline; clear: both;"></div>
        </div>
    </div>
</div>
<br>

<{if $item.files}>
    <table border="0" width="90%" cellspacing="1" cellpadding="0" align="center" class="outer">
        <tr>
            <td colspan="4" class="itemHead">
                <strong><{$smarty.const._CO_PUBLISHER_FILES_LINKED}></strong></td>
        </tr>
        <tr class="even">
            <td align="left" class="itemTitle">
                <strong><{$smarty.const._CO_PUBLISHER_FILENAME}></strong></td>
            <td align="center" width="100" class="itemTitle">
                <strong><{$smarty.const._MD_PUBLISHER_DATESUB}></strong></td>
            <td align="center" width="50" class="itemTitle">
                <strong><{$smarty.const._MD_PUBLISHER_HITS}></strong></td>
        </tr>

        <!-- BEGIN DYNAMIC BLOCK -->
        <{foreach item=file from=$item.files}>
            <tr>
                <td class="odd" align="left">
                    <{if $file.mod}>
                        <a href="<{$publisher_url}>/file.php?op=mod&fileid=<{$file.fileid}>">
                            <img src="<{$publisher_url}>/assets/images/links/edit.gif" title="<{$smarty.const._CO_PUBLISHER_EDITFILE}>" alt="<{$smarty.const._CO_PUBLISHER_EDITFILE}>"></a>
                        <a href="<{$publisher_url}>/file.php?op=del&fileid=<{$file.fileid}>">
                            <img src="<{$publisher_url}>/assets/images/links/delete.png" title="<{$smarty.const._CO_PUBLISHER_DELETEFILE}>" alt="<{$smarty.const._CO_PUBLISHER_DELETEFILE}>"></a>
                    <{/if}>
                    <a href="<{$publisher_url}>/visit.php?fileid=<{$file.fileid}>" target="_blank">
                        <img src="<{$publisher_url}>/assets/images/links/file.png" title="<{$smarty.const._MD_PUBLISHER_DOWNLOAD_FILE}>"
                             alt="<{$smarty.const._MD_PUBLISHER_DOWNLOAD_FILE}>">&nbsp;<strong><{$file.name}></strong>
                    </a>

                    <div><{$file.description}></div>
                </td>
                <td class="odd" align="center"><{$file.datesub}></td>
                <td class="odd" align="center"><{$file.hits}></td>
            </tr>
        <{/foreach}> <!-- END DYNAMIC BLOCK -->
    </table>
    <br>
<{/if}>

<{if $other_items == "previous_next"}>
    <{if $previousItemLink || $nextItemLink}>
        <table class="outer">
            <tr>
                <td class="itemHead" colspan="2">
                    <strong><{$smarty.const._MD_PUBLISHER_ITEMS_LINKS}></strong></td>
            </tr>
            <tr style="vertical-align: middle;">
                <td class="odd" width="50%" align="left">
                    <{if $previousItemLink}>
                        <a href="<{$previousItemUrl}>">
                            <img style="vertical-align: middle;" src="<{$publisherImagesUrl}>/links/previous.gif" title="<{$smarty.const._MD_PUBLISHER_PREVIOUS_ITEM}>"
                                 alt="<{$smarty.const._MD_PUBLISHER_PREVIOUS_ITEM}>">
                        </a>
                        <{$previousItemLink}>
                    <{/if}>
                </td>
                <td class="odd" width="50%" align="right">
                    <{if $nextItemLink}> <{$nextItemLink}>
                        <a href="<{$nextItemUrl}>"><img style="vertical-align: middle;" src="<{$publisherImagesUrl}>/links/next.gif" title="<{$smarty.const._MD_PUBLISHER_NEXT_ITEM}>"
                                                        alt="<{$smarty.const._MD_PUBLISHER_NEXT_ITEM}>"></a>
                    <{/if}>
                </td>
            </tr>
        </table>
    <{/if}>
<{elseif $other_items == 'all'}>
    <table border="0" width="90%" cellspacing="1" cellpadding="3" align="center" class="outer">
        <tr>
            <td align="left" class="itemHead" width='65%'>
                <strong><{$smarty.const._MD_PUBLISHER_OTHER_ITEMS}> <{if $show_category == 1}>: <{$item.category}><{/if}></strong></td>
            <{if $show_date_col == 1}>
                <td align="center" class="itemHead" width="25%">
                    <strong><{$smarty.const._MD_PUBLISHER_DATESUB}></strong></td>
            <{/if}>
            <{if $show_hits_col == 1}>
                <td align="center" class="itemHead" width="10%">
                    <strong><{$smarty.const._MD_PUBLISHER_HITS}></strong></td>
            <{/if}>
        </tr>
        <!-- Start item loop -->
        <{foreach item=item from=$items}>
            <tr>

                <td class="even" align="left">
                    <{if $show_mainimage == 1}>
                    <{if $item.item_image==''}>
                    <a href="<{$item.itemurl}>"><img src="<{$publisher_url}>/assets/images/default_image.jpg" alt="<{$item.title}>" title="<{$item.title}>" align="left" width="100" style="padding:5px">
                        <{else}>
                        <a href="<{$item.itemurl}>"><img src="<{$item.item_image}>" alt="<{$item.title}>" align="left" width="100" style="padding:5px"></a>
                        <{/if}>
                        <{/if}>
                        <{$item.titlelink}>

                        <{if $show_summary == 1}><br><{$item.summary}><br><{/if}>
                        <{if $show_readmore == 1}>
                            <{$item.more}>
                            <br>
                        <{/if}>
                        <small>
                            <{if $show_poster == 1}>
                                <br>
                                <{$smarty.const._MD_PUBLISHER_POSTER}>  <{$item.who}>
                            <{/if}>
                            <{if $show_commentlink == 1 && $item.cancomment && $item.comments != -1}>
                                | <{$item.comments}>
                            <{/if}>
                </td>
                <{if $show_date_col == 1}>
                    <td class="odd" align="left">
                        <div align="center"><{$item.datesub}></div>
                    </td>
                <{/if}>
                </small>
                <{if $show_hits_col == 1}>
                    <td class="odd" align="left">
                        <div align="center"><{$item.counter}></div>
                    </td>
                <{/if}>
            </tr>
        <{/foreach}> <!-- End item loop -->
    </table>
<{/if}>

<{include file='db:publisher_footer.tpl'}>

<script type="text/javascript">
    <!--//<![CDATA[
    $publisher(document).ready(function () {
        var options = {
            caption: 'permanent'
        };

        $publisher('#ppy3').popeye(options);
    });
    //]]>-->
</script>
