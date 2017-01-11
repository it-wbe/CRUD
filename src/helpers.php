<?php

// http://stackoverflow.com/questions/28290332/best-practices-for-custom-helpers-on-laravel-5

/*
	BEGIN some string utils
*/
if (!function_exists('after')) {
    function after($this, $inthat, $else_empty = false)
    {
        if (!is_bool(strpos($inthat, $this)))
            return substr($inthat, strpos($inthat, $this) + strlen($this));
        else
            if (!$else_empty) return $inthat;
    }
}

if (!function_exists('after_last')) {
    function after_last($this, $inthat, $else_empty = false)
    {
        if (!is_bool(strrevpos($inthat, $this)))
            return substr($inthat, strrevpos($inthat, $this) + strlen($this));
        else
            if (!$else_empty) return $inthat;
    }
}

if (!function_exists('before')) {
    function before($this, $inthat)
    {
        return substr($inthat, 0, strpos($inthat, $this));
    }
}

if (!function_exists('before_last')) {
    function before_last($this, $inthat, $else_empty = false)
    {
        $p = strrevpos($inthat, $this);
        if ($p !== false)
            return substr($inthat, 0, $p);
        else
            if (!$else_empty) return $inthat;
    }
}

if (!function_exists('between')) {
    function between($this, $that, $inthat)
    {
        return before($that, after($this, $inthat));
    }
}

if (!function_exists('between_last')) {
    function between_last($this, $that, $inthat)
    {
        return after_last($this, before_last($that, $inthat));
    }
}

// use strrevpos function in case your php version does not include it

if (!function_exists('strrevpos')) {
    function strrevpos($instr, $needle)
    {
        $rev_pos = strpos(strrev($instr), strrev($needle));
        if ($rev_pos === false) return false;
        else return strlen($instr) - $rev_pos - strlen($needle);
    }
}

if (!function_exists('_is')) {
    function _is(&$attr, $val = true)
    {
        return (isset($attr) && ($attr == $val));
    }
}

if (!function_exists('bind_string')) {
    function bind_string($mask, $params = [])
    {
        //$params = array_unshift([$game->team_1_name, $game->team_2_name])
        // $mask = str_replace('t1')

        //В (type?очних|останніх 5|останніх 10|останніх 20) матчах %team% зіграли %match_cnt% (%match_all%?в %over_under% з %match_all%)

        $r = $mask;
        preg_match_all('/\%(.*?)\%/', $r, $vars);
        foreach ($vars[0] as $k => $v) {
            if (array_key_exists($vars[1][$k], $params))
                $val = $params[$vars[1][$k]];
            else
                $val = $vars[0][$k];
            $r = str_replace($v, $val, $r);
        }

        preg_match_all('/\{\*(.*?)\*\}/', $r, $vars);
        foreach ($vars[0] as $k => $v) {
            $op = explode('?', trim($v, '{*}'));
            if ((!$op) || (count($op) !== 2)) continue;
            $if_var_name = $op[0];

            $switches = explode('|', $op[1]);
            if (!$switches) continue;

            if (array_key_exists($if_var_name, $params))
                $if_var_value = $params[$if_var_name];
            else
                $if_var_value = '';

            if (count($switches) !== 1) {
                if (is_numeric($if_var_value)) {
                    if (array_key_exists($if_var_value, $switches))
                        $val = $switches[$if_var_value];
                    else
                        $val = '';
                } else {
                    if ($if_var_value)
                        $val = $switches[0];
                    else
                        $val = $switches[1];
                }
            } else {
                if ($if_var_value)
                    $val = $switches[0];
                else
                    $val = '';
            }

            $r = str_replace($v, $val, $r);
        }


        //die($r);

        //foreach ($ifs as $v)
        //    $mask = preg_match_all('(?<=\%).*?(?=\%)', $mask, $vars);

        return $r;
    }
}

if (!function_exists('is_admin_panel')) {
    function is_admin_panel()
    {
        return starts_with(\Request::path(), 'admin/') || ends_with(\Request::path(), '/admin');
    }
}

if (!function_exists('get_current_lang')) {
    function get_current_lang()
    {
        return session((is_admin_panel() ? 'admin_lang_id' : 'lang_id'));
    }
}