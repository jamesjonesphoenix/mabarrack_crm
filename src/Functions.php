<?php

namespace Phoenix;

/**
 * @param string       $location
 * @param array|string $args
 * @return bool
 */
function redirect(string $location = '', $args = [])
{
    if ( empty( $location ) ) {
        return false;
    }
    $argString = '';
    if ( !empty( $args ) ) {
        $argString = '?';
        if ( is_array( $args ) ) {

            $num_args = count( $args );
            $i = 0;
            foreach ( $args as $arg_name => $arg_value ) {
                $argString .= $arg_name . '=' . $arg_value;
                if ( ++$i !== $num_args ) {
                    $argString .= '&';
                }
            }
        } elseif ( is_string( $args ) ) {
            $argString .= $args;
        }
    }

    //clean location variable
    $location = ph_sanitize_redirect( $location );
    if ( strpos( $location, '.php' ) === false ) {
        $location .= '.php';
    }

    //redirect
    header( 'Location: ' . $location . $argString, true, 302 );
    exit();
}

/**
 * Taken verbatim from WordPress wp_sanitize_redirect() in pluggable.php
 *
 * Sanitizes a URL for use in a redirect.
 *
 * @param string $location The path to redirect to.
 * @return string Redirect-sanitized URL.
 * @since 2.3.0
 *
 */
function ph_sanitize_redirect(string $location = '')
{
    $regex = '/
		(
			(?: [\xC2-\xDF][\x80-\xBF]        # double-byte sequences   110xxxxx 10xxxxxx
			|   \xE0[\xA0-\xBF][\x80-\xBF]    # triple-byte sequences   1110xxxx 10xxxxxx * 2
			|   [\xE1-\xEC][\x80-\xBF]{2}
			|   \xED[\x80-\x9F][\x80-\xBF]
			|   [\xEE-\xEF][\x80-\xBF]{2}
			|   \xF0[\x90-\xBF][\x80-\xBF]{2} # four-byte sequences   11110xxx 10xxxxxx * 3
			|   [\xF1-\xF3][\x80-\xBF]{3}
			|   \xF4[\x80-\x8F][\x80-\xBF]{2}
		){1,40}                              # ...one or more times
		)/x';
    $location = preg_replace_callback( $regex, '_ph_sanitize_utf8_in_redirect', $location );
    $location = preg_replace( '|[^a-z0-9-~+_.?#=&;,/:%!*\[\]()@]|i', '', $location );
    $location = ph_kses_no_null( $location );

    // remove %0d and %0a from location
    $strip = ['%0d', '%0a', '%0D', '%0A'];
    return _ph_deep_replace( $strip, $location );
}

/**
 * Taken verbatim from WordPress _wp_sanitize_utf8_in_redirect() in pluggable.php
 *
 * URL encode UTF-8 characters in a URL.
 *
 * @param array $matches RegEx matches against the redirect location.
 * @return string URL-encoded version of the first RegEx match.
 * @see wp_sanitize_redirect()
 *
 * @ignore
 * @since 4.2.0
 * @access private
 *
 */
function _ph_sanitize_utf8_in_redirect($matches)
{
    return urlencode( $matches[0] );
}

/**
 * Taken Verbatim from WordPress wp_kses_no_null() in kses.php
 *
 * Removes any invalid control characters in $string.
 *
 * Also removes any instance of the '\0' string.
 *
 * @param string $string
 * @param array  $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
 * @return string
 * @since 1.0.0
 *
 */
function ph_kses_no_null($string, $options = null)
{
    if ( !isset( $options['slash_zero'] ) ) {
        $options = ['slash_zero' => 'remove'];
    }

    $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
    if ( 'remove' === $options['slash_zero'] ) {
        $string = preg_replace( '/\\\\+0+/', '', $string );
    }

    return $string;
}

/**
 * Taken verbatim from WordPress _deep_replace() in formatting.php
 *
 * Perform a deep string replace operation to ensure the values in $search are no longer present
 *
 * Repeats the replacement operation until it no longer replaces anything so as to remove "nested" values
 * e.g. $subject = '%0%0%0DDD', $search ='%0D', $result ='' rather than the '%0%0DD' that
 * str_replace would return
 *
 * @param string|array $search The value being searched for, otherwise known as the needle.
 *                              An array may be used to designate multiple needles.
 * @param string       $subject The string being searched and replaced on, otherwise known as the haystack.
 * @return string The string with the replaced svalues.
 * @since 2.8.1
 * @access private
 *
 */
function _ph_deep_replace($search, $subject)
{
    $subject = (string)$subject;

    $count = 1;
    while ( $count ) {
        $subject = str_replace( $search, '', $subject, $count );
    }

    return $subject;
}

/**
 * @param $number
 * @return mixed
 */
function phValidateID($number = null)
{
    if ( $number === null || $number === '' ) {
        return false;
    }
    if ( ((int)$number == $number && $number >= 0) || ctype_digit( $number ) ) {
        return (int)$number;
    }
    return false;
}

/**
 * @param bool $suffix
 * @return string
 */
function getScriptFilename($suffix = false)
{
    return basename( $_SERVER['SCRIPT_FILENAME'], $suffix );
}