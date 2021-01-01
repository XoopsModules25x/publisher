<?php

declare(strict_types=1);

/**
 * This plugin will modify an url to remove and replace a specific
 * parameter while removing all others or keeping only certain ones.
 *
 * Note, any parameters not listed in the $remove_params_arr will
 * not be removed (they will be kept). To keep all parameters, pass
 * an empty string "" for the $remove_params_arr.
 *
 * @param string $url               The url to change (REQUIRED).
 * @param string $insert_param      The current parameter you wish to
 *                                  change or insert, optionally this
 *                                  could be a query string (REQUIRED).
 * @param null   $param_value       The value of the parameter you wish
 *                                  to change or insert (REQUIRED or
 *                                  OPTIONAL if $insert_param is a query
 *                                  string).
 *                                  $insert_param and $param_value can be
 *                                  multiple values by separating each
 *                                  pair by comma. The same number of
 *                                  values should be in each variable.
 * @param string $remove_params_arr A list of parameters (and values)
 *                                  which will be removed (OPTIONAL).
 *
 * @return string The modified url
 * @author  Ian Short (ian.short@live.co.uk - Made modifications to V1.86)
 *
 * @author  Jim Smith (admin@itlan.kicks-ass.org principal author)
 * @version 1.9 - Added remove duplicates from $url_arr. Replaced function implode_query with http_build_query. Removed code error checks, now handled by http_build_query.
 */
function smarty_modifier_rewrite_url($url, $insert_param, $param_value = null, $remove_params_arr = '')
{
    //parse $insert_param if it is a query string
    if (preg_match('/.+=([\w%,-])*/', $insert_param)) {
        parse_str($insert_param, $insert_arr);
        $insert_param = array_keys($insert_arr);
        $param_value  = array_values($insert_arr);
    }

    //split $url and parse into array
    if (preg_match('/\w+\.\w+/', $url)) {
        //assume full url
        $newurl_arr = parse_url($url);
        $newurl     = '';
        if (isset($newurl_arr['scheme'])) {
            $newurl = $newurl_arr['scheme'] . '://';
        }
        if (isset($newurl_arr['username']) && isset($newurl_arr['password'])) {
            $newurl .= $newurl_arr['username'] . ':' . $newurl_arr['password'] . '@';
        } elseif (isset($newurl_arr['username'])) {
            $newurl .= $newurl_arr['username'] . '@';
        }
        if (isset($newurl_arr['host'])) {
            $newurl .= $newurl_arr['host'];
        }
        if (isset($newurl_arr['port'])) {
            $newurl .= ':' . $newurl_arr['port'];
        }
        if (isset($newurl_arr['path'])) {
            $newurl .= $newurl_arr['path'];
        }
        $newurl .= '?';
        if (isset($newurl_arr['query'])) {
            parse_str($newurl_arr['query'], $url_arr);
        }
    } else {
        //assume just query string
        if (preg_match('/#/', $url)) {
            $temp_arr               = explode('#', $url);
            $newurl_arr['fragment'] = $temp_arr[1];
            $url                    = $temp_arr[0];
        }
        $newurl = '';
        parse_str($url, $url_arr);
    }

    //remove params from array
    if (isset($remove_params_arr) && ('' != $remove_params_arr)) {
        !is_array($remove_params_arr) ? $remove_params_arr = explode(',', $remove_params_arr) : '';
        foreach ($remove_params_arr as $param) {
            unset($url_arr[$param]);
        }
    }

    //add current param to array, params separated by semi-colon
    if (isset($insert_param) && ('' != $insert_param)) {
        !is_array($insert_param) ? $insert_param = explode(',', $insert_param) : '';
        !is_array($param_value) ? $param_value = explode(',', $param_value) : '';
        for ($i = 0, $size = count($param_value); $i < $size; ++$i) {
            if ('' != trim($param_value[$i])) {
                $url_arr[trim($insert_param[$i])] = trim($param_value[$i]);
            }
        }
    }

    // Remove any duplicate array elements
    $url_arr = array_unique($url_arr);

    //assemble the url string from the array
    $newurl .= http_build_query($url_arr, null, '&');

    //attach anchor fragment to end of url
    if (isset($newurl_arr['fragment'])) {
        $newurl .= '#' . $newurl_arr['fragment'];
    }

    return $newurl;
}

/*
from: https://www.smarty.net/forums/viewtopic.php?t=17202

Examples
You can use it to replace or add a single parameter (adds 'client_city'):
Code:
{$cur_page|cat:"?$query"|rewrite_url:"client_city":"Chicago":""}


You can use it to remove parameters (removes 'client_city'):
Code:
{$cur_page|cat:"?$query"|rewrite_url:"":"":"client_city"}


You can do both in the same operation (adds 'client_city', removes 'next'):
Code:
{$cur_page|cat:"?$query"|rewrite_url:"client_city":"Chicago":"next"}


You can, of course, use $smarty variables (inserts the 'client_city' with value stored in $client.city):
Code:
{$cur_page|cat:"?$query"|rewrite_url:"client_city":$client.city:""}


You can specify a list of parameters to add and to remove (this eliminates the need to make separate calls to rewrite_url if you want to add more than one parameter).
Inserts 'client_city', removes both 'next' and 'client_state':
Code:
{$cur_page|cat:"?$query"|rewrite_url:"client_city":"Chicago":"next,client_state"}

Inserts both 'client_city=Chicago' and 'client_state=Illinois', removes 'next':
Code:
{$cur_page|cat:"?$query"|rewrite_url:"client_city,client_state":"Chicago,Illinois":"next"}


Sometimes you need to call it twice. For instance if a variable contains the parameters you want to replace ($special_query). In this case you have to apply rewrite_url twice because the parameters do not exist in the URL until you apply the first rewrite_url. Then your second rewrite_url can modify your new URL:
Code:
{$cur_page|cat:"?$query"|rewrite_url:$special_query:"":""|rewrite_url:"":"":"next"}


If you already have the insert parameter as a parameter=value pair (inserts the pair 'client_city=Chicago'):
Code:
{$cur_page|cat:"?$query"|rewrite_url:"client_city=Chicago":"":""}


You can also use this inside PHP files by using the alternative syntax (removes 'next'):
Code:
$url = smarty_modifier_rewrite_url("$cur_page?$query","","","next");


I wrote this a while back and use it a lot in my templates. Much easier than trying to get all my URLs ready inside my PHP files. I can do it all dynamically in my templates!
*/
