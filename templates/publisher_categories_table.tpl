<div align="right"><{$navbar|default:''}></div>
<table border="0" width="90%" cellspacing="1" cellpadding="0" align="center" class="outer">
    <tr>
        <td align="left" class="itemHead"><strong><{$smarty.const._CO_PUBLISHER_CATEGORY}></strong></td>
        <{if $displayarticlescount|default:false}>
            <td align="left" width="70px" class="itemHead"><strong><{$smarty.const._MD_PUBLISHER_ITEMS}></strong></td>
        <{/if}>
        <{if $displaylastitem|default:0 == 1}>
            <td align="left" width="40%" class="itemHead"><strong><{$smarty.const._MD_PUBLISHER_LAST_SMARTITEM}></strong></td>
        <{/if}>
    </tr>

    <!-- Start categories loop --> 
    <{foreach item=category from=$categories}>
        <tr>
            <td class="even" align="left">
                <{if $category.image_path|default:''}>
                    <{if $isAdmin|default:0 == 1}>
                        <a href="<{$publisher_url}>/admin/category.php?op=mod&categoryid=<{$category.categoryid}>"><img src="<{$category.image_path}>" title="<{$smarty.const._MD_PUBLISHER_CATEGORY_EDIT}>" alt="<{$lang_editcategory|default:false}>"></a>&nbsp;
                    <{else}>
                        <img src="<{$category.image_path}>" alt="<{$category.name}>">&nbsp;
                    <{/if}><!-- End with a selected image path -->
                <{else}><!-- Start without a selected image path -->
                    <{if $isAdmin|default:0 == 1}>
                        <a href="<{$publisher_url}>/admin/category.php?op=mod&categoryid=<{$category.categoryid}>"><img src="<{$publisher_url}>/assets/images/links/cat.gif" title="<{$lang_editcategory|default:false}>" alt="<{$lang_editcategory|default:false}>"></a>&nbsp;
                    <{else}>
                        <img src="<{$publisher_url}>/assets/images/links/cat.gif" alt="<{$category.name}>">&nbsp;
                    <{/if}><!-- End without a selected image path -->
                <{/if}>

                <{if $selected_category|default:0 == $category.categoryid|default:0}>
                    <strong><{$category.name}></strong>
                <{else}>
                    <strong><{$category.categorylink}></strong>
                <{/if}>

                <br>
                <span class="publisher_category_dsc"><{$category.description}></span>
            </td>
            <{if $displayarticlescount|default:false}>
                <td class="even" align="center">
                    <{if $category.total|default:0 != 0}> <{$category.total}> <{/if}>
                </td>
            <{/if}>
            <{if $displaylastitem|default:0 == 1}>
                <td class="even" align="right"><{$category.last_title_link|default:''}></td>
            <{/if}>
        </tr>
        <{if $category.subcats|default:false}>
            <{foreach item=subcat from=$category.subcats}>
                <tr>
                    <td class="odd" align="left">
                        <div style="padding-left: 10px;">
                            <{if $subcat.image_path|default:false}>
                                <{if $isAdmin|default:0 == 1}>
                                    <a href="<{$publisher_url}>/admin/category.php?op=mod&categoryid=<{$subcat.categoryid}>"><img src="<{$subcat.image_path}>" title="<{$lang_editcategory|default:false}>" 
                                                                                                                              alt="<{$lang_editcategory|default:false}>" alt="<{$lang_editcategory|default:false}>"></a>
                                    &nbsp;<{$subcat.categorylink}>
                                <{else}>
                                    <img src="<{$subcat.image_path}>" title="<{$lang_editcategory|default:false}>" alt="<{$lang_editcategory|default:false}>">
                                    &nbsp;<{$subcat.categorylink}>
                                <{/if}><!-- End with a selected image path -->
                            <{else}><!-- Start without a selected image path -->
                                <{if $isAdmin|default:0 == 1}>
                                    <a href="<{$publisher_url}>/admin/category.php?op=mod&categoryid=<{$subcat.categoryid}>"><img src="<{$publisher_url}>/assets/images/links/subcat.gif"
                                                                                                                              title="<{$lang_editcategory|default:false}>" alt="<{$lang_editcategory|default:false}>"
                                                                                                                              alt="<{$lang_editcategory|default:false}>"></a>
                                    &nbsp;<{$subcat.categorylink}>
                                <{else}>
                                    <img src="<{$publisher_url}>/assets/images/links/subcat.gif" title="<{$lang_editcategory|default:false}>" alt="<{$lang_editcategory|default:false}>">
                                    &nbsp;<{$subcat.categorylink}>
                                <{/if}>
                                <{if $displaysubcatdsc|default:0 == 1}>
                                    <span class="publisher_category_dsc"><{$subcat.description}></span>
                                <{/if}>
                            <{/if}>
                        </div>
                    </td>
                    <{if $displayarticlescount|default:false}>
                        <td class="odd" align="center"><{$subcat.total}></td>
                    <{/if}>
                    <{if $displaylastitem|default:0 == 1}>
                        <td class="odd" align="right"><{$subcat.last_title_link|default:''}></td>
                    <{/if}>
                </tr>
            <{/foreach}>
        <{/if}>
    <{/foreach}> <!-- End categories loop -->
</table>
<div align="right"><{$navbar|default:''}></div><!-- End sub categories loop -->
