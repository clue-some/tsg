<?php

     /* doctype() -- DOCTYPE HTML 4.01 and XHTML 1.0/1.1
                   	 Transitional, Frameset and Strict
                   	 also performs content-type negotiation
        Phil:
        Added an 8th case: doctype ("xhtml", "svg", "1.1")
        will include scalable vector graphics within the xhtml.

		head() -- output the head element.
				  Configured for tracksuitgene.com
				  
		body() -- output the body element.
				  Configured for tracksuitgene.com
				 
		This code modified from:
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
    
/*----------------------------------------------------------------------------------
	
	head (array of css filenames, array of js filenames)

 */

	function head ($css = null, $js = null) {
	
		/* globals should be set before calling the function */	
		global $page,		/* <head> metadata for the current page set before call */
			   $media_type,	/* set in doctype() */
			   $charset,	/* set in doctype() */
			   $lang,		/* set in doctype() */
			   $ent,		/* maps entity chars to numeric equivs */
			   				/* Phil: leave until needed */
			   $copyright;	/* set here */
		
		$title = 'Tracksuitgene';
		if ($page['title']) $title .= ' - ' . $page['title'];
		
		$no_description = 'Home site for Tracksuitgene artists, music and information.';
		$description = ($page['description'] ? $page['description'] : $no_description);
		
		$default_keywords = 'tracksuitgene,music,electronic,digital,tracksuit,dance,informal,play,rest,leisure';
		$keywords = ($page['keywords'] ? $page['keywords'] . ',' . $default_keywords
									   : $default_keywords);
		
		// ODP/dmoz.org RDF -- a *very* small subset
		// Phil: set this up for electronic music categories
		// Phil: http://www.dmoz.org/about.html

		$categories = array(
			// Phil: listed on dmoc.org
			'NETLABEL' => 'Business/Arts and Entertainment/Music/Labels/Specialty/Dance/Techno',
			'ARTIST' => 'Arts/Music/Styles/E/Electronic/Bands and Artists'
		);

		$category = ($cat = $categories[strtoupper($page['category'])]) ? $cat
																		: $categories['NETLABEL'];
 		
 		$domain = 'tracksuitgene.com';
 		$author = 'Pred Maclinty';
 		
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
				echo "\t";
?>				
				<script language="javascript" type="text/javascript" src="<?php echo $src?>"></script>
<?php
				echo "\n";
				
			}
		}

		echo '</head>' . "\n";

	} // head() 

