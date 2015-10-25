<?php
    #
    #
    #
class port_badge {

    var $db;
    var $port;

    # use this function if you already have the port fetched from the database
    function port_badge(&$db, $port = NULL) {
        $this->db   = $db;
        $this->port = $port;
    }
    
    function _escapeText($text) {
        $text = str_replace('-', '--', $text);
        $text = str_replace('_', '__', $text);

        return $text;
    }
    
    function contents() {
        $contents = '';
        $port = $this->port;
        if (!empty($port)) {
            $PortVersion = freshports_PackageVersion($port->{'version'}, null, null);
            
            $link =  'https://img.shields.io/badge/FreeBSD_port-' . $this->_escapeText($PortVersion);
            $link .= empty($port->{'broken'}) ? '-blue' : '-red';
            $link .= '.png';
            
            $contents = $link;
        }
        
        return $contents;
    }

    function empty() {
        return = 'https://img.shields.io/badge/FreeBSD_port-not_found-lightgray.png';
    }
}

