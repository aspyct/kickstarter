<?php
/**
 * A few useful functions for common patterns.
 * @author Antoine d'Otreppe <a.dotreppe@aspyct.org> @aspyct
 */


/**
 * Returns the value at $key in the $array, or $default otherwise
 * 
 * @param array &$array
 * @param string $key
 * @param mixed $default
 * @return mixed 
 */
function array_get(array &$array, $key, $default=null) {
    if (array_key_exists($key, $array)) {
        return $array[$key];
    }
    else {
        return $default;
    }
}
