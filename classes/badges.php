<?php
    #
    #
    #
class port_badge {

    const BADGE_SITE            = 'https://img.shields.io/badge';
    const BADGE_LABEL           = 'FreeBSD_port';
    const BADGE_NOT_FOUND       = 'not_found';
    const BADGE_COLOR_NORMAL    = 'blue';
    const BADGE_COLOR_BROKEN    = 'red';
    const BADGE_COLOR_NOT_FOUND = 'lightgray';
    const BADGE_IMAGE_TYPE      = '.png';

    var $db;
    var $port;

    # object creation
    function port_badge(&$db, $port = NULL) {
        $this->db   = $db;
        $this->port = $port;
    }

    # used for 'escaping' text to the the format required by badge creation site
    function _escapeText($text) {
        # underscores & dashes need to be doubled
        $text = str_replace('-', '--', $text);
        $text = str_replace('_', '__', $text);

        return $text;
    }
    
    function url() {
        $url  = '';
        $port = $this->port;
        if (!empty($port)) {

            # at present, we assume you've already fetched this port from the db
            # future versions will need to act on cache and/or fetch from db as required.

            # grab the properly formatted version for this package
            $PortVersion = freshports_PackageVersion($port->{'version'}, null, null);
            
            # We are aiming for something of the form:
            # https://img.shields.io/badge/FreeBSD_port-7.2.0-blue.png
            
            $url =  self::BADGE_SITE . '/' . self::BADGE_LABEL . '-' . $this->_escapeText($PortVersion) . '-';
            $url .= empty($port->{'broken'}) ? self::BADGE_COLOR_NORMAL : self::BADGE_COLOR_BROKEN;
            $url .= self::BADGE_IMAGE_TYPE;
        }
        
        return $url;
    }

    function not_found() {
        return self::BADGE_SITE . '/' . self::BADGE_LABEL . '-' . self::BADGE_NOT_FOUND . '-' . self::BADGE_COLOR_NOT_FOUND . self::BADGE_IMAGE_TYPE;
    }
}
