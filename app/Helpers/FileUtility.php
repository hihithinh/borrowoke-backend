<?php

namespace App\Helpers;


use App\Constant\DefaultConfig;
use App\Enums\EStoreFileType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FileUtility {

    /**
     * @param $type
     * @param UploadedFile $file
     * @param array $meta
     * @return string sub path to file
     * @throws \Exception
     */
    public static function storeFile($type, UploadedFile $file, $meta = []) {
        if (!EStoreFileType::isValid($type) || empty(config("app.fileStoringSubPath.$type"))) {
            throw new \Exception('Store type is not supported');
        }

		$physicalPath = config('app.resource_physical_path');

        $fileExtension = mb_strtolower($file->getClientOriginalExtension());
        if (empty($fileExtension)) {
            $fileExtension = self::mime2Text($file->getMimeType());
        }

        $subPath = self::generateFileSubPath($file->getFilename(), $fileExtension, $type, $meta);
        $fileName = $fileName = pathinfo($subPath, PATHINFO_FILENAME) . ".$fileExtension";

        $physicFile = $file->move(str_replace($fileName, '', "$physicalPath/$subPath"), $fileName);
        if (!file_exists($physicFile)) {
            throw new \Exception("Can't find moved file");
        }
        return $subPath;
    }

    public static function copyFile($oldPath, $newType, $meta = []) {
		if (!EStoreFileType::isValid($newType) || empty(config("app.fileStoringSubPath.$newType"))) {
			throw new \Exception('Store type is not supported');
		}

		$physicalPath = config('app.resource_physical_path');

		$fileExtension = pathinfo($oldPath, PATHINFO_EXTENSION);
		$fileName = pathinfo($oldPath, PATHINFO_FILENAME);
		$subPath = self::generateFileSubPath($fileName, $fileExtension, $newType, $meta);

		self::createWritableFile("$physicalPath/$subPath");

		copy("$physicalPath/$oldPath", "$physicalPath/$subPath");
		if (!file_exists("$physicalPath/$subPath")) {
			throw new \Exception("Can't find moved file");
		}
		return $subPath;
	}

	private static function generateFileSubPath($fileName, $fileExtension, $type, $meta = []) {
		$physicalPath = config('app.resource_physical_path');
		$isUseShardedPath = EStoreFileType::isUseShardedPath($type);
		do {
			$fileName = hash('md5', $fileName . (string)Carbon::now()->timestamp);
			$subPath = str_replace([
				'{yymm}',
				$isUseShardedPath ? '{sharded_hash}' : '{hash}',
				'{extension}'
			], [
				Carbon::now()->format('ym'),
				$isUseShardedPath ? self::shardingPath($fileName) : $fileName,
				$fileExtension,
			], config("app.fileStoringSubPath.$type"));
			if (EStoreFileType::hasCustomPathOption($type)) {
				foreach ($meta as $key => $val) {
					$subPath = str_replace('{' . $key . '}', $val, $subPath);
				}
			}
		} while (file_exists("$physicalPath/$subPath"));

		return $subPath;
	}

    public static function removeFiles($paths = []) {
        if (!is_array($paths)) {
            $paths = [$paths];
        }
        $physicalPath = config('app.resource_physical_path');
        foreach ($paths as $subPath) {
            if (isset($subPath) && file_exists("$physicalPath/$subPath")) {
                unlink("$physicalPath/$subPath");
            }
        }
    }

    /**
     * Copy from https://gist.github.com/alexcorvi/df8faecb59e86bee93411f6a7967df2c#gistcomment-2722664
     * @param $mime
     * @return bool|mixed
     */
    public static function mime2Text($mime) {
        $mime_map = [
            'video/3gpp2'                                                               => '3g2',
            'video/3gp'                                                                 => '3gp',
            'video/3gpp'                                                                => '3gp',
            'application/x-compressed'                                                  => '7zip',
            'audio/x-acc'                                                               => 'aac',
            'audio/ac3'                                                                 => 'ac3',
            'application/postscript'                                                    => 'ai',
            'audio/x-aiff'                                                              => 'aif',
            'audio/aiff'                                                                => 'aif',
            'audio/x-au'                                                                => 'au',
            'video/x-msvideo'                                                           => 'avi',
            'video/msvideo'                                                             => 'avi',
            'video/avi'                                                                 => 'avi',
            'application/x-troff-msvideo'                                               => 'avi',
            'application/macbinary'                                                     => 'bin',
            'application/mac-binary'                                                    => 'bin',
            'application/x-binary'                                                      => 'bin',
            'application/x-macbinary'                                                   => 'bin',
            'image/bmp'                                                                 => 'bmp',
            'image/x-bmp'                                                               => 'bmp',
            'image/x-bitmap'                                                            => 'bmp',
            'image/x-xbitmap'                                                           => 'bmp',
            'image/x-win-bitmap'                                                        => 'bmp',
            'image/x-windows-bmp'                                                       => 'bmp',
            'image/ms-bmp'                                                              => 'bmp',
            'image/x-ms-bmp'                                                            => 'bmp',
            'application/bmp'                                                           => 'bmp',
            'application/x-bmp'                                                         => 'bmp',
            'application/x-win-bitmap'                                                  => 'bmp',
            'application/cdr'                                                           => 'cdr',
            'application/coreldraw'                                                     => 'cdr',
            'application/x-cdr'                                                         => 'cdr',
            'application/x-coreldraw'                                                   => 'cdr',
            'image/cdr'                                                                 => 'cdr',
            'image/x-cdr'                                                               => 'cdr',
            'zz-application/zz-winassoc-cdr'                                            => 'cdr',
            'application/mac-compactpro'                                                => 'cpt',
            'application/pkix-crl'                                                      => 'crl',
            'application/pkcs-crl'                                                      => 'crl',
            'application/x-x509-ca-cert'                                                => 'crt',
            'application/pkix-cert'                                                     => 'crt',
            'text/css'                                                                  => 'css',
            'text/x-comma-separated-values'                                             => 'csv',
            'text/comma-separated-values'                                               => 'csv',
            'application/vnd.msexcel'                                                   => 'csv',
            'application/x-director'                                                    => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
            'application/x-dvi'                                                         => 'dvi',
            'message/rfc822'                                                            => 'eml',
            'application/x-msdownload'                                                  => 'exe',
            'video/x-f4v'                                                               => 'f4v',
            'audio/x-flac'                                                              => 'flac',
            'video/x-flv'                                                               => 'flv',
            'image/gif'                                                                 => 'gif',
            'application/gpg-keys'                                                      => 'gpg',
            'application/x-gtar'                                                        => 'gtar',
            'application/x-gzip'                                                        => 'gzip',
            'application/mac-binhex40'                                                  => 'hqx',
            'application/mac-binhex'                                                    => 'hqx',
            'application/x-binhex40'                                                    => 'hqx',
            'application/x-mac-binhex40'                                                => 'hqx',
            'text/html'                                                                 => 'html',
            'image/x-icon'                                                              => 'ico',
            'image/x-ico'                                                               => 'ico',
            'image/vnd.microsoft.icon'                                                  => 'ico',
            'text/calendar'                                                             => 'ics',
            'application/java-archive'                                                  => 'jar',
            'application/x-java-application'                                            => 'jar',
            'application/x-jar'                                                         => 'jar',
            'image/jp2'                                                                 => 'jp2',
            'video/mj2'                                                                 => 'jp2',
            'image/jpx'                                                                 => 'jp2',
            'image/jpm'                                                                 => 'jp2',
            'image/jpeg'                                                                => 'jpeg',
            'image/pjpeg'                                                               => 'jpeg',
            'application/x-javascript'                                                  => 'js',
            'application/json'                                                          => 'json',
            'text/json'                                                                 => 'json',
            'application/vnd.google-earth.kml+xml'                                      => 'kml',
            'application/vnd.google-earth.kmz'                                          => 'kmz',
            'text/x-log'                                                                => 'log',
            'audio/x-m4a'                                                               => 'm4a',
            'application/vnd.mpegurl'                                                   => 'm4u',
            'audio/midi'                                                                => 'mid',
            'application/vnd.mif'                                                       => 'mif',
            'video/quicktime'                                                           => 'mov',
            'video/x-sgi-movie'                                                         => 'movie',
            'audio/mpeg'                                                                => 'mp3',
            'audio/mpg'                                                                 => 'mp3',
            'audio/mpeg3'                                                               => 'mp3',
            'audio/mp3'                                                                 => 'mp3',
            'video/mp4'                                                                 => 'mp4',
            'video/mpeg'                                                                => 'mpeg',
            'application/oda'                                                           => 'oda',
            'audio/ogg'                                                                 => 'ogg',
            'video/ogg'                                                                 => 'ogg',
            'application/ogg'                                                           => 'ogg',
            'application/x-pkcs10'                                                      => 'p10',
            'application/pkcs10'                                                        => 'p10',
            'application/x-pkcs12'                                                      => 'p12',
            'application/x-pkcs7-signature'                                             => 'p7a',
            'application/pkcs7-mime'                                                    => 'p7c',
            'application/x-pkcs7-mime'                                                  => 'p7c',
            'application/x-pkcs7-certreqresp'                                           => 'p7r',
            'application/pkcs7-signature'                                               => 'p7s',
            'application/pdf'                                                           => 'pdf',
            'application/octet-stream'                                                  => 'pdf',
            'application/x-x509-user-cert'                                              => 'pem',
            'application/x-pem-file'                                                    => 'pem',
            'application/pgp'                                                           => 'pgp',
            'application/x-httpd-php'                                                   => 'php',
            'application/php'                                                           => 'php',
            'application/x-php'                                                         => 'php',
            'text/php'                                                                  => 'php',
            'text/x-php'                                                                => 'php',
            'application/x-httpd-php-source'                                            => 'php',
            'image/png'                                                                 => 'png',
            'image/x-png'                                                               => 'png',
            'application/powerpoint'                                                    => 'ppt',
            'application/vnd.ms-powerpoint'                                             => 'ppt',
            'application/vnd.ms-office'                                                 => 'ppt',
            'application/msword'                                                        => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop'                                                   => 'psd',
            'image/vnd.adobe.photoshop'                                                 => 'psd',
            'audio/x-realaudio'                                                         => 'ra',
            'audio/x-pn-realaudio'                                                      => 'ram',
            'application/x-rar'                                                         => 'rar',
            'application/rar'                                                           => 'rar',
            'application/x-rar-compressed'                                              => 'rar',
            'audio/x-pn-realaudio-plugin'                                               => 'rpm',
            'application/x-pkcs7'                                                       => 'rsa',
            'text/rtf'                                                                  => 'rtf',
            'text/richtext'                                                             => 'rtx',
            'video/vnd.rn-realvideo'                                                    => 'rv',
            'application/x-stuffit'                                                     => 'sit',
            'application/smil'                                                          => 'smil',
            'text/srt'                                                                  => 'srt',
            'image/svg+xml'                                                             => 'svg',
            'application/x-shockwave-flash'                                             => 'swf',
            'application/x-tar'                                                         => 'tar',
            'application/x-gzip-compressed'                                             => 'tgz',
            'image/tiff'                                                                => 'tiff',
            'text/plain'                                                                => 'txt',
            'text/x-vcard'                                                              => 'vcf',
            'application/videolan'                                                      => 'vlc',
            'text/vtt'                                                                  => 'vtt',
            'audio/x-wav'                                                               => 'wav',
            'audio/wave'                                                                => 'wav',
            'audio/wav'                                                                 => 'wav',
            'application/wbxml'                                                         => 'wbxml',
            'video/webm'                                                                => 'webm',
            'audio/x-ms-wma'                                                            => 'wma',
            'application/wmlc'                                                          => 'wmlc',
            'video/x-ms-wmv'                                                            => 'wmv',
            'video/x-ms-asf'                                                            => 'wmv',
            'application/xhtml+xml'                                                     => 'xhtml',
            'application/excel'                                                         => 'xl',
            'application/msexcel'                                                       => 'xls',
            'application/x-msexcel'                                                     => 'xls',
            'application/x-ms-excel'                                                    => 'xls',
            'application/x-excel'                                                       => 'xls',
            'application/x-dos_ms_excel'                                                => 'xls',
            'application/xls'                                                           => 'xls',
            'application/x-xls'                                                         => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
            'application/vnd.ms-excel'                                                  => 'xlsx',
            'application/xml'                                                           => 'xml',
            'text/xml'                                                                  => 'xml',
            'text/xsl'                                                                  => 'xsl',
            'application/xspf+xml'                                                      => 'xspf',
            'application/x-compress'                                                    => 'z',
            'application/x-zip'                                                         => 'zip',
            'application/zip'                                                           => 'zip',
            'application/x-zip-compressed'                                              => 'zip',
            'application/s-compressed'                                                  => 'zip',
            'multipart/x-zip'                                                           => 'zip',
            'text/x-scriptzsh'                                                          => 'zsh',
        ];

        return isset($mime_map[$mime]) === true ? $mime_map[$mime] : false;
    }

    public static function shardingPath(string $hash, int $depth = 3, int $width = 1, $prefix = '') {
        // lấy [depth * width] kí tự đầu của [hash], tạo thành [depth] nhóm, mỗi nhóm có [width] kí tự
        $length = strlen($hash);

        $shards = [];
        for ($i = 0; $i < $depth; $i++) {
            if ($depth * $width > $length) {
                $shards[] = Str::random($width);
            } else {
                $shards[] = mb_substr($hash, count($shards) * $width, $width);
            }
        }

        $shards[] = $hash;

        $shards[count($shards) - 1] = "$prefix{$shards[count($shards) - 1]}";

        return implode('/', $shards);
    }

    public static function getFileResourcePath($subPath, $default = DefaultConfig::FALLBACK_IMAGE_PATH) {
        if (empty($subPath)) {
            return $default;
        }
        if (Str::startsWith($subPath, ['https://', 'http://'])) {
            return $subPath;
        }
        if (!file_exists(config('app.resource_physical_path') . "/$subPath")) {
            return $default;
        }
        return config('app.resource_url_path') . "/$subPath";
    }

    public static function getTemporaryFilePath(string $fileName, string $fileExtension, string $prefix = '') {
		$physicalPath = config('app.resource_physical_path');

		do {
			$fileName = hash('md5', $fileName . (string)Carbon::now()->timestamp);
			$subPath = str_replace([
				'{yymm}',
				'{sharded_hash}',
				'{extension}'
			], [
				Carbon::now()->format('ym'),
				FileUtility::shardingPath($fileName, 3, 1, $prefix),
				'xlsx',
			], config("app.fileStoringSubPath.temporary"));
		} while (file_exists("$physicalPath/$subPath"));

		return $subPath;
	}

	/**
	 * This functions check recursive permissions and recursive existence parent folders,
	 * before creating a folder. To avoid the generation of errors/warnings.
	 *
	 * @return bool
	 *     true folder has been created or exist and writable.
	 *     False folder not exist and cannot be created.
	 */
	public static function createWritableFolder($folder) {
		if (file_exists($folder)) {
			// Folder exist.
			return is_writable($folder);
		}
		// Folder not exit, check parent folder.
		$folderParent = dirname($folder);
		if ($folderParent != '.' && $folderParent != '/') {
			if (!static::createWritableFolder(dirname($folder))) {
				// Failed to create folder parent.
				return false;
			}
			// Folder parent created.
		}

		if (is_writable($folderParent)) {
			// Folder parent is writable.
			if (mkdir($folder, 0777, true)) {
				// Folder created.
				return true;
			}
			// Failed to create folder.
		}
		// Folder parent is not writable.
		return false;
	}

	/**
	 * This functions check recursive permissions and recursive existence parent folders,
	 * before creating a file/folder. To avoid the generation of errors/warnings.
	 *
	 * @return bool
	 *     true has been created or file exist and writable.
	 *     False file not exist and cannot be created.
	 */
	public static function createWritableFile($file) {
		// Check if conf file exist.
		if (file_exists($file)) {
			// check if conf file is writable.
			return is_writable($file);
		}

		// Check if conf folder exist and try to create conf file.
		if (static::createWritableFolder(dirname($file)) && ($handle = fopen($file, 'a'))) {
			fclose($handle);
			return true; // File conf created.
		}
		// Inaccessible conf file.
		return false;
	}
}
