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

<{if $show_articles|default:false === true}>
    <table>
        <tr>
            <th><{$lang_articles}></th>
            <{if $showcategory|default:0 == 1}>
               <th align="center"><{$lang_category}></th>
            <{/if}>
            <{if $showposter|default:0 == 1}>
               <th align="center"><{$lang_author}></th>
             <{/if}>
            <{if $showhits|default:0 == 1}>
               <th align="center"><{$lang_views}></th>
             <{/if}>
            <{if $showdate|default:0 == 1}>
               <th align="center"><{$lang_date}></th>
             <{/if}>
            <{if $showpdfbutton|default:0 == 1 OR $showprintlink|default:0 == 1 OR $showemaillink|default:0 == 1}>
               <th align="center"><{$lang_actions}></th>
            <{/if}>
        </tr>


        <{foreach item=story from=$stories}>
            <tr class="<{cycle values=" even,odd"}>">
                <td>
                <{if $showmainimage|default:0 == 1}>
                <a href="<{$item.itemurl}>"><img src="<{$story.item_image}>" title="<{$story.cleantitle}>" alt="<{$story.cleantitle}>" align="left"></a><br>
                <{/if}>
                &nbsp;&nbsp;<{$story.title}>
                <{if $showsummary|default:0 == 1}><br>
                &nbsp;&nbsp;<{$story.summary}>
                <{/if}>
                <{if $showcomment|default:0 == 1 && $story.cancomment|default:false && $story.comment|default:0 != -1}>
                 <br>&nbsp;&nbsp;<small><{$story.comment}></small>
                 <{/if}>
                </td>
                <{if $showcategory|default:0 == 1}>
                <td align="center"><{$story.category}></td>
                 <{/if}>
                 <{if $showposter|default:0 == 1}>
                <td align="center"><{$story.author}></td>
                 <{/if}>
                 <{if $showhits|default:0 == 1}>
                <td align="center"><{$story.counter}></td>
                 <{/if}>
                 <{if $showdate|default:0 == 1}>
                <td align="center"><{$story.date}></td>
                 <{/if}>
                 <{if $showpdfbutton|default:0 == 1 OR $showprintlink|default:0 == 1 OR $showemaillink|default:0 == 1}>
                <td align="center">
                     <{if $showpdfbutton|default:0 == 1}>
                    <a href="<{$story.pdf_link}>" rel="nofollow"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/pdf.gif" border="0" alt="<{$lang_pdf}>"></a>
                     <{/if}>
                     <{if $showprintlink|default:0 == 1}>
                    <a href="<{$story.print_link}>" rel="nofollow"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/print.gif" border="0" alt="<{$lang_printer}>"></a>
                     <{/if}>
                     <{if $showemaillink|default:0 == 1}>
                    <a href="<{$story.mail_link}>" target="_top"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/friend.gif" border=" alt="<{$smarty.const._MD_PUBLISHER_SENDSTORY}>"></a>
                    <{/if}>
                 </td>
                   <{/if}>
            </tr>
        <{/foreach}>
    </table>
    <div><br><{$lang_storytotal}></div>
<{/if}>
