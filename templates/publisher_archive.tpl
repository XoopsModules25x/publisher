<table>
    <tr>
        <th><{$lang_newsarchives}></th>
    </tr>
    <{foreach item=year from=$years}>
        <tr class="even">
            <td><{$year.number}> (<{$year.articlesYearCount}>)</td>
        </tr>
        <tr class="odd">
            <td>
                <{foreach item=month from=$year.months}>
                    <a href="./archive.php?year=<{$year.number}>&month=<{$month.number}>"><{$month.string}> (<{$month.articlesMonthCount}>) </a>
                    &nbsp;
                <{/foreach}>
            </td>
        </tr>
    <{/foreach}>
</table>

<{if $show_articles === true}>
    <table>
        <tr>
            <th><{$lang_articles}></th>
            <{if $showcategory == 1}>
               <th align="center"><{$lang_category}></th>
            <{/if}>
            <{if $showposter == 1}>
               <th align="center"><{$lang_author}></th>
             <{/if}>
            <{if $showhits == 1}>
               <th align="center"><{$lang_views}></th>
             <{/if}>
            <{if $showdate == 1}>
               <th align="center"><{$lang_date}></th>
             <{/if}>
            <{if $showpdfbutton == 1 OR $showprintlink == 1 OR $showemaillink == 1}>
               <th align="center"><{$lang_actions}></th>
            <{/if}>
        </tr>


        <{foreach item=story from=$stories}>
            <tr class="<{cycle values=" even,odd"}>">
                <td>
                <{if $showmainimage == 1}>
                <a href="<{$item.itemurl}>"><img src="<{$story.item_image}>" title="<{$story.cleantitle}>" alt="<{$story.cleantitle}>" align="left"></a><br>
                <{/if}>
                &nbsp;&nbsp;<{$story.title}>
                <{if $showsummary == 1}><br>
                &nbsp;&nbsp;<{$story.summary}>
                <{/if}>
                <{if $showcomment == 1 && $story.cancomment && $story.comment != -1}>
                 <br>&nbsp;&nbsp;<small><{$story.comment}></small>
                 <{/if}>
                </td>
                <{if $showcategory == 1}>
                <td align="center"><{$story.category}></td>
                 <{/if}>
                 <{if $showposter == 1}>
                <td align="center"><{$story.author}></td>
                 <{/if}>
                 <{if $showhits == 1}>
                <td align="center"><{$story.counter}></td>
                 <{/if}>
                 <{if $showdate == 1}>
                <td align="center"><{$story.date}></td>
                 <{/if}>
                 <{if $showpdfbutton == 1 OR $showprintlink == 1 OR $showemaillink == 1}>
                <td align="center">
                     <{if $showpdfbutton == 1}>
                    <a href="<{$story.pdf_link}>" rel="nofollow"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/pdf.gif" border="0" alt="<{$lang_pdf}>"></a>
                     <{/if}>
                     <{if $showprintlink == 1}>
                    <a href="<{$story.print_link}>" rel="nofollow"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/print.gif" border="0" alt="<{$lang_printer}>"></a>
                     <{/if}>
                     <{if $showemaillink == 1}>
                    <a href="<{$story.mail_link}>" target="_top"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/friend.gif" border=" alt="<{$smarty.const._MD_PUBLISHER_SENDSTORY}>"></a>
                    <{/if}>
                 </td>
                   <{/if}>
            </tr>
        <{/foreach}>
    </table>
    <div><br><{$lang_storytotal}></div>
<{/if}>
