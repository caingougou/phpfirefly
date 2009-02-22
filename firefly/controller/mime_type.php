<?php
class MimeType {
	private static $mime_types = array (
		'html' => 'text/html',
		'php' => 'text/html',
		'jsp' => 'text/html',
		'asp' => 'text/html',
		'aspx' => 'text/html',
		'txt' => 'text/plain',
		'css' => 'text/css',
		'ics' => 'text/calendar',
		'csv' => 'text/csv',
		'xml' => 'application/xml',
		'yml' => 'application/x-yaml',
		'yaml' => 'application/x-yaml',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'rss' => 'application/rss+xml',
		'atom' => 'application/atom+xml',
		'pdf' => 'application/pdf',
		'exe' => 'application/octet-stream',
		'gz' => 'application/x-gzip',
		'zip' => 'application/zip',
		'tar' => 'application/x-tar',
		'tgz' => 'application/x-compressed',
		'doc' => 'application/msword',
		'docx' => 'application/msword',
		'xls' => 'application/vnd.ms-excel',
		'xlsx' => 'application/vnd.ms-excel',
		'ppt' => 'application/vnd.ms-powerpoint',
		'pptx' => 'application/vnd.ms-powerpoint',
		'gif' => 'image/gif',
		'png' => 'image/png',
		'jpg' => 'image/jpg',
		'mp3' => 'audio/mpeg',
		'mpeg' => 'audio/mpeg',
		'mpg' => 'audio/mpeg',
		'wav' => 'audio/x-wav',
		'mov' => 'video/quicktime',
		'avi' => 'video/x-msvideo'
	);

	public static function register($extension, $content_type) {
		array_merge(self :: $mime_types, array ( $extension => $content_type ));
	}

	public static function get_mime_types() {
		return self :: $mime_types;
	}
}
?>
