<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=<{$xoops_charset}>">
    <meta http-equiv="content-language" content="<{$xoops_langcode}>">
    <meta name="robots" content="<{$xoops_meta_robots|default:''}>">
    <meta name="keywords" content="<{$xoops_meta_keywords|default:''}>">
    <meta name="description" content="<{$xoops_meta_description|default:''}>">
    <meta name="rating" content="<{$xoops_meta_rating|default:''}>">
    <meta name="author" content="<{$xoops_meta_author|default:''}>">
    <meta name="copyright" content="<{$xoops_meta_copyright|default:''}>">
    <meta name="generator" content="XOOPS">
    <title><{$printtitle}></title>
</head>

<body>

<{if !$doNotStartPrint|default:false}>
    <script for=window event=onload language="javascript">
        if (window.print)
            window.print();
    </script>
<{/if}>

<div id="pagelayer">
    <div style="text-align: center;">
        <img src="<{$printlogourl}>" border="0" alt="">
     <span style="padding-top: 5px; font-size: 18px; font-weight: bold;">
       <br><{$xoops_sitename}> (<{$xoops_url}>)</span><small><br><{$xoops_slogan}> </small> <hr>
        </div>
    
       <{if $printheader|default:'' != ''}>
           <div style="text-align: left; margin-top: 10px; border: 1px solid; padding: 2px;"><{$printheader}></div>
       <{/if}>

    <{$item.image}>

    <{if !$noTitle|default:false}>
        <h2><{$item.title}></h2>
    <{/if}>

    <{if !$noCategory|default:''}>
       <{$lang_category}> : <{$item.categoryname}>
    <{/if}>

    <{if $display_who_link|default:false}>
        | <{$lang_author_date}>
    <{/if}>

    <{if $item.body|default:''}>
        <div style="padding-top: 8px; text-align: justify;"><{$item.body}></div>
    <{/if}>

    <{if $itemfooter|default:false}>    
        <div style="text-align: center; font-weight: bold; border: 1px solid; padding: 2px; margin-top: 10px;"><{$itemfooter}></div>
    <{/if}> <br><br><br> <{if $indexfooter|default:false}>
        <div style="text-align: center; margin-top: 10px; border: 1px solid; padding: 2px;"><{$indexfooter}></div>
    <{/if}>

    <{if $smartPopup|default:false}>
        <div style="text-align: center;">
            <a href="javascript:window.close();"><{$smarty.const._MD_PUBLISHER_PRINT_CLOSE}></a>
        </div>
    <{/if}>
</div>


</body>

</html>
