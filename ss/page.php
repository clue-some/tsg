<?php
/*
 *	Page class - parent class of tracksuitgene.com configured XHTML pages
 *  	doctype()	- DOCTYPE HTML 4.01 and XHTML 1.0/1.1
 *					Transitional, Frameset and Strict
 *					also performs content-type negotiation
 *					Phil:  Added an 8th case: doctype ("xhtml", "svg", "1.1")
 *					will include scalable vector graphics within the xhtml.
 *
 *		head()		- output the head element.
 *					Configured for tracksuitgene.com
 *
 *		body()		- output the body element.
 *					Configured for tracksuitgene.com
 *
 *		This code modified from:
 *		(c) Copyright 2004-2006, Douglas W. Clifton, all rights reserved.
 *		for more copyright information visit the following URI:
 *		http://loadaveragezero.com/info/copyright.php
 */
class Page
{
	private static $lang = 'en-GB';     	// UK English
	private static $charset = 'UTF-8';		// Unicode 8-bit character encoding
	private static $media = array (
        'HTML'  => 'text/html',
        'XHTML' => 'application/xhtml+xml'
    );
	private $browserisIE;
	private $browserhasJavascript;
	private $media_type;
	protected $copyright;
    protected $page = array ();

    public function __construct ($testforIE = FALSE, $testforJS = FALSE)
    {
    	$this->browserisIE = $testforIE;
    	$this->browserhasJavascript = $testforJS;
    	$this->page['title'] = 'First Contact';
		$this->page['description'] = 'Front page for the Tracksuitgene site.';
		$this->page['description'] .= ' Log In or Enter a Code.';
		$this->page['keywords'] = 'netlabel,electronic,music,recording,mp3,download,artist';
		$this->page['category'] = 'NETLABEL';
    }

