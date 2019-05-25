<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

# Network
namespace network;

/**
 * Describe a Content-Disposition HTTP header during a multipart request
 */
class ContentDisposition {

	/**
	 * Constructor
	 *
	 * @param string $name         Form variable name e.g. 'name'
	 * @param string $content      Form variable value e.g. 'value'
	 * @param string $filename     Filename during upload e.g. 'myimage.jpg'
	 * @param string $content_type Content type e.g. 'image/jpg'
	 */
	public function __construct( $name, $content, $filename = null, $content_type = null ) {
		$this->name = $name;
		$this->content = $content;
		$this->filename = $filename;
		$this->contentType = $content_type;
	}

	/**
	 * Explicit constructor from a name and it's value
	 *
	 * @param string $name    Form variable name e.g. 'name'
	 * @param string $content Form variable value e.g. 'value'
	 * @return self
	 */
	public static function createFromNameContent( $name, $content ) {
		return new self( $name, $content, null, null );
	}

	/**
	 * Explicit constructor from file content, filename, and it's type
	 *
	 * @param string $name         Form variable name e.g. 'file'
	 * @param string $content      File content
	 * @param string $filename     Filename during upload e.g. 'myimage.jpg'
	 * @param string $content_type Content type e.g. 'image/jpg'
	 * @return self
	 */
	public static function createFromNameContentFilenameType( $name, $content, $filename, $content_type ) {
		return new self( $name, $content, $filename, $content_type );
	}

	/**
	 * Explicit constructor from an URL, content type and filename
	 *
	 * This is a shortcut to download the file and send the content.
	 *
	 * @param string $name         Form variable name e.g. 'file'
	 * @param string $url          File URL to be downloaded
	 * @param string $filename     Filename during upload e.g. 'myimage.jpg'
	 * @param string $content_type Content type e.g. 'image/jpg'
	 * @return self
	 */
	public static function createFromNameURLFilenameContentType( $name, $url, $filename, $content_type ) {
		$content = file_get_contents( $url );
		if( !$filename ) {
			$filename = basename( $url );
		}
		return new self( $name, $content, $filename, $content_type );
	}

	/**
	 * Explicit constructor from an URL and its content type
	 *
	 * Will be used a dummy filename.
	 *
	 * @param string $name         Form variable name e.g. 'file'
	 * @param string $url          File URL to be downloaded
	 * @param string $content_type e.g. 'image/jpg'
	 * @param string $content_type
	 * @return self
	 */
	public static function createFromNameURLType( $name, $url, $content_type ) {
		return self::createFromNameURLTypeFilename( $name, $url, 'dummy', $content_type );
	}


	/**
	 * Get the multipart request part
	 *
	 * An interesting reference:
	 *  	https://stackoverflow.com/a/4247082
	 *
	 * @param string $boundary
	 * @return string
	 */
	public function get( $boundary ) {
		$headers = [];

		// Content-Disposition
		$value = "form-data; name=\"{$this->name}\";";
		if( $this->filename ) {
			$value .= " filename=\"{$this->filename}\"";
		}
		$headers[] = HTTPRequest::header( 'Content-Disposition', $value );

		// Content-Type
		if( $this->contentType ) {
			$headers[] = HTTPRequest::header( 'Content-Type', $this->contentType );
		}

		$content  = "--$boundary\r\n";
		$content .= HTTPRequest::implodeHTTPHeaders( $headers );
		$content .= "\r\n";
		$content .= "{$this->content}\r\n";

		return $content;
	}

	/**
	 * Check if a boundary does not collides
	 *
	 * @param strimg $boundary
	 */
	public function isBoundaryOK( $boundary ) {
		return                          strpos( $this->name,        $boundary ) !== -1
		    &&                          strpos( $this->content,     $boundary ) !== -1
		    && ( !$this->filename    || strpos( $this->filename,    $boundary ) !== -1 )
		    && ( !$this->contentType || strpos( $this->contentType, $boundary ) !== -1 );
	}

	/**
	 * Generate a "Content-Type: multipart/form-data; boundary=$value"
	 *
	 * @return string
	 */
	public static function generateBoundary() {
		return 'ASD' . microtime( true );
	}

	/**
	 * Aggregate some content dispositions with a suitable boumdary
	 *
	 * @param array  $dispositions Array of ContentDisposition(s)
	 * @param string $boundary     Reference to the generated boundary
	 * @return string
	 * @see https://www.w3.org/Protocols/rfc1341/7_2_Multipart.html
	 */
	public static function aggregate( $dispositions, & $boundary ) {

		// $data should be an array of ContentDisposition
		foreach( $dispositions as $key => &$value ) {
			if( !is_object( $value ) ) {
				$value = ContentDisposition::createFromNameContent( $key, $value );
			}
		}

		// generate a safe boundary that never collides with the data
		$boundary = '';
		do {
			$ok = true;
			$boundary .= self::generateBoundary();
			foreach( $dispositions as $k => $disposition ) {
				if( !$disposition->isBoundaryOK( $boundary ) ) {
					$ok = false;
					break;
				}
			}
		} while( !$ok );

		// boundary should follow the standard
		if( strlen( $boundary ) > 69 ) {
			throw new \Exception( 'cannot generate a suitable boundary (wtf?)' );
		}

		// I want to test the specifications so this phrase should be perfectly ignored. asd
		$s = "Hello! This is an ignored preamble for a multipart message. Have a nice read of RFC1341. asd\r\n";

		// merge the dispositions with the safe boundary
		foreach( $dispositions as $disposition ) {
			$s .= $disposition->get( $boundary );
		}

		// close the whole
		$s .= "--$boundary--\r\n";

		// I want to test the specifications so this phrase should be perfectly ignored. asd
		$s .= "You know that multipart message? This is its ignored epiloque. Have a nice day! asd\r\n";

		return $s;
	}
}
