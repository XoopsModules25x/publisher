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

<{if $show_articles == true}>
    <table>
        <tr>
            <th><{$lang_articles}></th>
            <th align="center"><{$lang_actions}></th>
            <th align="center"><{$lang_views}></th>
            <th align="center"><{$lang_date}></th>
        </tr>
        <{foreach item=story from=$stories}>
        <tr class="<{cycle values=" even,odd"}>">
            <td><{$story.title}></td>
            <td align="center">
                <a href="<{$story.print_link}>" rel="nofollow"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/print.gif" border="0" alt="<{$lang_printer}>"></a>
                <a href="<{$story.mail_link}>" target="_top"><img src="<{$xoops_url}>/modules/<{$module_dirname}>/assets/images/links/friend.gif" border="0"
                                                                  alt="<{$smarty.const._MD_PUBLISHER_SENDSTORY}>"></a>
            </td>
            <td align="center"><{$story.counter}></td>
            <td align="center"><{$story.date}></td>
            </tr><{/foreach}>
    </table>
    <div><{$lang_storytotal}></div><{/if}>
