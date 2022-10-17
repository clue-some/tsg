<?php

     /* doctype() -- DOCTYPE HTML 4.01 and XHTML 1.0/1.1
                   	 Transitional, Frameset and Strict
                   	 also performs content-type negotiation
        Phil:
        Added an 8th case: doctype ("xhtml", "svg", "1.1")
        includes scalable vector graphics within the xhtml.

		head() -- output the head element.
				  Configured for tracksuitgene.com
				 
		
    (c) Copyright 2004-2006, Douglas W. Clifton, all rights reserved.
        for more copyright information visit the following URI:
        http://loadaveragezero.com/info/copyright.php
        
    */

    $agent = get_browser(null, true);  // user agents don't play nice
    $_IE = ($agent['browser'] == 'IE');

    $media = array(
        'HTML'  => 'text/html',
        'XHTML' => 'application/xhtml+xml'
    );

    $charset = 'UTF-8';     // Unicode 8-bit character encoding

/*  Phil:
    $lang    = 'en-US'; */    // US English
	$lang    = 'en-GB';     // UK English

    function doctype ($doc = 'xhtml', $type = 'strict', $ver = '1.1') {

        global $media, $media_type;     // these we share with head(), etc.

        $doc  = strtoupper($doc);
        $type = strtolower($type);

        $avail = 'PUBLIC';  // or SYSTEM, but we're not going there yet

        // begin FPI

        $ISO = '-';     // W3C is not ISO registered [or IETF for that matter]
        $OID = 'W3C';   // unique owner ID
        $PTC = 'DTD';   // the public text class

        // as far as I know the PCL is always English

        $PCL = 'EN';
        $xlang = 'en';  // this you may want to vary if you're in different locale

        // DTDs are all under the Technical Reports (TR) branch @ W3C

        $URI  = 'http://www.w3.org/';

        $doc_top  = '<html';    // what comes after the DOCTYPE of course

        if ($doc == 'HTML') {

            $top = $doc;
            $media_type = $media[$doc];

            $PTD = $doc . ' 4.01';  // we're only supporting HTML 4.01 here

            switch ($type) {

                case 'frameset':

                    $PTD .= ' ' . ucfirst($type);
                    $URI .= 'TR/html4/frameset.dtd';
                    break;

                case 'transitional':

                    $PTD .= ' ' . ucfirst($type);
                    $URI .= 'TR/html4/loose.dtd';
                    break;

                case 'strict':
                default:

                    $URI .= 'TR/html4/strict.dtd';
            }
            $doc_top .= '>';   // no namespaces here
        }
        else {

            // must be xhtml then, but catch typos

            if ($doc != 'XHTML')  $doc = 'XHTML';

            $top = 'html';  // remember XML is lowercase
            $doc_top .= ' xmlns="http://www.w3.org/1999/xhtml" xml:lang="' . $xlang . '"';

            // return the correct media type header for this document,
            // but we should probably make sure the browser groks XML!

            // the W3C validator does not send the correct Accept header for this family of documents, sigh

            if (stristr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator')) $media_type = $media['XHTML'];
            else $media_type = (stristr($_SERVER['HTTP_ACCEPT'], $media['XHTML'])) ? $media['XHTML'] : $media['HTML'];

            // do NOT send XHTML 1.1 to browsers that don't accept application/xhtml+xml
            // see: labs/PHP/DOCTYPE.php#bug-fix for details and a link to the W3C XHTML
            // NOTES on this topic

            if ($media_type == $media['HTML'] and $ver == '1.1') $ver = '1.0';

			if ($type == 'svg') {

				$PTD = implode (' ', array ($doc, $ver, 'plus MathML 2.0 plus SVG 1.1'));
				$URI .= '2002/04/xhtml-math-svg/xhtml-math-svg.dtd';

			}
			else {
			
				if ($ver == '1.1') {
					$PTD = implode(' ', array($doc, $ver));
					$URI .= 'TR/xhtml11/DTD/xhtml11.dtd';
				}
				else {
					$PTD = implode(' ', array($doc, '1.0', ucfirst($type)));
					$URI .= 'TR/xhtml1/DTD/xhtml1-' . $type . '.dtd';
				}
				
				// for backwards compatibilty				
				$doc_top .= ' lang="' . $xlang . '"';
			}

            $doc_top .= '>';    // close root XHTML tag

            global $_IE, $charset, $lang;

            // send HTTP header

            header ('Content-type: ' . $media_type . '; charset=' . $charset);

            // send the XML declaration before the DOCTYPE, but this
            // will put IE into quirks mode which we don't want

            if (!$_IE) echo '<?xml version="1.0" encoding="' . $charset . '"?>' . "\n";
        }

        $FPI = implode('//', array($ISO, $OID, $PTC . ' ' . $PTD, $PCL));

        echo <<<_DTD

<!DOCTYPE $top
		  $avail
		 "$FPI"
		 "$URI">
$doc_top

_DTD;

    } // doctype()

	function head ($css = null, $js = null) {
	
		/* globals should be set before calling the function */	
		global $page,		/* <head> metadata for the current page set before call */
			   $media_type,	/* set in doctype() */
			   $charset,	/* set in doctype() */
			   $lang,		/* set in doctype() */
			   $ent,		/* maps entity chars to numeric equivs */
			   				/* Phil: leave until needed */
			   $copyright;	/* set here */
		
		$title = 'Tracksuit Gene';
		if ($page['title']) $title .= ' - ' . $page['title'];
		
		$no_description = 'Home site for Tracksuit Gene artists, music and information.';
		$description = ($page['description'] ? $page['description'] : $no_description);
		
		$default_keywords = 'tracksuitgene,music,electronic,digital,tracksuit,dance';
		$keywords = ($page['keywords'] ? $page['keywords'] . ',' . $default_keywords
									   : $default_keywords);
		
		// ODP/dmoz.org RDF -- a *very* small subset
		// Phil: set this up for electronic music categories
		// Phil: http://www.dmoz.org/about.html

		$categories = array(
			// Phil:
			// listed on dmoc.org
			'NETLABEL' => 'Business/Arts and Entertainment/Music/Labels/Specialty/Dance/Techno',
			'ARTIST' => 'Arts/Music/Styles/E/Electronic/Bands and Artists'
		);

		// Phil:
		$category = ($cat = $categories[strtoupper($page['category'])]) ? $cat
																		: $categories['NETLABEL'];
 		
 		$domain = 'tracksuitgene.com';
 		$author = 'Pred MacClinty';
 		
 		$year = date ('Y');
 		$copyright = 'Copyright &copy; ' . (($year <= 2010) ? $year : ('2010 - ' . $year)); 
		$copyright = implode (', ', array ($copyright, $domain, 'all rights reserved.'));

echo <<<_H
<head>
	<title>$title</title>
	<meta http-equiv="Content-type" content="$media_type; charset=$charset" />
	<meta http-equiv="Content-language" content="$lang" />
	<meta name="Resource-type" content="document" />
	<meta name="description" content="$description" />
	<meta name="keywords" content="$keywords" />
	<meta name="Category" content="$category" />
	<meta name="Distribution" content="Global" />
	<meta name="Rating" content="General" />
	<meta name="Robots" content="index,follow" />
	<meta name="Author" content="$author" />
	<meta name="Copyright" content="$copyright" />
	<link type="$media_type" rel="home" href="/" />
	<link type="image/x-icon" rel="shortcut icon" href="../img/favicon.ico" />
	<link type="image/gif" rel="icon" href="/img/favicon.gif" />
	<link type="text/css" rel="stylesheet" media="screen" href="../css/root.css" />
	<!--[if IE]>
		<link type="text/css" rel="stylesheet" media="screen" href="../css/blueprint/ie.css" />
	<![endif]-->

_H;

		$dir = array (
			'CSS' => '../css/',
			'JS'  => '../js/'		// Phil: none yet
		);
	
		// import additional CSS modules
	
		if ( is_array ($css) && count ($css) > 0 ) {
		
			echo "\t" . '<style type="text/css" media="screen">' . "\n";
			
			foreach ($css as $ss) {
				$url = $dir['CSS'] . $ss . '.css';
				echo "\t\t" . '@import url(' . $url . ');' . "\n";
			}
			
			echo "\t" . '</style>' . "\n";
		}
	
		// import javascript modules
	
		if ( is_array ($js) ) {
		
			foreach ($js as $s) {
			
				$src = $dir['JS'] . $s . '.js';
				echo "\t" . '<script type="text/javascript" src="' . $src . '"></script>' . "\n";
				
			}
		}
	
		echo '</head>' . "\n";

	} // head() 

    // pre.php
?>