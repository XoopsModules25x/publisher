<small>
    <{if $rating_5stars|default:false}>
        <div class="blog_ratingblock">
            <div id="unit_long<{$item.id}>">
                <div id="unit_ul<{$item.id}>" class="blog_unit-rating">
                    <div class="blog_current-rating" style="width:<{$item.rating.size}>;"><{$item.rating.text}></div>
                    <div>
                        <a class="blog_r1-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING1}>" rel="nofollow">1</a>
                    </div>
                    <div>
                        <a class="blog_r2-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=2&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING2}>" rel="nofollow">2</a>
                    </div>
                    <div>
                        <a class="blog_r3-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=3&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING3}>" rel="nofollow">3</a>
                    </div>
                    <div>
                        <a class="blog_r4-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=4&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING4}>" rel="nofollow">4</a>
                    </div>
                    <div>
                        <a class="blog_r5-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=5&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING5}>" rel="nofollow">5</a>
                    </div>
                </div>
                <div>
                    <{$item.rating.text}>
                </div>
            </div>
        </div>
    <{/if}>
    <{if $rating_10stars|default:false}>
        <div class="blog_ratingblock">
            <div id="unit_long<{$item.id}>">
                <div id="unit_ul<{$item.id}>" class="blog_unit-rating-10">
                    <div class="blog_current-rating" style="width:<{$item.rating.size}>;"><{$item.rating.text}></div>
                    <div>
                        <a class="blog_r1-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_1}>" rel="nofollow">1</a>
                    </div>
                    <div>
                        <a class="blog_r2-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=2&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_2}>" rel="nofollow">2</a>
                    </div>
                    <div>
                        <a class="blog_r3-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=3&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_3}>" rel="nofollow">3</a>
                    </div>
                    <div>
                        <a class="blog_r4-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=4&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_4}>" rel="nofollow">4</a>
                    </div>
                    <div>
                        <a class="blog_r5-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=5&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_5}>" rel="nofollow">5</a>
                    </div>
                    <div>
                        <a class="blog_r6-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=6&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_6}>" rel="nofollow">6</a>
                    </div>
                    <div>
                        <a class="blog_r7-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=7&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_7}>" rel="nofollow">7</a>
                    </div>
                    <div>
                        <a class="blog_r8-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=8&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_8}>" rel="nofollow">8</a>
                    </div>
                    <div>
                        <a class="blog_r9-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=9&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_9}>" rel="nofollow">9</a>
                    </div>
                    <div>
                        <a class="blog_r10-unit rater" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=10&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_10_10}>" rel="nofollow">10</a>
                    </div>
                </div>
                <div>
                    <{$item.rating.text}>
                </div>
            </div>
        </div>
    <{/if}>
    <{if $rating_10num|default:false}>
        <div class="blog_ratingblock">
            <div id="unit_long<{$item.id}>">
                <div id="unit_ul<{$item.id}>" class="blog_unit-rating-10numeric">
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=1}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=1&amp;source=1" rel="nofollow">1</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=2}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=2&amp;source=1" rel="nofollow">2</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=3}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=3&amp;source=1" rel="nofollow">3</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=4}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=4&amp;source=1" rel="nofollow">4</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=5}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=5&amp;source=1" rel="nofollow">5</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=6}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=6&amp;source=1" rel="nofollow">6</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=7}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=7&amp;source=1" rel="nofollow">7</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=8}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=8&amp;source=1" rel="nofollow">8</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=9}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=9&amp;source=1" rel="nofollow">9</a>
                    <a class="blog-rater-numeric <{if $item.rating.avg_rate_value >=10}>blog-rater-numeric-active<{/if}>" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=10&amp;source=1" rel="nofollow">10</a>
                </div>
                <div class='left'>
                    <{$item.rating.text}>
                </div>
            </div>
        </div>
    <{/if}>
    <{if $rating_likes|default:false}>
        <div class="blog_ratingblock">