    public function doctype ($doc = 'xhtml', $type = 'strict', $ver = '1.1')
    {
        $doc  = strtoupper($doc);
        $type = strtolower($type);

        $avail = 'PUBLIC';				// or SYSTEM, but we're not going there yet
        // begin FPI
        $ISO = '-';     				// W3C is not ISO registered [or IETF for that matter]
        $OID = 'W3C';   				// unique owner ID
        $PTC = 'DTD';   				// the public text class
        $PCL = 'EN';					// as far as I know the PCL is always English
        $xlang = 'en';  				// this you may want to vary if you're in different locale
        $URI  = 'http://www.w3.org/';	// DTDs are all under the Technical Reports (TR) branch @ W3C
        $doc_top  = '<html';    		// what comes after the DOCTYPE of course

        if ($doc == 'HTML')
        {
            $top = 'HTML';
            $this->media_type = self::$media['HTML'];

            $PTD = 'HTML 4.01';  // we're only supporting HTML 4.01 here

            switch ($type)
            {
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

            if (stristr($_SERVER['HTTP_USER_AGENT'], 'W3C_Validator'))
            	$this->media_type = self::$media['XHTML'];
            else
            	$this->media_type = (stristr($_SERVER['HTTP_ACCEPT'], self::$media['XHTML']))
            						? self::$media['XHTML'] : self::$media['HTML'];

            // do NOT send XHTML 1.1 to browsers that don't accept application/xhtml+xml
            // see: labs/PHP/DOCTYPE.php#bug-fix for details and a link to the W3C XHTML
            // NOTES on this topic

            if ($this->media_type == self::$media['HTML'] and $ver == '1.1') $ver = '1.0';

			if ($type == 'svg')
			{
				$PTD = implode (' ', array ($doc, $ver, 'plus MathML 2.0 plus SVG 1.1'));
				$URI .= '2002/04/xhtml-math-svg/xhtml-math-svg.dtd';
			}
			else
			{
				if ($ver == '1.1')
				{
					$PTD = implode(' ', array($doc, $ver));
					$URI .= 'TR/xhtml11/DTD/xhtml11.dtd';
				}
				else
				{
					$PTD = implode(' ', array($doc, '1.0', ucfirst($type)));
					$URI .= 'TR/xhtml1/DTD/xhtml1-' . $type . '.dtd';
				}

				$doc_top .= ' lang="' . $xlang . '"';	// for backwards compatibilty
			}

            $doc_top .= '>';    // close root XHTML tag

            global $_IE, $charset, $lang;
            header ('Content-type: ' . $this->media_type . '; charset=' . self::$charset);	// send HTTP header

            // send the XML declaration before the DOCTYPE, but this
            // will put IE into quirks mode which we don't want

            if (!$this->browserisIE) echo '<?xml version="1.0" encoding="' . self::$charset . '"?>' . "\n";
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

	public function head ($css = null, $js = null) {

		$title = 'Tracksuitgene';

		if (isset($this->page['title'])) {
			$title .= ' - ' . $this->page['title'];
		} else {
			echo "<p>NOT SET</p>";
		}

		$no_description = 'The tracksuitgene codes for the informal; the relaxed inner self.';
		$description = (isset($this->page['description']) ? $this->page['description'] : $no_description);

		$default_keywords = 'tracksuitgene,music,electronic,digital,tracksuit,dance,dna,informal,play,rest,leisure,offduty';
		$keywords = (isset($this->page['keywords']) ? $this->page['keywords'] . ',' . $default_keywords : $default_keywords);

		// ODP/dmoz.org RDF -- a *very* small subset
		// TODO: set this up for electronic music categories
		// http://www.dmoz.org/about.html

		$categories = array(
			// listed on dmoc.org
			'NETLABEL' => 'Business/Arts and Entertainment/Music/Labels/Specialty/Dance/Techno',
			'ARTIST' => 'Arts/Music/Styles/E/Electronic/Bands and Artists'
		);

		$category = ($cat = $categories[strtoupper($this->page['category'])]) ? $cat : $categories['NETLABEL'];

 		$domain = 'tracksuitgene.com';
 		$author = 'Pred Maclinty';

 		$year = date ('Y');
 		$this->copyright = 'Copyright &copy; ' . (($year <= 2010) ? $year : ('2010 - ' . $year));
		$this->copyright = implode (', ', array ($this->copyright, $domain, 'all rights reserved.'));

		$tmp_lang = self::$lang;
		$tmp_char = self::$charset;

echo <<<_H
<head>
	<title>$title</title>
	<meta http-equiv="Content-type" content="$this->media_type; charset=$tmp_char" />
	<meta http-equiv="Content-language" content="$tmp_lang" />
	<meta name="Resource-type" content="document" />
	<meta name="description" content="$description" />
	<meta name="keywords" content="$keywords" />
	<meta name="Category" content="$category" />
	<meta name="Distribution" content="Global" />
	<meta name="Rating" content="General" />
	<meta name="Robots" content="index,follow" />
	<meta name="Author" content="$author" />
	<meta name="Copyright" content="$this->copyright" />
	<link type="$this->media_type" rel="home" href="/" />
	<link type="image/x-icon" rel="shortcut icon" href="../img/favicon.ico" />
	<link type="image/gif" rel="icon" href="../img/favicon.gif" />
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
				<script language="javascript" type="text/javascript" src="<?php echo $src; ?>"></script>
<?php
				echo "\n";

			}
		}

		echo '</head>' . "\n";

	} // head()

/*----------------------------------------------------------------------------------

	body (username)

 */

	public function body ($user = '[-no login-]')
	{
		$graphicsvg = "http://tracksuitgene.dev/img/TSGfront0.1.svg";
		$graphicpng = "http://tracksuitgene.dev/img/TSGcolour2.png";

		if (!$this->browserhasJavascript)
		{
			// serve the forms based version
		}
		else
		{
echo <<<_EOT

<body id="canvas">

	<div id="presentation">
		<div id="header">

			<p>Tracksuitgene</p>

		</div>
		<div id="user">

			<p>User: $user</p>

		</div>
	</div>
	<div id="centergraphic">

		<noscript>Tracksuitgene needs a Javascript enabled browser!</noscript>
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

	</div>
	<div id="footer">

		<p>{$this->copyright}</p>

	</div>

</body>
</html>

_EOT;
		} // else
	} // body()
}

/*
 *	LoginPage child class
 * 		- inherits from Page class
 * 		- implements overloaded body function to include login form.
 *
 *		body()		- output the body element with Login form.
 */

class LoginPage extends Page
{
	public function __construct ($testforIE = FALSE, $testforJS = FALSE)
    {
    	$this->browserisIE = $testforIE;
    	$this->browserhasJavascript = $testforJS;
    	$this->page['title'] = 'Login Dialogue';
		$this->page['description'] = 'Login page for the Tracksuitgene site.';
		$this->page['description'] .= ' Log In or Sign Up.';
		$this->page['keywords'] = 'netlabel,electronic,music,recording,mp3,download,artist';
		$this->page['category'] = 'NETLABEL';
	}

/*----------------------------------------------------------------------------------

	body (username)

 */