/*----------------------------------------------------------------------------------
	
	body (username, size of tracksuitgene graphic to display)

 */

	function body ($user = '[not logged in]') {
	
		/* globals should be set before calling the function */	
		global $copyright;	/* set in head() */
		
		$graphicsvg = "../img/TSGbeta0.3.svg";
		$graphicpng = "../img/TSG%20colour%202.png";
		
echo <<<_EOT

<body id="canvas">

	<div id="presentation">
		<div id="header">
		
			<p>TracksuitGenE</p>
		
		</div>
		<div id="user">

			<p>User: $user</p>

		</div>
	</div>
	<div id="centergraphic">
	
		<object id="tracksuitgene_svgobject"
				title="The Tracksuit Gene graphic."
				data="$graphicsvg"
				standby="Loading graphic image..."
				type="image/svg+xml"
				height="100%"
				width="100%">

			<!-- belt and buckle - only displayed if container tag ignored -->
			<img alt="Front page graphic"
				 src="$graphicpng"
				 id="tracksuitgene_pngimg"
				 height="100%"
				 width="100%" />
				 
		</object>

<!-- Created with Inkscape (http://www.inkscape.org/) 
<svg id="svg2" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" style="enable-background:new;" xmlns="http://www.w3.org/2000/svg" height="100%" width="100%" version="1.1" xmlns:cc="http://creativecommons.org/ns#" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1047.6521 918.70105" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<style id="style4" type="text/css">#a5263 path:hover {fill:#1e476c;}
#a28690 path:hover {fill:#652548;}</style>

	<defs id="defs8">
		<linearGradient id="linearGradient4962" y2="696" gradientUnits="userSpaceOnUse" y1="725.7" x2="622.3" x1="472.3">
			<stop id="stop11" style="stop-color:#efdac2;" offset="0"/>
			<stop id="stop13" style="stop-color:#efdac2;stop-opacity:0;" offset="1"/>
		</linearGradient>
		<linearGradient id="linearGradient4970" y2="746.9" gradientUnits="userSpaceOnUse" y1="796.4" x2="264.5" x1="370.5">
			<stop id="stop16" style="stop-color:#efdac2;" offset="0"/>
			<stop id="stop18" style="stop-color:#efdac2;stop-opacity:0;" offset="1"/>
		</linearGradient>
	</defs>
	<metadata id="metadata1439">
		<rdf:RDF>
			<cc:Work rdf:about="">
				<dc:format>image/svg+xml</dc:format>
				<dc:type rdf:resource="http://purl.org/dc/dcmitype/StillImage"/>
				<dc:title/>
			</cc:Work>
		</rdf:RDF>
	</metadata>
	<g id="g1441" transform="translate(147.34713,-86.79984)">
		<path id="path1443" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#696969;" d="m447,305.2c-72.5,17.86-76.72,4.933-113.1-8.654-11.5,27.9-131.2,530.4-148,592.2,34.22,43.55,167.7,152.8,318.9,50.83-19.13-62.25-55.81-604-57.71-634.4z"/>
	</g>
	<g id="g1445" transform="translate(147.34713,-86.79984)">
		<path id="path1447" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#e7c7a4;" d="m456.1,345.3c-0.4838,1.124-0.5335,3.552,1.11,4.992,16.65,14.59,27.12,23.48,46.51,48.36,16.76,21.5,34.45,28.71,59.37,51.9,1.658,1.542,21.42,14.87,26.07,15.71-6.505,1.115-11.06,8.595-14.42,12.23-84.52,91.51-84.79,399.9-68.72,404.5-8.204-0.4539-12.88,13.78-40.57,37.99-22.69,19.84-71.66,29.14-80.38,26.18-0.7795-0.2645-4.133-0.0586-4.222-0.0586,2.589-3.839-1.05-50.01-3.645-80.41-1.239-14.52-1.91-31.27,0-45.71,2.252-17.04,0.0491-115.5,11.43-176.4,13.94-74.7,7.36-89.5,10.71-123.6,7.667-77.87-21.2-138-29.28-140.5,0,0,37.21-19.67,64.82-30.89,18.18-7.389,21.69-5.643,21.21-4.278z"/>
	</g>
	<g id="g1449" transform="translate(147.34713,-86.79984)">
		<path id="path1451" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#e7c7a4;" d="m300.6,330.6c0.5716-2.031,1.356,1.32-2.02,3.135-6.7,3.602-25.55,4.049-57.43,19.42-23.82,11.48-38.47,18.69-55.87,28.24-5.479,3.008-9.064,4.724-11.5,5.693-4.287,1.705-5.025,1.099-6.279,1.15,4.129,6.771,4.79,10.79,5.757,11.67,35.22,31.9,22.84,106-28.16,145,33.86,81.1,41.17,275.1,36.56,277.7,27.12,7.566,52.58,36.94,54.29,38.64,51.5,50.91,103.9,58.8,110.9,58.93,4.422,0.0807,8.571,1.678,8.571,1.678,1.663-44.81,0.3899-38.45,0.505-50.76,0.3727-39.86,12.43-140.7,7.006-181-8.012-59.4-15.37-78.03-27.88-114.4-36.28-105.6,0.7103-174.3,23.15-208.6,8.008-12.24-30.45-26.83-57.58-36.41z"/>
	</g>
	<g id="g1453" transform="translate(147.34713,-86.79984)">
		<g id="g1455" style="enable-background:new;fill:#eae9e8;">
			<path id="path1457" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#eae9e8;" d="m361.4,980.6,1.998,10.77c1.825,9.938,5.553,13.91-1.345,12.98-1.802-0.2419-3.601-0.4727-6.263-0.2802-2.379,0.1714-2.843,0.513-3.266,0.094-1.912-1.898,2.948-10.23,3.496-12.61l2.536-10.96h2.843zm-1.498,11.24c-1.22-0.09-6.273,10.01-3.343,9.742,2.323-0.2131,4.597,0.4339,5.61-0.778,1.039-1.245-0.611-4.04-0.999-5.509-0.4337-1.642-0.3342-1.562-1.076-3.299-0.0439-0.1028-0.1108-0.1496-0.1921-0.1556z"/>
			<rect id="rect1459" style="enable-background:new;stroke:#000000;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:1.96050084;fill:#eae9e8;" transform="scale(-1,1)" rx="1.247" ry="1.247" height="7.825" width="8.343" y="972.4" x="-363.9"/>
			<rect id="rect1461" style="enable-background:new;stroke:#000000;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:1.96050084;fill:#eae9e8;" transform="scale(-1,1)" rx="1.247" ry="1.247" height="7.825" width="8.343" y="964.5" x="-363.9"/>
		</g>
		<g id="g1463" style="fill:#eae9e8;">
			<g id="g1465" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62866062,-0.04643672,-0.07366543,0.99728301,646.44681,-112.54251)">
				<path id="path1467" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1469" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1471" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6285735,-0.04760144,-0.07551309,0.99714481,647.64603,-105.62761)">
				<path id="path1473" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1475" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1477" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62871068,-0.04575402,-0.07258241,0.99736242,644.52407,-100.09031)">
				<path id="path1479" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1481" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1483" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62911929,-0.03974254,-0.06304603,0.99801062,635.44038,-96.347159)">
				<path id="path1485" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1487" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1489" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62894254,-0.04244796,-0.06733781,0.99773023,638.91506,-88.757636)">
				<path id="path1491" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1493" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1495" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62843425,-0.04940591,-0.07837563,0.9969239,648.45012,-79.213763)">
				<path id="path1497" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1499" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1501" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62904274,-0.04093638,-0.0649399,0.99788918,635.8156,-76.643463)">
				<path id="path1503" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1505" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1507" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62880665,-0.04441549,-0.07045903,0.99751467,640.39451,-68.714173)">
				<path id="path1509" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1511" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1513" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62908888,-0.04022095,-0.06380496,0.99796238,633.93243,-64.170372)">
				<path id="path1515" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1517" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1519" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62922717,-0.03799625,-0.06027579,0.99818176,630.32792,-58.721943)">
				<path id="path1521" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1523" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1525" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62947237,-0.03369097,-0.05344606,0.99857074,623.74809,-54.144761)">
				<path id="path1527" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1529" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1531" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62991648,-0.02399533,-0.03806527,0.99927525,609.42613,-51.702293)">
				<path id="path1533" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1535" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1537" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63016158,-0.0163378,-0.02591766,0.99966408,598.10508,-48.25947)">
				<path id="path1539" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1541" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1543" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63031175,0.00881154,0.01397828,0.9999023,561.33582,-50.684667)">
				<path id="path1545" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1547" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1549" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62973062,0.02845872,0.04514582,0.99898041,532.5911,-50.165801)">
				<path id="path1551" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1553" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1555" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037334,0,0,1,574.57924,-34.95767)">
				<path id="path1557" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1559" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1561" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63032448,-0.00784848,-0.01245052,0.99992249,586.00305,-25.770416)">
				<path id="path1563" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1565" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1567" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63017506,-0.01580957,-0.0250797,0.99968546,597.47482,-16.415597)">
				<path id="path1569" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1571" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1573" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63014511,-0.01696152,-0.02690711,0.99963794,598.97804,-9.5748806)">
				<path id="path1575" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1577" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1579" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62999606,-0.02180618,-0.03459248,0.9994015,605.81644,-1.2931681)">
				<path id="path1581" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1583" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1585" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63001273,-0.02131908,-0.03381976,0.99942794,604.89251,4.9410084)">
				<path id="path1587" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1589" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1591" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037275,-8.6306935e-4,-0.00136914,0.99999906,574.98643,3.8068838)">
				<path id="path1593" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1595" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1597" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037334,0,0,1,573.71991,9.9255)">
				<path id="path1599" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1601" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1603" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037334,0,0,1,573.71991,16.36289)">
				<path id="path1605" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1607" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1609" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037334,0,0,1,573.71991,22.77796)">
				<path id="path1611" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1613" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1615" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037334,0,0,1,573.71991,29.20977)">
				<path id="path1617" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1619" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1621" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037334,0,0,1,573.71991,35.636)">
				<path id="path1623" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1625" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1627" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61917536,0.11828958,0.18765004,0.98223595,398.13525,-295.5752)">
				<path id="path1629" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1631" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1633" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62110088,0.10772303,0.17088767,0.98529052,415.40961,-288.46094)">
				<path id="path1635" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1637" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1639" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62166567,0.10441432,0.16563886,0.98618648,421.51662,-281.8327)">
				<path id="path1641" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1643" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1645" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62265634,0.09833429,0.15599373,0.98775805,431.77746,-274.8682)">
				<path id="path1647" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1649" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1651" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62321481,0.09473039,0.15027664,0.98864399,438.22758,-268.10169)">
				<path id="path1653" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1655" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1657" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62393139,0.08988974,0.14259763,0.98978074,446.49473,-261.13475)">
				<path id="path1659" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1661" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1663" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6249351,0.08262368,0.13107102,0.99137298,458.34598,-253.74345)">
				<path id="path1665" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1667" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1669" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62786419,0.05618813,0.08913468,0.99601958,498.71991,-242.61533)">
				<path id="path1671" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1673" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1675" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6292677,0.03731912,0.05920161,0.99824605,527.2739,-231.80828)">
				<path id="path1677" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1679" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1681" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63014343,0.01702367,0.02700569,0.99963528,557.53501,-219.7396)">
				<path id="path1683" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1685" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1687" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63036315,0.00358324,0.00568431,0.99998384,577.37365,-209.04265)">
				<path id="path1689" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1691" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1693" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63036165,-0.00383928,-0.00609049,0.99998145,588.22998,-200.06819)">
				<path id="path1695" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1697" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1699" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6302608,-0.01191118,-0.01889544,0.99982147,599.9161,-190.73713)">
				<path id="path1701" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1703" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1705" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63010603,-0.01835585,-0.02911901,0.99957595,609.13227,-181.88685)">
				<path id="path1707" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1709" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1711" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6298975,-0.02448863,-0.03884782,0.99924514,617.81694,-173.0728)">
				<path id="path1713" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1715" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1717" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62970951,-0.02892197,-0.0458807,0.99894693,623.96707,-164.85787)">
				<path id="path1719" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1721" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1723" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62953895,-0.0324232,-0.05143492,0.99867635,628.71569,-156.99708)">
				<path id="path1725" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1727" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1729" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62951715,-0.03284372,-0.05210202,0.99864177,628.9943,-150.40204)">
				<path id="path1731" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1733" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1735" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62922402,-0.0380483,-0.06035836,0.99817677,636.13695,-141.78187)">
				<path id="path1737" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1739" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1741" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62911929,-0.03974254,-0.06304603,0.99801062,638.18578,-134.65248)">
				<path id="path1743" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1745" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1747" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62896233,-0.0421537,-0.06687101,0.99776163,641.24654,-127.22209)">
				<path id="path1749" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1751" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1753" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62838673,-0.05000662,-0.07932857,0.99684852,652.06082,-117.28269)">
				<path id="path1755" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1757" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1759" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.55776099,-0.29372304,-0.46595092,0.88481057,967.71914,-324.73714)">
				<path id="path1761" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1763" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1765" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.56504617,-0.27945192,-0.44331177,0.89636749,946.43846,-334.60332)">
				<path id="path1767" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1769" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1771" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.57284486,-0.26309564,-0.4173648,0.90873903,922.42758,-345.82278)">
				<path id="path1773" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1775" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1777" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.58061106,-0.24548186,-0.38942297,0.92105903,896.74609,-357.36214)">
				<path id="path1779" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1781" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1783" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.58535752,-0.2339383,-0.37111071,0.92858863,879.05141,-362.32698)">
				<path id="path1785" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1787" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1789" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.5952441,-0.20749703,-0.3291653,0.94427232,841.52969,-379.86267)">
				<path id="path1791" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1793" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1795" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.59948415,-0.19490845,-0.30919526,0.95099858,822.51455,-384.29605)">
				<path id="path1797" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1799" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1801" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60733433,-0.1688655,-0.26788173,0.9634518,785.25905,-398.56687)">
				<path id="path1803" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1805" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1807" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60983289,-0.159607,-0.2531944,0.96741542,770.90155,-399.19692)">
				<path id="path1809" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1811" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1813" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61694703,-0.12940985,-0.20529081,0.97870102,727.69512,-413.71006)">
				<path id="path1815" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1817" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1819" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6186642,-0.12093451,-0.19184585,0.98142507,714.61338,-412.84879)">
				<path id="path1821" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1823" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1825" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62211111,0.10172664,0.16137522,0.98689312,389.52164,-417.6918)">
				<path id="path1827" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1829" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1831" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61978202,0.1150687,0.18254056,0.98319833,370.30558,-412.52539)">
				<path id="path1833" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1835" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1837" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61675876,0.13030416,0.2067095,0.97840236,348.22811,-407.02996)">
				<path id="path1839" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1841" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1843" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61401134,0.14269069,0.22635901,0.97404395,330.56258,-400.98891)">
				<path id="path1845" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1847" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1849" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61019913,0.15820105,0.25096406,0.96799641,308.10502,-394.50048)">
				<path id="path1851" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1853" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1855" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.6069812,0.17013046,0.26988842,0.96289161,291.20515,-387.74646)">
				<path id="path1857" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1859" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1861" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60295718,0.18388364,0.29170592,0.95650806,271.50344,-380.4423)">
				<path id="path1863" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1865" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1867" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.59832514,0.19843783,0.31479413,0.94915997,250.57915,-372.53999)">
				<path id="path1869" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1871" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1873" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.59629297,0.20446328,0.32435268,0.94593622,243.11989,-365.56622)">
				<path id="path1875" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1877" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1879" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.59597438,0.20539006,0.32582289,0.94543082,243.7494,-359.33545)">
				<path id="path1881" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1883" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1885" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.5958408,0.20577729,0.32643717,0.9452189,245.22832,-353.20219)">
				<path id="path1887" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1889" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1891" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.59701964,0.20233166,0.32097115,0.94708897,252.74266,-347.70193)">
				<path id="path1893" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1895" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1897" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.59987067,0.1937156,0.30730297,0.95161174,268.34301,-342.83398)">
				<path id="path1899" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1901" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1903" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60069689,0.19113817,0.30321423,0.95292242,274.36317,-337.0457)">
				<path id="path1905" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1907" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1909" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60476364,0.17785242,0.28213823,0.95937376,297.06195,-332.31816)">
				<path id="path1911" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1913" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1915" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60722926,0.16924295,0.2684805,0.96328512,312.26004,-326.81259)">
				<path id="path1917" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1919" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1921" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61063299,0.15651803,0.24829418,0.96868467,333.70193,-321.22925)">
				<path id="path1923" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1925" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1927" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.612542,0.14887192,0.23616468,0.97171305,347.09141,-315.18252)">
				<path id="path1929" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1931" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1933" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61579721,0.13477518,0.21380215,0.97687699,370.26904,-308.856)">
				<path id="path1935" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1937" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1939" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.617195,0.128222,0.20340645,0.97909439,381.67043,-302.37288)">
				<path id="path1941" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1943" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1945" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62328552,-0.09426404,-0.14953685,0.98875615,676.09352,-422.44514)">
				<path id="path1947" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1949" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1951" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62511039,-0.0812868,-0.12895025,0.99165106,656.85239,-423.20248)">
				<path id="path1953" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1955" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1957" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62768532,-0.05815226,-0.09225051,0.99573583,623.19896,-428.57624)">
				<path id="path1959" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1961" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1963" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62911715,-0.03977643,-0.0630998,0.99800723,596.31555,-430.56609)">
				<path id="path1965" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1967" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1969" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62997161,-0.02250139,-0.03569534,0.99936271,571.02412,-431.32015)">
				<path id="path1971" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1973" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1975" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63037166,-0.0014536,-0.00230594,0.99999734,540.2559,-432.6964)">
				<path id="path1977" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1979" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1981" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.63020889,0.0143978,0.02284012,0.99973913,517.07762,-431.51455)">
				<path id="path1983" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1985" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1987" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62955808,0.03204949,0.05084208,0.9987067,491.27321,-430.18912)">
				<path id="path1989" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1991" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1993" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62849726,0.04859776,0.07709362,0.99702386,467.11779,-427.91921)">
				<path id="path1995" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path1997" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g1999" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62646207,0.07011297,0.11122452,0.99379531,435.5516,-425.9463)">
				<path id="path2001" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2003" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2005" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62502712,0.08192461,0.12996205,0.99151897,418.5571,-421.52424)">
				<path id="path2007" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2009" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<path id="path2011" style="stroke-linejoin:miter;stroke-width:0.82204723;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#eae9e8;" d="m374,319.5c-0.0365-1.224,2.362-5.379-0.4277-2.402"/>
			<g id="g2013" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62919196,-0.03857486,-0.06119368,0.99812591,643.48145,-588.13622)">
				<path id="path2015" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2017" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2019" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.58829279,-0.2264556,-0.35924044,0.93324504,895.71692,-420.10008)">
				<path id="path2021" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2023" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2025" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.52992296,-0.34139742,-0.54157973,0.84064939,1040.9893,-289.7668)">
				<path id="path2027" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2029" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2031" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.5336148,-0.33559766,-0.53237921,0.84650598,1030.3095,-291.73987)">
				<path id="path2033" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2035" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2037" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.5441779,-0.31818385,-0.50475461,0.86326288,1005.1262,-307.67027)">
				<path id="path2039" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2041" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2043" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.54802671,-0.31150806,-0.49416439,0.86936848,993.46905,-310.02054)">
				<path id="path2045" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2047" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2049" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61337544,-0.14539987,-0.23065676,0.97303518,789.30523,-496.73156)">
				<path id="path2051" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2053" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2055" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.60789179,-0.16684758,-0.26468058,0.96433614,817.2009,-475.17908)">
				<path id="path2057" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2059" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2061" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61749667,-0.12676124,-0.20108915,0.97957295,764.88083,-515.39725)">
				<g id="g2063" style="fill:#eae9e8;" transform="translate(0.05354553,-0.00902179)">
					<path id="path2065" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
					<path id="path2067" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
				</g>
			</g>
			<g id="g2069" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62185236,-0.10329661,-0.16386578,0.98648265,733.19465,-536.13473)">
				<path id="path2071" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2073" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2075" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.62223981,-0.10093646,-0.16012172,0.98709728,730.90828,-543.84326)">
				<path id="path2077" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2079" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2081" style="enable-background:new;fill:#eae9e8;" transform="matrix(-0.61337544,-0.14539987,-0.23065676,0.97303518,793.52205,-522.18679)">
				<g id="g2083" style="fill:#eae9e8;" transform="matrix(0.99206226,0.07926799,-0.19948128,0.99206226,186.20297,-19.721308)">
					<path id="path2085" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
					<path id="path2087" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
				</g>
			</g>
		</g>
	</g>
	<g id="g2089" transform="translate(147.34713,-86.79984)">
		<g id="g2091" style="stroke-width:2.06220484;stroke-miterlimit:4;enable-background:new;stroke-dasharray:none;fill:#cac7e3;" transform="matrix(-1,0,0,1,710.76542,18)">
			<path id="path2093" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#cac7e3;" d="m338.9,294.9c-5.025-1.681,7.197,59.97,11.07,56.43,3.883-3.556,23.07-21.23,38.93-27.5,16.93-6.695,21.47-8.567,22.5-12.14,1.345-4.668-9.628-50.72-37.62-31.2-7.134,4.978-27.03,17.04-34.88,14.41z"/>
			<path id="path2095" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m389.7,276c16.39,9.296,21.32,36.76,21.32,36.81"/>
			<path id="path2097" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m348.9,294.2c4.29,36.28,11.05,48.18,11.07,48.21"/>
			<path id="path2099" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m359.3,289.5c8.901,47.17,11.37,44.7,11.43,44.64"/>
			<path id="path2101" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m386.8,275.6c14.74,9.787,20.22,28,22.88,38.74"/>
			<path id="path2103" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m383.4,276c18.75,15.26,22.31,40.49,22.32,40.53"/>
			<path id="path2105" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m379.6,277.6c6.613,4.546,20.01,40.28,19.82,41.96"/>
			<path id="path2107" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m373.6,281c2.224,1.902,4.555,8.197,7.682,18.14,3.447,10.96,9.282,24,9.282,24"/>
			<path id="path2109" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m367.8,284.9c6.328,27.32,13.39,42.53,13.39,42.5"/>
		</g>
	</g>
	<g id="g2111" transform="translate(147.34713,-86.79984)">
		<g id="g2113" style="fill:#eae9e8;">
			<g id="g2115" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.60994464,-0.15917941,0.25251608,0.96759269,-76.68941,-509.33847)">
				<path id="path2117" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2119" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2121" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61088618,-0.15552694,0.24672195,0.96908631,-70.05806,-505.75532)">
				<path id="path2123" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2125" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2127" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61512023,-0.13783196,0.21865132,0.97580306,-44.13303,-511.79277)">
				<path id="path2129" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2131" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2133" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62244296,-0.09967602,0.1581222,0.98741955,10.38673,-529.30303)">
				<path id="path2135" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2137" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2139" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62870112,-0.04588515,0.07279043,0.99734726,87.65462,-550.51243)">
				<path id="path2141" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2143" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2145" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63031753,0.00838834,-0.01330693,0.99991146,166.67883,-565.10376)">
				<path id="path2147" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2149" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2151" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62435592,0.08689205,-0.13784221,0.9904542,283.07795,-576.96209)">
				<path id="path2153" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2155" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2157" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61603174,0.13369909,-0.21209508,0.97724903,353.30243,-574.61933)">
				<path id="path2159" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2161" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2163" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61102584,-0.15497733,0.24585007,0.96930787,-67.40671,-462.96397)">
				<path id="path2165" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2167" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2169" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.51142204,-0.36853501,0.58462975,0.81130024,-343.61908,-238.67853)">
				<path id="path2171" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2173" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2175" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.54079216,-0.32390489,0.51383025,0.85789186,-284.8943,-291.58547)">
				<path id="path2177" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2179" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2181" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.56239319,-0.28475331,0.4517217,0.89215891,-231.93488,-330.98747)">
				<path id="path2183" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2185" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2187" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.57658194,-0.25480152,0.40420732,0.91466739,-190.23669,-356.20724)">
				<path id="path2189" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2191" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2193" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.586667,-0.23063471,0.36587002,0.93066595,-155.87486,-373.31911)">
				<path id="path2195" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2197" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2199" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.59468731,-0.20908744,0.33168827,0.94338905,-124.87006,-386.4241)">
				<path id="path2201" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2203" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2205" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.60083769,-0.19069509,0.30251135,0.95314578,-98.04181,-395.63552)">
				<path id="path2207" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2209" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2211" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.60632416,-0.17245742,0.27357982,0.9618493,-71.40057,-403.77522)">
				<path id="path2213" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2215" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2217" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61082361,-0.15577246,0.24711144,0.96898707,-46.8652,-409.87812)">
				<path id="path2219" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2221" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2223" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61426229,-0.14160645,0.22463903,0.97444205,-25.80476,-413.51951)">
				<path id="path2225" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2227" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2229" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.61786577,-0.12494977,0.19821551,0.98015846,-1.32379,-418.23138)">
				<path id="path2231" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2233" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2235" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62134312,-0.1063169,0.16865703,0.9856748,25.9131,-423.3922)">
				<path id="path2237" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2239" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2241" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62316534,-0.09505523,0.15079195,0.98856551,42.7851,-423.57442)">
				<path id="path2243" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2245" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2247" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62552735,-0.07801336,0.12375739,0.9923125,67.78241,-426.51902)">
				<path id="path2249" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2251" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2253" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62739499,-0.06120514,0.09709348,0.99527526,92.44202,-428.6308)">
				<path id="path2255" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2257" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2259" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62845412,-0.04915248,0.0779736,0.99695542,110.26546,-427.90661)">
				<path id="path2261" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2263" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2265" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62955738,-0.03206338,0.05086411,0.99870558,135.30006,-428.97579)">
				<path id="path2267" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2269" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2271" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63009941,-0.01858163,0.02947718,0.99956545,155.08911,-427.97211)">
				<path id="path2273" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2275" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2277" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63036871,-0.00241711,0.00383441,0.99999266,178.74356,-427.48889)">
				<path id="path2279" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2281" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2283" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63023596,0.01315985,-0.02087628,0.99978207,201.51895,-426.20287)">
				<path id="path2285" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2287" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2289" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.629842,0.0258768,-0.04104997,0.9991571,220.05375,-423.56294)">
				<path id="path2291" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2293" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2295" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62874255,0.04531401,-0.07188441,0.99741298,248.49519,-422.20507)">
				<path id="path2297" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2299" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2301" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62773687,0.05759316,-0.09136358,0.9958176,266.27697,-418.53915)">
				<path id="path2303" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2305" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2307" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62844506,0.04926824,-0.07815724,0.99694105,253.31818,-410.32818)">
				<path id="path2309" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2311" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2313" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62971349,0.02883513,-0.04574295,0.99895324,222.59472,-398.78387)">
				<path id="path2315" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2317" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2319" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63022335,0.01375071,-0.02181359,0.99976206,200.13658,-387.93979)">
				<path id="path2321" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2323" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2325" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63034063,0.00642168,-0.0101871,0.9999481,189.27317,-379.19232)">
				<path id="path2327" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2329" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2331" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,179.84289,-370.61145)">
				<path id="path2333" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2335" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2337" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63033258,0.00716839,-0.01137166,0.99993535,190.29455,-366.58419)">
				<path id="path2339" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2341" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2343" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63026364,0.01176002,-0.01865564,0.99982598,196.9352,-361.63235)">
				<path id="path2345" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2347" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2349" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62785478,0.025521,-0.04055258,0.99765453,217.759,-357.94141)">
				<path id="path2351" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2353" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2355" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62942824,0.03450564,-0.05473841,0.99850073,229.99815,-355.39332)">
				<path id="path2357" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2359" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2361" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62845803,0.0491025,-0.07789431,0.99696163,251.25786,-352.56386)">
				<path id="path2363" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2365" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2367" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62773242,0.05764164,-0.09144047,0.99581055,263.45428,-348.0254)">
				<path id="path2369" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2371" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2373" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62673363,0.0676425,-0.10730545,0.9942261,277.7904,-343.62082)">
				<path id="path2375" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2377" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2379" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62564825,0.0770377,-0.12220964,0.99250431,291.16787,-338.87022)">
				<path id="path2381" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2383" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2385" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62465056,0.08474804,-0.13444102,0.9909216,301.96502,-333.68109)">
				<path id="path2387" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2389" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2391" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62335187,0.09382424,-0.14883917,0.98886141,314.79283,-328.53003)">
				<path id="path2393" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2395" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2397" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.6230084,0.09607853,-0.15241528,0.98831654,317.23602,-322.4396)">
				<path id="path2399" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2401" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2403" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62168625,0.10429166,-0.16544427,0.98621914,328.6836,-316.99104)">
				<path id="path2405" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2407" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2409" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62091856,0.10876896,-0.17254689,0.9850013,334.41166,-311.06434)">
				<path id="path2411" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2413" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2415" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62021382,0.11271809,-0.17881164,0.98388333,339.29723,-305.05631)">
				<path id="path2417" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2419" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2421" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62046608,0.11132114,-0.17659557,0.9842835,336.02753,-298.64256)">
				<path id="path2423" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2425" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2427" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62357009,0.09236281,-0.14652081,0.98920759,306.19944,-290.34801)">
				<path id="path2429" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2431" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2433" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62318746,0.09491018,-0.15056186,0.98860059,309.10593,-284.31448)">
				<path id="path2435" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2437" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2439" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62578339,0.07593213,-0.12045581,0.99271869,279.59269,-275.26142)">
				<path id="path2441" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2443" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2445" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.6261027,0.07325268,-0.11620524,0.99322523,274.80558,-268.43616)">
				<path id="path2447" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2449" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2451" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62712936,0.06386952,-0.10132015,0.99485387,260.03398,-260.3358)">
				<path id="path2453" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2455" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2457" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62789146,0.05588261,-0.08865002,0.99606284,247.48098,-252.31445)">
				<path id="path2459" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2461" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2463" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62807118,0.05382513,-0.08538612,0.99634794,243.85262,-245.46787)">
				<path id="path2465" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2467" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2469" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62856962,0.0476527,-0.07559441,0.99713865,234.14211,-237.68914)">
				<path id="path2471" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2473" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2475" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62875992,0.04507228,-0.07150093,0.99744053,229.83637,-230.67321)">
				<path id="path2477" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2479" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2481" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62927967,0.03711672,-0.05888054,0.99826504,217.60461,-222.30092)">
				<path id="path2483" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2485" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2487" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62923498,0.03786664,-0.06007018,0.99819415,218.33652,-216.09324)">
				<path id="path2489" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2491" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2493" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62959624,0.03129096,-0.04963877,0.99876723,208.23929,-207.95674)">
				<path id="path2495" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2497" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2499" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62973721,0.02831228,-0.04491351,0.99899087,203.53181,-200.7196)">
				<path id="path2501" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2503" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2505" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62980658,0.02672464,-0.04239494,0.99910092,200.90757,-193.85456)">
				<path id="path2507" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2509" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2511" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62996099,0.02279687,-0.03616408,0.99934586,194.85673,-186.30736)">
				<path id="path2513" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2515" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2517" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63002809,0.02086036,-0.03309207,0.99945231,191.78052,-179.31167)">
				<path id="path2519" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2521" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2523" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63006633,0.01967158,-0.03120624,0.99951296,189.8182,-172.54382)">
				<path id="path2525" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2527" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2529" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.630177,0.01573203,-0.02495669,0.99968854,183.83952,-164.94996)">
				<path id="path2531" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2533" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2535" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63020948,0.01437211,-0.02279936,0.99974006,181.68554,-158.10457)">
				<path id="path2537" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2539" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2541" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.6301709,0.01597458,-0.02534146,0.99967885,183.88825,-152.17163)">
				<path id="path2543" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2545" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2547" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63019437,0.01502005,-0.02382723,0.99971609,182.32454,-145.45216)">
				<path id="path2549" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2551" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2553" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63023849,0.01303822,-0.02068333,0.99978608,179.26254,-138.41596)">
				<path id="path2555" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2557" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2559" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63024808,0.01256606,-0.01993432,0.99980129,178.43634,-131.839)">
				<path id="path2561" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2563" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2565" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63009449,0.01874772,-0.02974066,0.99955764,187.36794,-127.30241)">
				<path id="path2567" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2569" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2571" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63008687,0.01900219,-0.03014435,0.99954555,187.5474,-120.94925)">
				<path id="path2573" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2575" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2577" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62994724,0.02317395,-0.03676226,0.99932405,193.48653,-115.75186)">
				<path id="path2579" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2581" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2583" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62665582,0.06835957,-0.10844299,0.99410267,260.23878,-120.04898)">
				<path id="path2585" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2587" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2589" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62728626,0.06230968,-0.09884567,0.99510278,250.51793,-112.52302)">
				<path id="path2591" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2593" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2595" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62973586,0.02834239,-0.04496128,0.99898872,199.54062,-98.078061)">
				<path id="path2597" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2599" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2601" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63034669,0.00579586,-0.00919432,0.99995773,166.18012,-84.840622)">
				<path id="path2603" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2605" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2607" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63030417,-0.00933779,0.01481311,0.99989028,144.07525,-73.17286)">
				<path id="path2609" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2611" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2613" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62975276,-0.02796437,0.0443616,0.99901554,117.20949,-59.584005)">
				<path id="path2615" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2617" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2619" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62917709,-0.03881664,0.06157723,0.99810232,101.87325,-48.612456)">
				<path id="path2621" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2623" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2625" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62813663,-0.05305579,0.08416566,0.99645177,81.87214,-35.817957)">
				<path id="path2627" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2629" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2631" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62802456,-0.05436635,0.08624468,0.99627399,80.54877,-28.808755)">
				<path id="path2633" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2635" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2637" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62862675,-0.04689309,0.0743894,0.99722927,91.78683,-25.849025)">
				<path id="path2639" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2641" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2643" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62820775,-0.052207,0.08281918,0.9965646,84.66755,-17.011423)">
				<path id="path2645" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2647" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2649" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62850758,-0.04846411,0.0768816,0.99704023,90.5553,-12.32081)">
				<path id="path2651" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2653" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2655" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62874432,-0.04528944,0.07184542,0.99741578,95.59757,-7.3426032)">
				<path id="path2657" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2659" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2661" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62859044,-0.04737729,0.07515752,0.99717167,93.06112,-0.02912314)">
				<path id="path2663" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2665" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2667" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62923553,-0.03785766,0.06005594,0.99819502,107.19844,2.1610523)">
				<path id="path2669" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2671" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2673" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62915245,-0.03921415,0.06220782,0.99806322,105.64092,9.1638026)">
				<path id="path2675" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2677" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2679" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62971521,-0.02879763,0.04568344,0.99895597,121.02345,11.144887)">
				<path id="path2681" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2683" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2685" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.62987291,-0.025113,0.0398383,0.99920613,126.63139,16.030937)">
				<path id="path2687" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2689" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2691" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63029808,-0.00974044,0.01545186,0.99988061,149.14732,16.568238)">
				<path id="path2693" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2695" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2697" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63013293,0.01740776,-0.027615,0.99961863,188.87125,13.910229)">
				<path id="path2699" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2701" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2703" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.24534,25.92554)">
				<path id="path2705" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2707" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2709" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.23586,32.37016)">
				<path id="path2711" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2713" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2715" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.23586,38.80751)">
				<path id="path2717" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2719" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2721" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.23586,45.24487)">
				<path id="path2723" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2725" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2727" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.23586,51.68222)">
				<path id="path2729" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2731" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2733" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.23586,58.11958)">
				<path id="path2735" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2737" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<g id="g2739" style="enable-background:new;fill:#eae9e8;" transform="matrix(0.63037334,4.1495795e-6,-6.5825336e-6,1,163.23586,64.55693)">
				<path id="path2741" style="stroke-dasharray:none;stroke:#000000;stroke-width:0.34972411;stroke-miterlimit:4;fill:#eae9e8;" d="m335.2,921.7c-1.694,0-1.66,3.25,0.0354,3.25h2.35c0.2835,0,0.5-0.2165,0.5-0.5v-0.2812h5.938c0.2882,0,0.5312-0.2118,0.5312-0.5v-0.7188c0-0.2882-0.243-0.5-0.5312-0.5h-5.938v-0.25c0-0.2835-0.2165-0.5-0.5-0.5z"/>
				<path id="path2743" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;fill:#eae9e8;stroke-width:1.03737998px;" d="m342.5,918.8v6.437"/>
			</g>
			<path id="path2745" style="stroke-linejoin:miter;stroke-width:0.82204723;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#eae9e8;" d="m363.9,326.9c-3.969-1.938-1.344,0.2812-1.094,2.469"/>
		</g>
	</g>
	<g id="g2747" transform="translate(147.34713,-86.79984)">
		<g id="g2749" style="stroke-width:2.06220484;stroke-miterlimit:4;enable-background:new;stroke-dasharray:none;fill:#cac7e3;" transform="matrix(-1,0,0,1,710.76542,-10)">
			<g id="g2751" style="stroke-miterlimit:4;stroke-width:2.06220484;stroke-dasharray:none;fill:#cac7e3;">
				<g id="g2753" style="stroke-miterlimit:4;stroke-width:2.06220484;stroke-dasharray:none;fill:#cac7e3;">
					<path id="path2755" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#cac7e3;" d="m323.2,957.4c4.81-1.621,8.871-0.4004,10.64,2.659,2.839,4.921,3.107,32.24,0.1698,44.02-1.477,5.921-9.078,1.686-13.08,1.303-62.52-5.985-103-44.95-111.1-53.03-3.934-3.934-9.665-4.157-9.762-11.65-0.2523-19.51-3.706-41.77,1.314-46.19,10.06-8.856,29.01,23.48,63.23,44.75,10.72,6.662,52.89,20.06,58.6,18.14z"/>
					<path id="path2757" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m321.5,957.4c6.309,10.15-1.867,41.2,0.2525,48.23"/>
					<path id="path2759" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m306.9,955.1c2.789,23.17-3.283,48.21-3.283,48.21"/>
					<path id="path2761" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m292.6,950.2c0.8081,46.15-5.052,48.51-5.303,48.61"/>
					<path id="path2763" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m278.1,945.8c-1.889,17.09-1.93,40.24-6.818,47.47"/>
					<path id="path2765" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m265.7,939.6c-3.15,22.35-6.245,42.88-8.838,46.53"/>
					<path id="path2767" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m254.6,932.9c-4.934,12.11-6.636,30.23-8.838,47.42"/>
					<path id="path2769" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m244.6,924.3c-5.702,14.05-5.849,33.03-8.586,49.71"/>
					<path id="path2771" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m236.2,917.3c-6.259,16.37-5.072,34.32-7.361,51.53"/>
					<path id="path2773" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m227.8,909.2c-5.087,14.97-4.366,35.86-6.061,54.29"/>
					<path id="path2775" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m220.3,902.4c-3.418,6.431-3.373,36.51-4.998,55.18"/>
					<path id="path2777" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m214.1,897c-2.617,7.914-1.887,37.59-2.778,56.73"/>
					<path id="path2779" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m207.4,950.3c0.4103-18.7-1.416-40.99,1.263-56.06"/>
					<path id="path2781" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m203.9,947.8c0.1738-18.99-2.71-47.81,1.263-54.6"/>
					<path id="path2783" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m201.8,946.3c0.1523-17.83-3.024-44.42,0.7576-52.7"/>
				</g>
			</g>
			<g id="g2785" style="stroke-miterlimit:4;stroke-width:2.06220484;stroke-dasharray:none;fill:#cac7e3;">
				<g id="g2787" style="stroke-miterlimit:4;stroke-width:2.06220484;stroke-dasharray:none;fill:#cac7e3;">
					<path id="path2789" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#cac7e3;" d="m471.8,871.3c30.2-29.46,62.44-48.73,63.57-31.78,3.42,51.44-2.402,57.09-7.778,58.19-4.603,0.9411,1.759-1.555-9.472,6.745-93.94,69.43-134.8,75.37-142.4,75.77-7.327,0.3846-15.79,4.851-19.21,4.982-8.959,0.3426-9.166-35.49-4.911-52.33,0.9997-3.958,9.196-2.615,12.12-3.868,54.38-23.29,68.68-19.29,108.1-57.7z"/>
					<path id="path2791" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m504.1,844.6c5.382,0.8193,2.503,43.74,3.354,67.64"/>
					<path id="path2793" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m453,888.2c6.828,7.559,5.059,36.33,7.315,55.17"/>
					<path id="path2795" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m362.5,929.5c-2.04,38.24,2.84,50.51,3.571,52.5"/>
					<path id="path2797" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m376,924.2c5.487,23.63,6.436,39.23,7.224,54.54"/>
					<path id="path2799" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m388.9,919.5c5.291,13.68,8.874,31.71,10.36,55.12"/>
					<path id="path2801" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m403.5,913.9c6.868,18.05,13.06,36.1,11.47,54.15"/>
					<path id="path2803" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m418.4,908.1c7.878,8.444,13.87,34.04,13.06,52.04"/>
					<path id="path2805" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m434.9,900.3c10.78,13.42,11.78,38.06,11.85,51.36"/>
					<path id="path2807" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m465.8,877.4c9.081,14.84,4.813,37.38,6.366,58.64"/>
					<path id="path2809" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m480.1,863.6c7.266,19.06,3.204,42.88,4.327,64.53"/>
					<path id="path2811" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m494.3,852c5.61,14.79,1.913,45.02,4.286,66.43"/>
					<path id="path2813" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m512.8,838.6c4.752,33.49,1.856,45.19,2.784,67.79"/>
					<path id="path2815" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m518.2,836.3c3.495,24.8,1.686,44.3,2.5,66.43"/>
					<path id="path2817" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m522.7,834.2c3.194,16.68,1.509,43.07,2.124,64.88"/>
					<path id="path2819" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m526.8,833.4c4.101,14.78,1.682,41.3,1.428,63.93"/>
					<path id="path2821" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m530.4,833.5c3.459-2.136,4.495,37.11,0.2178,63.17"/>
				</g>
			</g>
		</g>
	</g>
	<g id="g2823" transform="translate(147.34713,-86.79984)">
		<g id="g2825" style="stroke-width:2.06220484;stroke-miterlimit:4;enable-background:new;stroke-dasharray:none;fill:#cac7e3;" transform="matrix(-1,0,0,1,710.76542,0)">
			<path id="path2827" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#cac7e3;" d="m342.7,320.9c13.18,0.5871-7.532,22.14,0.3571,54.15,5.315,21.56-57.44-21.5-66.07-24.99-12.16-4.921-22.89-3.112-23.03-5.55-0.216-3.725-10.07-49.46,13.04-38.03,57.09,28.24,55.22,13.51,75.71,14.43z"/>
			<path id="path2829" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m335.6,321c-13.76,32.62-5.178,57.85-5.178,57.85"/>
			<path id="path2831" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m322.6,323.6c-9.621,26.58-3.571,50.18-3.571,50.18"/>
			<path id="path2833" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m312.6,323.6c-7.995,20.13-2.866,45.31-2.857,45.35"/>
			<path id="path2835" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m301.7,321.5c-5.525,13.62-2.227,41.41-2.321,41.43"/>
			<path id="path2837" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m291,317.8c-1.577,5.408-6.93,34.36-2.5,38.75"/>
			<path id="path2839" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m281.9,313.8c-0.2661,13.79-6.027,30.78-3.036,37.14"/>
			<path id="path2841" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m275.3,311c-4.299,21.98-3.064,36.98-3.036,37.32"/>
			<path id="path2843" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m269.6,308.1c-3.439,48.23-2.857,38.93-2.857,38.93"/>
			<path id="path2845" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m265.3,306.2c-3.151,7.224-2.143,40-2.143,40"/>
			<path id="path2847" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m262.6,305.1c-7.102,10.53-2.247,40.89-2.143,40.89"/>
			<path id="path2849" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m260.6,304.4c-9.265,8.956-2.296,41.47-2.5,41.61"/>
			<path id="path2851" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:none;" d="m258.9,304.5c-10.47,7.797-2.998,40.71-3.393,40.71"/>
		</g>
	</g>
	<g id="g2853" transform="translate(147.34713,-86.79984)">
		<path id="path2855" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#efdac2;" d="m599.7,477.1c-4.088-13.76-11.45-14.72-21.21-3.03-2.891,3.461-5.637,4.794-10.1,11.62-1.434,2.191-4.936,4.398-8.257,11.27-22.02,45.57-35.46,42.43-41.06,120.6-12.23,49.37-13.7,54.39-20.41,116.5-12.74,10.28-10.2,19.84-2.993,30.42-8.835-8.881-16.82-9.073-26.25,2.067-16.71,3.778-41.48,7.768-87.28-5.963,0,0-38.23,90.15-38.3,90.13,14.47,8.188,20.24,12.18,36.75,18.5,20.45,8.489,33.77,10.67,53.68,18.31,35.01,13.42,50.81,17.85,72.51,17.36,28.26,2.891,51.95,6.729,67.34-2.039,34.08-19.42,38.24-66.18,37.71-77.76-0.2308-5.068,0.3835-9.861,1.205-14.2,8.207-8.867,3.624-15.92,4.118-26.68,6.213-135.3-1.39-128.1,8.263-145.1,4.947-8.728,34.91-95.94-17.41-149.4-2.065-2.112-6.501-6.522-8.299-12.57z"/>
		<path id="path2857" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:none;" d="m495.6,764.5c15.07,21.57,50.33,11.26,92.38,32.18"/>
		<path id="path2859" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:none;" d="m469.7,766.1c-12.28,15.04-12.46,35.63-6.503,71.12"/>
		<path id="path2861" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:none;" d="m498.6,734.4c0.3144,21.04,53.06,30.63,53.06,30.63"/>
	</g>
	<g id="g2863" transform="translate(147.34713,-86.79984)">
		<path id="path2865" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="M384.1,761.2c-163.8-56.7-236.3-210.6-152.9-189.6-106-48.47-83.65,177.4,126.2,286.9"/>
		<path id="path2867" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:url(#linearGradient4970);" d="M384.1,761.2c-163.8-56.7-236.3-210.6-152.9-189.6-106-48.47-83.65,177.4,126.2,286.9"/>
	</g>
	<g id="g2869" transform="translate(147.34713,-86.79984)">
		<g id="g2871" style="enable-background:new;fill:#efdac2;" transform="matrix(-0.99949404,-0.03180659,-0.03180659,0.99949404,734.64504,16.454869)">
			<path id="path2873" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#efdac2;" d="m549.3,537.8c-52.88-73.47-11.48-132.8-1.92-137.6,1.533-0.7778,3.346-6.497,6.325-9.191,5.133-4.642,9.596-1.752,13.17,0.6184,6.301,4.183,0.9523,4.409,18.72,10.27,27.19,8.963,58.65,43.46,55.48,93.99,12.53,33.73,8.978,102.2-1.857,142.2-5.332,19.65-9.915,39.64-10.14,62.21,1.562,46.26-43.19,65.9-83.34,60.58-25.35-3.354-28.83-4.235-43.93-1.317-46.11,8.91-97.36-93.66-53.92-115.3,3.576-1.781,7.292-3.426,11.1-4.96,6.207-2.497,12.63-4.59,18.81-5.686,4.766-0.8446,14.37-0.5936,14.37-0.5936,5.54-8.798,15.86-9.346,26.78-4.917,1.34-6.351,5.594-4.46,19.19-5.166,29.17-10.7,59.87,1.793,65.81-2.561"/>
			<path id="path2875" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#efdac2;" d="m552.6,520c-13.12,4.221-28.22,39.42-16.09,70.5-4.034,2.512-10.42,3.496-2.225,8.821-10.08,1.627-13.07,6.782-3.249,12.14-9.364,2.796-12.11,11.53,5.611,11.65,13.72-0.6374,65.63,10.56,73.51-0.2955"/>
			<path id="path2877" style="stroke-linejoin:miter;stroke:#000000;stroke-linecap:butt;stroke-dasharray:none;stroke-miterlimit:4;stroke-width:2.06220484;fill:#efdac2;" d="m533.1,607.4c60.48,10.93,52.09,16.12,52.09,16.11"/>
		</g>
		<path id="path2879" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:none;" d="m97.94,671.7c23.21-17.05,35.42-10.58,62.85-7.142l15.44,1.934"/>
	</g>
	<g id="g2881" transform="translate(147.34713,-86.79984)">
		<path id="path2883" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="m512.8,335.6c-21.23,42.68-328-20.69-333.1,108.5,45.92-88.52,313.1,0.4676,333.1-108.5z"/>
	</g>
	<g id="g2885" transform="translate(147.34713,-86.79984)">
		<path id="path2887" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="m297.8,124.7c8.75,73.75,108.6,125.9,173.1,156.5,32.83,15.55,43.5,37.4,41.87,54.64-0.952-21.1-45.59-32.95-53.08-35.47-55.28-18.57-115.2-29.68-212-130.9"/>
	</g>
	<g id="g2889" transform="translate(147.34713,-86.79984)">
		<path id="path2891" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#c2f1c2;" d="m426.8,266.8c-7.899,11.64,12.38,23.48,21.18,10.31l50.81-76.04c2.053-3.073-2.338-36.91-10.99-24.16z"/>
		<path id="path2893" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fdf9aa;" d="m456.6,222.9c-3.72,5.481,13.21,21.56,16.5,16.63l25.71-38.48c2.053-3.072-2.337-36.9-10.99-24.16z"/>
	</g>
	<path id="path2897" d="m482.6,282.1-6.272-62.71c-2.342-23.42,20.84-28.34,23.24-4.211l6.444,64.97c2.684,27.07-20.83,27.72-23.41,1.944z" style="stroke-linejoin:miter;enable-background:new;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;stroke-width:2.06220484;fill:#c2f1c2;"/>
	<path id="path2899" d="m629.3,270.4-8.847-97.98c-1.223-13.55-23.95-2.305-22.55,14.91l7.026,86.32c1.748,21.47,25.63,10.7,24.37-3.251z" style="stroke-linejoin:miter;enable-background:new;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;stroke-width:2.06220484;fill:#eac6da;"/>
	<a id="a2901" style="fill:#eac6da;" xlink:show="replace" onmouseout="InkWeb.setAtt({el:[&apos;g2950&apos;], att:&apos;fill fill-opacity&apos;, val:&apos;0 0&apos;});InkWeb.setAtt({el:[&apos;path2903&apos;], att:&apos;fill&apos;, val:&apos;eac6da&apos;});" xlink:href="http://tracksuitgene.dev/php/front.php" transform="translate(147.34713,-86.79984)" onmouseover="InkWeb.setAtt({el:[&apos;g2950&apos;], att:&apos;fill fill-opacity&apos;, val:&apos;652548 1&apos;});InkWeb.setAtt({el:[&apos;path2903&apos;], att:&apos;fill&apos;, val:&apos;652548&apos;});">
		<path id="path2903" d="m381.3,145.4c-28.25,10.64-56.49,21.3-84.7,32.04-7.455,4.096-4.019,15.14,2.44,18.44,9.685,5.959,20.85,1.347,30.42-2.388,23.34-8.744,46.69-17.46,70.03-26.17-8.192,2.977-28.05-18.14-18.2-21.92z" style="stroke-linejoin:miter;enable-background:new;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;stroke-width:2.06220484;"/>
	</a>
	<a id="a2905" style="fill:#c6dbef;" xlink:show="replace" onmouseout="InkWeb.setAtt({el:[&apos;g3003&apos;], att:&apos;fill fill-opacity&apos;, val:&apos;0 0&apos;});InkWeb.setAtt({el:[&apos;path2907&apos;], att:&apos;fill&apos;, val:&apos;c6dbef&apos;});" xlink:href="http://tracksuitgene.dev/php/front.php?show=code" transform="translate(147.34713,-86.79984)" onmouseover="InkWeb.setAtt({el:[&apos;g3003&apos;], att:&apos;fill fill-opacity&apos;, val:&apos;1e476c 1&apos;});InkWeb.setAtt({el:[&apos;path2907&apos;], att:&apos;fill&apos;, val:&apos;1e476c&apos;});">
		<path id="path2907" d="m465.3,113.7-84.04,31.7c-10.18,3.839,9.888,25.01,18.19,21.91l78.99-29.52c14.23-5.317,6.531-31.51-13.13-24.1z" style="stroke-linejoin:miter;enable-background:new;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;stroke-width:2.06220484;"/>
	</a>
	<path id="path2909" d="m479.3,249.6-3.023-30.22c-2.343-23.42,20.84-28.34,23.24-4.211l3.248,32.71c0.7985,8.043-22.38,12.52-23.46,1.717z" style="stroke-linejoin:miter;enable-background:new;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;stroke-width:2.06220484;fill:#fdf9aa;"/>
	<path id="path2911" d="m624.8,221-4.655-49c-1.286-13.54-24.05-2.296-22.55,14.91l3.841,44.01c0.8703,9.972,24.49,1.888,23.37-9.914z" style="stroke-linejoin:miter;enable-background:new;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;stroke-width:2.06220484;fill:#c7d9ee;"/>
	<g id="g2913" style="display:none;" transform="matrix(0.07642241,0,0,0.07642241,154.7112,90.91203)">
		<path id="path2915" d="m44.24,13.83c87.42,23.41,160,49.41,547.1,15.02,320-28.43,346.4-40.41,407.4-12.25-28.13,52.56-76.49,71.62-288.2,104.3-150.6,23.28-192.8,25.56-258.4,36.79,35.07,16.86,127.7,35.96,132.4,57.49,23.42,108-21.1,501.1-36.25,655.4,227.3-344.7,349.2-500.6,450.6-620.3v31.59c-184.3,205.2-413.8,550.6-458.7,617.2l-61.45,36.43c26.77-260,59.63-636.4,46.48-693.9-9.268-40.55-195.5-94.96-194-98.26,0.779-1.689,50.57-9.315,192.9-23.17,448.3-43.65,410.5-79.19,410.5-79.19-36.62-13.73-183,3.019-388.6,25.39-356.4,38.8-493.2,13.89-545.9-14.39-0.197,0.2277,44.2-37.85,44.13-38.17z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2917" d="m1999,279.9c0-0.01-88.53,158.8-209,491.7-24.62,15.42-39.6,14.56-46.83,12.87-183.9-62.17-335.9-229.3-462.1-250,74.59-83.53,289.8-106.8,314.5-188.5,75.87-251.1-326.5-119.2-381.1,217.7-7.059,43.52-18.88,200.9-18.88,200.9l-62.21,30.1c25.5-309.4,35.63-716.3-133.3-514v-31.73c85.48-97.94,190.8-50.53,194.5,223.9,148.3-389.9,551.2-340.7,454.4-153.5-48.84,94.43-187,112.7-305,183.4,130.2,27.65,172.8,117.2,433.9,245.6,133.8-358,221.2-498,221.2-498z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2919" d="m2210,382.8c-20.04-180.4-163.6-232.8-210.4-134.1v29.47c58.02-115.8,222.3-73.23,147.3,388.5l58.5-14.82c28.51-231.5,48.78-304.4,48.78-304.4l333.9-32.52c54.14,130.4,93.34,286.2,119.1,462.6,0,0-10.46,5.848,54.95-33.34-33.67-175-73.62-336.7-118.4-434.2l354.8-30.36,0.02-30.44s-368,29.77-368.8,29.66c-83.14-148.2-143.3-168.5-224.3-164.4-97.42,4.856-159.7,130.2-195.5,268.4zm364.1-98.26-313.7,30.83c16.25-58.73,37.34-140.5,104.9-175.1,78.85-40.32,163.2,41.97,208.8,144.2z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2921" d="m3000,249.1,501.5-43.07s-504.9,282.8-240,510.1c102,87.56,317.2,4.136,397.4-35.31,167.5-82.44,251.2-249.7,340.1-430.6v29.76c-158.9,378.8-340.6,433.1-383.6,452.8-72.31,33.1-293.6,125.8-406.3,15.41-275.4-269.5,172.3-496.1,172.3-496.1l-381.5,27.51z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2923" d="m4895,796.4c-246.1,22.63-353.9-259.5-639.3-370.5,134.4-206.2,319-216.5,407.2-234.5-41.19-89.51-522.8,90.07-547.9,408.2-3.47,43.95-8.975,168.4-8.975,168.4,1.558,0-49.84,28.04-49.84,28.04,12.69-197.1,54.61-660.4,1.825-678.8,0,0-26.07,85.83-58.34,161.1v-30.25c67.71-183.9,62.49-213.1,100.3-148.6,17.98,30.62,40.11,163.2,21.53,349.7,102.2-209.7,433.7-357.9,539.6-301.4,161.9,86.39-245.9,4.181-355.4,252.7,216.6,70.9,329.7,345.8,609,353.7,31.11,0.4121,83.89-47.36,83.89-47.36s-76.63,86.8-103.7,89.62z" style="opacity:0.64224135;fill:#000000;"/>
		<rect id="rect2925" style="opacity:0.64224135;color:#000000;fill-rule:nonzero;enable-background:new;fill:#000000;" height="31.3" width="8.073" y="248.7" x="4991"/>
		<path id="path2927" d="m5470,316.2c-220.2,51.05-432.8,140.4-466.3,296.3-22.43,104.3,67.38,145.2,169.7,175.4,223.3,65.8,702.6,28.51,736.8-125.9,18.16-81.87-10.69-112.2-215-239-49.5-30.73-118-69.15-154.9-113l0.179-0.5509c229.7-36.46,458.5-29.92,458.5-29.92v-30.41s-226.6,5.892-464.1,53.18l-0.915-0.3653c-12.17-16.47-19.6-33.66-19.72-51.47-0.584-84.53,123.2-132.8,251.7-178.2,0,0-8.075,13.86-51.71,37.62,0,0,61.6-23.48,82.82-37.62,43.98-29.32-11.25-42.06-42.43-31.11-122.4,42.99-300.4,144.6-302.6,226.3-0.414,15.05,6.349,31.32,18.2,48.22zm4.616,5.458c46.36,60.63,154,128.5,229.1,177.7,75.9,49.71,200.6,111.7,155.6,207.9-40.49,86.42-440.6,114.7-620.8,52.33-110.3-38.12-185.1-89.01-157-202.2,32.17-129.3,205.1-198.8,392.3-236z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2929" d="m6142,730.7c157.3,30.59,280.4-112.3,435-370.7-14.89,203.1,50.52,388.3,120.1,373.5,139.8-29.88,302.1-189.4,302.1-189.4l-0.01-38.13s-157.1,155.8-271.2,183.8c-28.24,6.926-116.9-54.68-109.9-445.1-185.8,339.5-330.6,462.7-429.8,442.1-72.77-15.09,85.83-296.4,19.84-406.4-22.18-36.97-207.9-31.33-207.9-31.33l-0.01,30.43s147-0.2443,160.8,23.53c50.6,86.87-115.1,408.9-19,427.6z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2931" d="m7000,505s102.9-93.74,235.3-253.5c17.59-21.12,51.85-37.9,57.14-17.28,37.72,146.9,34.41,275.6,118.8,468.1,6.321,14.43,185.4-312.3,588-543.1,0,0-406.4,230.3-562.4,565.6-34.63,14.26-66.04,17.12-88.66,6.707-89.02-252.1-80.89-377.4-105.7-455.3-128.2,165.9-242.5,267-242.5,267z" style="opacity:0.64224135;fill:#000000;"/>
		<rect id="rect2933" style="opacity:0.64224135;color:#000000;fill-rule:nonzero;enable-background:new;fill:#000000;" height="31.24" width="8.078" y="248.7" x="7991"/>
		<path id="path2935" d="m11740,259.2c-67.84,69.9,142.4,563.8-33.17,554-156.6-8.773-487-559.4-487.4-549.7-39.62,232-51.32,279-78.12,472.2l-64.05,27.15c47.23-213.7,46.33-448.6,29.11-492.1-19.21-48.52-78.19-77.2-104.5,8.893v-30.42c19.31-66.13,106.2-50.72,140.5-8.472,28.44,35.08,17.78,208,17.78,208,31.16-201,77.33-263.3,80.9-257.7,78.76,123.8,401.6,583.1,492.5,567.7,55.62-9.396-112.5-459.2-51.42-521.4,144.8,43.75,230.9,19.42,318.7,12.19l0.129,30.31c-57.45,5.216-195.2,14.95-261-20.56z" style="opacity:0.64224135;fill:#000000;"/>
		<path id="path2937" d="m8459,195.3c15.4,53.06,62.02,220.2,4.242,601l-71.06,30.68c60.8-266.7,19.3-504.5-6.138-595.5-15.94-57.08,259.4-90.63,259.4-90.63-137.9,7.335-347.1,9.672-645.8,18.02l77.83-38.84c298-6.972,541.4,3.291,889.2-43.53l-35.36,33.94c-50.3,18.08-482.3,50.63-472.3,84.85z" style="opacity:0.64224135;fill:#000000;"/>
		<rect id="rect2939" style="opacity:0.64224135;color:#000000;fill-rule:nonzero;enable-background:new;fill:#000000;" height="31.24" width="8.078" y="248.7" x="8991"/>
		<path id="path3196" style="opacity:0.64224135;fill:#000000;" d="m1E+4,249.1c93.97-4.835,163,33.28,211.3,152.7,0,0,63.01-273.1,304.4-245.3l-21.75,56c-224.6-13.27-245.4,191.3-254.7,229.8,96.15,22.33,213.6-12.34,333.6-23.41-140.8,88.36-370.9,228.1-305.4,259.4,286.1,136.8,604.4-75.27,731.5-429.6v31.18c-122.3,337.9-392,612.7-799.5,440.4-26.18-11.07,74.07-129,296-258-86.67,21.43-173.3,27.21-260,22l-14,160-72,6c41.28-183.9,10.62-374.2-149.5-370.9z"/>
		<use id="use2942" style="opacity:0.64224135;fill:#000000;" xlink:href="#path3196" transform="translate(1999.9992,-8.97e-6)" height="1000" width="1000" y="0" x="0"/>
		<g id="g2944" style="opacity:0.64224135;" transform="translate(8999.4501,-0.0699564)">
			<path id="path2946" d="m550.4,100.4s110.1-11.89,131.6-41.83c-501.3-131.3-875.7,550.9-495.6,672.9,173.2,55.57,318.5-58.04,503.5-214,23.96,30.74-39.33,209.7-55.75,430.6,0,0,1.202-1.04,77.42-44.78,105.7-315.5,194-616.5,287.9-623.6v-30.51c-107.6,4.691-180.7,329.4-282.8,619.7-2.673-61.49,73.45-355.6,51.2-442.8-156.9,138.1-371.6,331.5-545.4,272.2-308.7-105.4,32.3-678.6,398.4-628.7,14.39,2.832-54.71,25.99-70.45,30.76z" style="fill:#000000;"/>
			<rect id="rect2948" style="color:#000000;fill-rule:nonzero;enable-background:new;fill:#000000;" height="31.3" width="8.073" y="248.7" x="0.09475"/>
		</g>
	</g>
	<g id="g2950" style="fill-opacity:0;" transform="translate(141.79464,-29.659872)">
		<path id="path2952" d="m-137.4,188.8c78.43,1.867,261.4-29.76,370.4-36.1l-9.986,1.76c-98.02,6.167-280.7,39.32-364.8,38.08zm238.2,8.445c-0.6546,1.669-6.038,2.267-6.775,0.5126-7.828-18.63-7.085-39.79-9.521-38.28-10.43,6.451-20.34,26.83-31.37,45.57-1.451,2.466-6.094,6.044-6.094,6.044,0.1308-8.475,1.767-23.96,1.085-27.78-6.036,4.756-19.24,18.72-33.35,12.53-20.77-9.104-13.31-45.53,28.34-43.57,4.985,0.2344-9.609,15.21-77.66,5.96-13.12-1.784-6.948-14.34,5.012-6.985,34.13,21,17.54,37.89,8.755,42.74-5.191,2.869-13.41,7.812-22.09,0.5214-16.94-14.22,15.82-36.4,17.45-33.6-6.482,13.8-23,37.31-38.73,41.65-6.951,1.916-22.96,2.339-28.73,2.061-4.144-0.1993-10.32-0.9129-14.11-2.634,2.196-19.54,6.848-44.24,6.682-53.85l4.835-3.22c1.667,9.521-4.488,38.6-5.528,52.56,3.506,2.33,8.98,3.013,12.67,3.292,10.07,0.7646,22.11,0.6848,27.64-1.069,12.53-3.972,30.95-29.41,33.49-37.41-3.398,0.4188-26.13,16.69-10.95,29.1,7.954,6.506,15.55,2.139,20.23-1.133,4.286-2.997,16.08-16.83-16.19-35.83-7.613-4.483-8.175,1.524,2.679,3.083,44.4,6.378,68.24,0.7684,68.95-2.899-28.68-0.2014-38.9,31.3-15.96,39.06,11.79,3.989,24.81-12.39,31.98-18.19,1.659,7.897-0.09973,19.3-0.1413,26.97,11.18-19.92,22.13-40.81,31.26-45.2,1.893-0.9099,5.389-0.758,5.557,0.1081,2.207,11.38,2.8,23.58,9.247,38.29,25.99-51.55,32.68-39.66,33.56-33.22,0.6024,4.394,1.001,11.13,1.001,11.13,3.184-17.96,5.857-20.75,6.131-20.32,7.501,11.71,26.35,42.51,36.37,44.2,5.009,0.8461-5.43-33.83-3.348-40.17,1.598-4.865,11.15-7.802,20.2-7.728,0,0-13.53,0.9737-16.09,5.778-2.373,7.59,11.71,45.87-4.048,45.73-9.557-0.0885-34.37-40.94-34.4-40.2-2.905,7.743-4.204,19.89-6.252,34.65l-5.659,2.075c3.609-16.33,2.88-29.62,2.582-32.85-2.021-21.88-26.22,26.25-28.68,32.52z" style="fill:#652548;"/>
		<g id="g2954" style="fill:#652548;" transform="matrix(0.0484465,0,0,0.0484465,14.339181,197.4382)">
			<path id="path2956" d="m-307.9,718.7c-728.1-18.07-852,288-575,468.1,194.8,126.7,500.6,135.5,488.3-81.09-5.944-104.7-115-186.9-260.3-230.2-74.08-22.05,167.6-111.7,65.67-224.8-160.4-178.1-184.6,423.2-190.2,531.1l-65.96,29.73c10.66-146-35.81-252.9-46.41-295.5,0,0,56.58-46.35,56.38-48.16,0,0,45.79,118.1,44.15,175.9-4.039-228.4,78.79-624.6,252.2-440.4,105.9,112.5-89.54,217.1-69.48,223.2,145.1,44.33,259.8,128.3,277.8,216.4,54.44,266.6-396.2,350-636.5,182.6-266.5-185.7-69-541.5,659.4-538.4,97.61,0.4227,194.8,39.68,211.4,163.2,0,0,64.85-250.1,306.3-222.3l-21.75,56c-224.6-13.26-253.6,211.6-254.7,228.3,96.15,22.33,213.6-12.34,333.6-23.41-140.8,94.6-337.8,225.3-280.1,248.3,265.9,106,453,36.21,565.1-61.97,31.69-27.74,55.37-56.75,69.98-83.11-22.25,47.84-53,89.27-90.14,124.1-153.5,143.8-416,174.4-638.3,75.66-26.18-11.07,74.07-129,296-258-86.67,21.44-173.3,27.21-260,22l-14,160-75.16,50.31c34.66-274.4,16.48-443.6-148.3-447.6z" style="fill:#652548;"/>
			<path id="path2958" d="m782.2,694.1c14.09,58.09,68.5,271.5,12.28,558.8l-81.05,31.22c60.8-266.7,7.695-481-11.16-573.6-9.713-47.73,227-98.52,290.9-112.5-158.2,15.12-373.6,9.672-672.2,18.02l94.97-49.74c192.1,5.488,521.1,9.52,872.1-32.62l-35.36,33.94c-50.3,18.08-481,82.72-470.4,126.5z" style="fill:#652548;"/>
			<path id="path2960" d="m1201,780.7c40.14,41.8,37.12,184.8,11.19,446.6l74.87-43.3c28.51-231.5,50.34-321.6,50.34-321.6l320.8-46.96c54.14,130.4,101.7,287.1,124.3,545.7,0,0,27.32-3.648,92.73-42.84-24.17-203.5-92.89-420.9-137.6-518.4l491.3-81.04v-31.44s-502.9,83.07-503.7,82.96c-93.43-158-153.6-203.6-233.9-200-82.73,3.737-164.2,150.9-191.5,297-2.923-93.73-17.89-116.6-36.25-131.6-17.88-14.63-83.68,44.83-62.59,44.83zm443.2,5.49s-296.1,43.95-300.5,44.35c-4.41,0.403,51.12-190.2,105.7-210.2,44.05-16.16,107.8-6.707,194.8,165.8z" style="fill:#652548;"/>
			<path id="path2962" d="m-1069,500.9c-143.6,459.5-156.4,504.7-59.66,840.1,6.205,21.5-56.04,106.4-62.67,85.05-91.26-294.5-71.71-469.3,55.19-871.4,9.534-30.21,67.14-53.71,67.14-53.71z" style="fill:#652548;"/>
			<path id="path2964" d="m2025,1370c143.6-459.5,111.7-482.4,14.92-817.7-6.204-21.5,56.04-106.4,62.67-85.05,91.26,294.5,116.5,446.9-10.45,849-9.534,30.21-67.14,53.71-67.14,53.71z" style="fill:#652548;"/>
		</g>
	</g>
	<g id="g3003" style="fill-opacity:0;" transform="matrix(1.0379843,0,0,1.0379843,144.0582,-86.836164)">
		<path id="path3005" d="m711.1,128.4c1.1,4.536,4.113,21.94-0.2769,44.38l-6.666,2.396c4.748-20.83,2.331-38.05,0.8587-45.29-0.7585-3.727,16.87-7.199,21.86-8.294-12.35,1.181-29.17,0.7553-52.49,1.407l7.416-3.884c15,0.4286,40.69,0.7434,68.1-2.548l-2.761,2.65c-3.928,1.412-36.86,5.761-36.03,9.18z" style="fill:#1e476c;"/>
		<path id="path3007" d="m613.3,136.8c-8.942,25.45-11.22,44.07-51.63,31.99-2.127-0.6358,5.784-10.07,23.11-20.15-6.768,1.674-13.54,2.125-20.3,1.718l-1.093,12.49-5.566,3.024c-0.4972-23.91,8.53-43.43,28.54-41.12l-1.698,4.373c-17.54-1.036-19.8,16.53-19.89,17.82,7.508,1.744,16.68-0.9632,26.05-1.828-10.99,7.387-26.03,18.16-21.38,19.56,34.73,10.39,34.79-5.32,43.85-30.34,2.003-5.532,9.217-14.21,10.46,1.375,0.3598,4.518,1.023,11.37,1.023,11.37,4.622-21.78,6.764-25.1,7.044-24.66,7.664,11.97,26.15,42.64,36.38,44.37,5.118,0.8646-8.859-30.12-6.447-36.45,0.7651-2.008,8.576-5.247,12.77-7.298-1.921,1.226-6.588,3.544-7.113,5.014-2.734,7.652,12.45,42.53-3.653,42.39-9.765-0.0905-33.18-39.87-33.21-39.11-3.824,15.44-4.569,21.34-6.662,36.42l-6.364,3.867c3.415-16.78,2.578-19.92,1.859-29.53-0.5113-6.845-2.453-15.63-6.086-5.291z" style="fill:#1e476c;"/>
		<path id="path3009" d="m796.5,138.5c-7.654,25.86-17.66,44.18-54.18,30.73-2.083-0.7668,5.784-10.07,23.11-20.15-6.768,1.674-13.54,2.125-20.3,1.718l-1.093,12.49-5.566,3.024c-0.0274-23.81,8.723-43.49,28.54-41.12l-1.698,4.373c-17.54-1.036-19.8,16.53-19.89,17.82,7.508,1.744,16.68-0.9632,26.05-1.828-10.99,7.387-26.02,18.13-21.38,19.56,32.47,9.976,38.04-2.62,46.4-29.08,2.57-8.129,13.35-18.37,12.91,12.94,7.12-36.24,33.47-36.71,33.38-23.01-0.0514,7.452-5.142,9.792-11.19,13.52-4.765,2.94-5.452,4.039-5.452,4.991,8.076,3.769,13.82,16.4,25.03,23.21,0,0,9.559,6.705,19.33,4.903-8.876,7.048-21.95-0.1119-22.52-0.2433-12.59-6.626-19.47-22.25-27.39-24.52,0,0,1.893-3.844,9.913-9.655,4.256-3.084,10.18-12.06,2.382-13.85-15.42-3.542-21.75,20.53-23.45,41.31,0.1217,0-6.414,4.227-6.414,4.227,0.697-10.83,1.458-17.63,1.207-26.05-0.383-12.83-5.327-13.42-7.726-5.311z" style="fill:#1e476c;"/>
		<path id="path3011" d="m616.8,196.2s-39.73,19.15-16.83,37.78c5.831,4.741,26.73,0.3248,33.28-2.087,30.71-11.31,43.11-29.34,35.17-28.47-3.006,0.33-23.67,16.81-8.165,29.49,8.128,6.648,14.39,1.774,19.17-1.569,4.379-3.062,17.35-15.78-16.55-36.62-11.44-7.033-5.348,7.298,26.92,5.722-47.58,3.785-35.67-16.78-21.96-8.969,35.58,20.26,17.92,38.72,8.946,43.67-5.304,2.931-13.7,7.983-22.57,0.5328-17.31-14.53,14.8-35.69,17.83-34.33,3.73,1.667-5.331,18.72-42.73,34.51-3.473,1.467-25.72,9.919-35.34,2.671-24.04-18.1,12.91-38.48,12.91-38.48zm153.1,36.96,5.149-3.785,1.093-12.19c6.768,0.4072,13.08,0.1078,19.24-1.566-18.62,9.645-24.43,18.29-22.05,19.69,15.66,9.679,49.47,4.34,62.71-8.325-12.61,5.202-36.02,14.18-56.17,4.222-5.083-2.511,11.53-12.28,22.52-19.18-9.371,0.8652-16.65,3.314-26.05,1.828,0.0926-1.298,2.715-18.63,19.52-17.6l1.698-4.373c-18.85-2.171-23.46,16.75-23.46,16.75-0.2435-2.74-2.16-11.98-12.41-12.08-57.03-0.5244-84.01,31.62-60.78,41.79,34.35,15.04,55.9-2.424,56.36-14.28,0.3806-9.8-9.061-27.89-25.52-33.5-6.304-2.152-16.45,1.089-18.33,33.84,0.1283-4.514-3.447-13.73-3.447-13.73,0.0155,0.1413-4.257,4.222-4.257,4.222,0.8277,3.319,3.777,8.7,4.218,20.02l5.005-2.783c0.72-19.33,4.324-40.55,11.98-37.79,15.5,5.583,25.62,20.42,24.6,33.05-0.9167,11.31-12.88,20.12-44.27,8.351-24.17-9.065-7.053-36.65,54.44-36.72,11.6-0.0136,8.897,23.01,8.2,34.14z" style="fill:#1e476c;"/>
	</g>
	<g id="g3013" transform="translate(147.34713,-86.79984)">
		<path id="path3015" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="M586.3,520.5c52.04,103.4-43.17,147.5-135.9,148.5-49.3,0.5261-103.2-5.837-147.3-20.89-72.65-24.77-105.8-49.51-105.8-49.51-7.499-35.94,18.91-33.52,33.23-27.33,69.42,30.02,117.1,47.62,193.7,50.52,63.51-2.017,203,4.881,162.1-101.3z"/>
		<path id="path3017" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="M194.3,389.1c21.96,194.3,349.3,117.6,425.8,280.1,23.65-16.28,10.88-40.88,10.56-45-64.48-112.8-401.4-121.3-436.3-235.1z"/>
		<path id="path3019" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#c6daef;" d="m216.3,481.8c-17.17,6.97-31-11.45-25.77-13.53l37.9-15.04c14.03-5.569,38.61,7.979,15.17,17.49z"/>
		<path id="path3021" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fdf9aa;" d="m216.3,481.8c-17.18,6.962-31-11.45-25.77-13.53l19.44-7.719c18.37-7.294,33.21,10.35,19.54,15.89z"/>
	</g>
	<g id="g3023" transform="translate(147.34713,-86.79984)">
		<path id="path3025" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#eac6da;" d="m366,513.9-4.403-16.66c-2.348-8.883,21.14-7.706,22.21-3.298l4.239,17.48c6.63,27.33-17.27,20.55-22.05,2.48z"/>
		<path id="path3027" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#eac6da;" d="m208.5,357.1,13.82,33.56c6.57,15.95,28.23,9.308,20.34-9.509l-16.26-38.75c-7.837-18.67-22.82,2.739-17.9,14.69z"/>
		<path id="path3029" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#c7d9ee;" d="m208.5,357.1,6.979,16.94c0.608,1.476,22.86-3.186,19.3-11.66l-8.383-19.98c-7.837-18.67-22.82,2.739-17.9,14.69z"/>
		<path id="path3031" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#c7d9ee;" d="m366,514.2-2.778-10.38c2.786,10.41,24.81,7.837,22.65-1.048l2.176,8.944c6.649,27.33-17.21,20.53-22.05,2.48z"/>
	</g>
	<g id="g3033" transform="translate(147.34713,-86.79984)">
		<path id="path3035" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="m421.1,106.9c76.07,65.96,78.74,100.1,78.24,110.6,13.05-46.04-22.52-129.6-22.52-129.6"/>
		<path id="path3037" style="stroke-linejoin:miter;stroke-width:2.06220484;fill-rule:nonzero;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="M499.4,217.4c-23.04,132.9-281.7,71.86-305.1,171.7-13.71-147.2,238.7-85.91,305.1-171.7z"/>
		<path id="path3039" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="m179.7,444.1c15.68,83.89,329.3-6.746,413.8,109-7.256-184-413.9,71.43-413.8-109z"/>
		<path id="path3041" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke-dashoffset:0;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#c2f1c2;" d="m524.4,528.5,9.365-25.76c5.51-15.16,26.28-5.603,20.98,9.214l-9.172,25.65c-10.96-0.4445-21.17-9.102-21.17-9.102z"/>
		<path id="path3043" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke-dashoffset:0;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fdf9aa;" d="m515.1,553.9,9.239-25.4c8.118,6.226,14.72,8.715,21.16,9.112l-9.288,25.93c-6.487,18.11-28.02,9.34-21.11-9.646z"/>
		<path id="path3045" style="opacity:0.98999999;stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:#fcf9e0;" d="m205.3,758.8c86.02,21.06,294.9,25.31,397.6-24.78,55.99-27.31,40.45-89.59,27.19-110.6,26.35,66.11-73.43,63.8-157.4,62.94-124.2-1.268-213.9-44.22-213.9-44.22"/>
		<path id="path3047" style="stroke-linejoin:miter;stroke-width:2.06220484;stroke:#000000;stroke-linecap:butt;stroke-miterlimit:4;stroke-dasharray:none;enable-background:new;fill:url(#linearGradient4962);" d="m205.3,758.8c86.02,21.06,294.9,25.31,397.6-24.78,55.99-27.31,40.45-89.59,27.19-110.6,26.35,66.11-73.43,63.8-157.4,62.94-124.2-1.268-213.9-44.22-213.9-44.22"/>
	</g>
</svg>
-->

	</div>
	<div id="footer">

		<p>$copyright</p>

	</div>

</body>
</html>

_EOT;

	
	} // body()
		
    // xrender.php
?>