<{*            <a class="blog-rate-like" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_LIKE}>" rel="nofollow">*}>
<{*                <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/like.png' alt='<{$smarty.const._MA_PUBLISHER_RATING_LIKE}>' title='<{$smarty.const._MA_PUBLISHER_RATING_LIKE}>'>(<{$item.rating.likes}>)</a>*}>


            <a class="blog-rate-dislike" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=-1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_DISLIKE}>" rel="nofollow">
                    <span class="btn btn-danger  btn-xs">
                <{$item.rating.dislikes}> <i class="fa fa-thumbs-down fa-lg"></i>
                    </span>
            </a>

            <a class="blog-rate-like" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_LIKE}>" rel="nofollow">
                  <span class="btn btn-success  btn-xs">
                 <i class="fa fa-thumbs-up fa-lg"></i> <{$item.rating.likes}>
                    </span>
            </a>



<{*            <button  class="btn btn-danger btn-xs"><i class="fa fa fa-thumbs-down fa-lg"></i></button>*}>

<{*            <button  class="btn btn-danger  btn-xs">*}>
<{*                (<{$item.rating.dislikes}>) <i class="fa fa-thumbs-down fa-lg"></i>*}>
<{*            </button>*}>

<{*            <button  class="btn btn-success  btn-xs">*}>
<{*                <i class="fa fa-thumbs-up fa-lg"></i> (<{$item.rating.likes}>)*}>
<{*            </button>*}>




<{*            <a class="blog-rate-dislike" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=-1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING_DISLIKE}>" rel="nofollow">*}>
<{*                <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/dislike.png' alt='<{$smarty.const._MA_PUBLISHER_RATING_DISLIKE}>' title='<{$smarty.const._MA_PUBLISHER_RATING_DISLIKE}>'> (<{$item.rating.dislikes}>)</a>*}>

        </div>
    <{/if}>



    <{if $rating_reaction|default:false}>
        <div class="blog_ratingblock">

                <a class="blog-rate-reaction" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=1&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING1}>" rel="nofollow">
                    <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/like20.png' alt='<{$smarty.const._MA_PUBLISHER_REACTION_LIKE}>' title='<{$smarty.const._MA_PUBLISHER_REACTION_LIKE}>'>(<{$item.rating.likes}>)</a>


                <a class="blog-rate-reaction" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=2&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING2}>" rel="nofollow">
                    <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/love20.png' alt='<{$smarty.const._MA_PUBLISHER_REACTION_LOVE}>' title='<{$smarty.const._MA_PUBLISHER_REACTION_LOVE}>'>(<{$item.rating.love}>)</a>

                <a class="blog-rate-reaction" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=3&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING3}>" rel="nofollow">
                    <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/smile20.png' alt='<{$smarty.const._MA_PUBLISHER_REACTION_HAHA}>' title='<{$smarty.const._MA_PUBLISHER_REACTION_HAHA}>'>(<{$item.rating.smile}>)</a>

                <a class="blog-rate-reaction" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=4&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING4}>" rel="nofollow">
                    <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/wow20.png' alt='<{$smarty.const._MA_PUBLISHER_REACTION_WOW}>' title='<{$smarty.const._MA_PUBLISHER_REACTION_WOW}>'>(<{$item.rating.wow}>)</a>

                <a class="blog-rate-reactionr" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=5&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING5}>" rel="nofollow">
                    <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/sad20.png' alt='<{$smarty.const._MA_PUBLISHER_REACTION_SAD}>' title='<{$smarty.const._MA_PUBLISHER_REACTION_SAD}>'>(<{$item.rating.sad}>)</a>

                <a class="blog-rate-reaction" href="ratelike.php?op=save&amp;<{$itemid}>=<{$item.id}>&rating=5&amp;source=1" title="<{$smarty.const._MA_PUBLISHER_RATING5}>" rel="nofollow">
                    <img class='blog-btn-icon' src='<{$blog_icon_url_16}>/angry20.png' alt='<{$smarty.const._MA_PUBLISHER_REACTION_ANGRY}>' title='<{$smarty.const._MA_PUBLISHER_REACTION_ANGRY}>'>(<{$item.rating.angry}>)</a>

        </div>
    <{/if}>


</small>