	public function body ($user = '[-no login-]')
	{
		$graphicsvg = "http://tracksuitgene.dev/img/TSGbeta0.6.svg";
		$graphicpng = "http://tracksuitgene.dev/img/TSGcolour2.png";

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
	<div id="leftnav">
		<p> LEFTNAV </p>
	</div>
	<div id="rightnav">
		<p> RIGHTNAV </p>
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

	</div>
	<div id="footer">

		<p>{$this->copyright}</p>

	</div>

</body>
</html>

_EOT;

	} // body()
}

/*
 *	CodePage child class
 * 		- inherits from Page class
 * 		- implements overloaded body function to include code enter form.
 *
 *		body()		- output the body element with Code form.
 */

class CodePage extends Page
{
	public function __construct ($testforIE = FALSE, $testforJS = FALSE)
    {
    	$this->browserisIE = $testforIE;
    	$this->browserhasJavascript = $testforJS;
    	$this->page['title'] = 'Code Entry Dialogue';
		$this->page['description'] = 'A pre-obtained code may be submitted to the Tracksuitgene.';
		$this->page['description'] .= ' Enter your Code.';
		$this->page['keywords'] = 'netlabel,electronic,music,recording,mp3,download,artist';
		$this->page['category'] = 'NETLABEL';
	}

/*----------------------------------------------------------------------------------

	body (username)

 */

	public function body ($user = '[-no login-]')
	{
		$graphicsvg = "http://tracksuitgene.dev/img/TSGbeta0.6.svg";
		$graphicpng = "http://tracksuitgene.dev/img/TSGcolour2.png";

echo <<<_EOT

<body id="canvas">

	<div id="leftnav">
		<p>LEFTNAV</p>
	</div>

	<div id="rightnav">
		<p>RIGHTNAV</p>
	</div>

	<div id="codecentergraphic">

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

	</div>

</body>
</html>

_EOT;
	} // body()
}
/*
 *	TestPage child class
 * 		- inherits from Page class
 * 		- implements overloaded body() function to test stuff.
 *
 *		body()		- output the body element with Code form.
 */

class TestPage extends Page
{
	public function __construct ($testforIE = FALSE, $testforJS = FALSE)
    {
    	$this->browserisIE = $testforIE;
    	$this->browserhasJavascript = $testforJS;
    	$this->page['title'] = 'Test stuff page';
		$this->page['description'] = 'Some stuff needs to be tested.';
		$this->page['description'] .= ' Usually for debug or to see how it works.';
		$this->page['keywords'] = 'netlabel,electronic,music,recording,mp3,download,artist';
		$this->page['category'] = 'NETLABEL';
	}

/*----------------------------------------------------------------------------------
 *
 *	body (username)
 *
 */

	public function body ($user = '[-no login-]')
	{
		$graphicsvg = "http://tracksuitgene.dev/img/filedoesnotexist.svg";
		$graphicpng = "http://tracksuitgene.dev/img/TSGcolour2.png";

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

	</div>
	<div id="footer">

		<p>{$this->copyright}</p>

	</div>

</body>
</html>

_EOT;
	} // body()
}
?>