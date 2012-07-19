﻿<?php

// Xupdate_ftp excutr function
if(!class_exists('ZipArchive') ){
	$mod_zip=false;
	if (!extension_loaded('zip')) {
		if (function_exists('dl')){
			$prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
			if(@dl($prefix . 'zip.' . PHP_SHLIB_SUFFIX)){
				$mod_zip=true;
			}
		}
	}
	if(!class_exists('ZipArchive') ){
		require_once XUPDATE_TRUST_PATH .'/include/FtpCommonFileArchive.class.php';
	}else{
		require_once XUPDATE_TRUST_PATH .'/include/FtpCommonZipArchive.class.php';
	}
}else{
	require_once XUPDATE_TRUST_PATH .'/include/FtpCommonZipArchive.class.php';
}

class Xupdate_FtpModuleInstall extends Xupdate_FtpCommonZipArchive {

/*parent public
	public $mRoot ;
	public $mModule ;
	public $mAsset ;

	public $Xupdate  ;	// Xupdate instance
	public $Ftp  ;	// FTP instance
	public $Func ;	// Functions instance
	public $mod_config ;
	public $content ;
	public $downloadDirPath;
	public $exploredDirPath;
	public $downloadUrlFormat;

	public $nextlink ;

	public $target_key;
	public $target_type;
*/

	public $trust_dirname;
	public $dirname;
	public $unzipdirlevel;
	
	private $lockfile;

	public function __construct() {

		parent::__construct();
		$mod_config = $this->mRoot->mContext->mModuleConfig;
		$this->lockfile = XOOPS_TRUST_PATH.'/'.trim($mod_config['temp_path'], '/').'/xupdate.lock';
	}

	/**
	 * execute
	 *
	 * @param string $caller
	 *
	 * @return	bool
	 **/
	public function execute( $caller )
	{

		$result = true;
		if( $this->Xupdate->params['is_writable']['result'] === true ) {
			if(! $this->checkExploredDirPath($this->target_key)) {
				$this->_set_error_log(_MI_XUPDATE_ERR_MAKE_EXPLOREDDIR . ': ' .$this->target_key);
				return false;
			}
			if (! $this->is_xupdate_excutable()) {
				$this->content.= '<div class="error">' . _MI_XUPDATE_ANOTHER_PROCESS_RUNNING . '</div>';
				return false;
			}
			
			$downloadUrl = $this->Func->_getDownloadUrl( $this->target_key, $this->downloadUrlFormat );
			$tempFilename = $this->target_key . '.zip';
			
			if ($this->checkExploredDirPath($this->target_key)) {
				$this->content.= _MI_XUPDATE_PROG_FILE_GETTING . '<br />';
				if ($this->Func->_downloadFile( $this->target_key, $downloadUrl, $tempFilename, $this->downloadedFilePath )){
					$downloadDirPath = realpath($this->Xupdate->params['temp_path']);
					$this->exploredDirPath = realpath($downloadDirPath.'/'.$this->target_key);
					if($this->_unzipFile()==true) {
						//一つディレクトリ階層を下げる
						$downdir_result = false;
						if (!empty($this->unzipdirlevel)){
							$downdir_result = $this->_exploredDirPath_DownDir();
						}
						// TODO port , timeout
						if ($this->Ftp->isConnected() || $this->Ftp->app_login("127.0.0.1")==true) {
							// overwrite control
							if(! isset($this->options['no_overwrite'])){
								$this->options['no_overwrite'] = array();
							}
							if(! isset($this->options['install_only'])){
								$this->options['install_only'] = array();
							}
							$this->Ftp->set_no_overwrite(array($this->options['no_overwrite'], $this->options['install_only']));
							if (!$this->uploadFiles()){
								$this->_set_error_log(_MI_XUPDATE_ERR_FTP_UPLOADFILES);
								$result = false;
							}
							// change directories to writable
							if(isset($this->options['writable_dir'])){
								array_map(array($this, '_chmod_dir'),$this->options['writable_dir']);
							}
							// change files to writable
							if(isset($this->options['writable_file'])){
								array_map(array($this, '_chmod_file'),$this->options['writable_file']);
							}

						}else{
							$this->_set_error_log(_MI_XUPDATE_ERR_FTP_LOGIN);
							$result = false;
						}
	
						//一つディレクトリ階層を戻す
						if ($downdir_result){
							$this->_exploredDirPath_UpDir();
						}
	
					}else{
						$this->_set_error_log(_MI_XUPDATE_ERR_UNZIP_FILE);
						$result = false;
					}
				}else{
					$this->_set_error_log(_MI_XUPDATE_ERR_DOWNLOAD_FILE);
					$result = false;
				}
			} else {
				$this->_set_error_log(_MI_XUPDATE_ERR_MAKE_EXPLOREDDIR . ': ' . $this->target_key);
				$result = false;
			}

			$this->content.= _MI_XUPDATE_PROG_CLEANING_UP . '<br />';
			$this->_cleanup($this->exploredDirPath);

			if ($this->Ftp->isConnected()) {
				$this->Ftp->app_logout();
			}
			//

//TODO unlink ok?
			@unlink( $this->downloadedFilePath );

			$this->content.= _MI_XUPDATE_PROG_COMPLETED . '<br /><br />';
		}else{
			$result = false;
		}

		if ($result){
			$this->nextlink = $this->_get_nextlink($this->dirname, $caller);

		}else{
			$this->content.= _ERRORS;
		}
		
		@ unlink($this->lockfile);
		
		return $result;
	}

	/**
	 * _getDownloadUrl
	 *
	 * @return	string
	 **/
	public function _getDownloadUrl()
	{
		$url = sprintf($this->downloadUrlFormat, $this->target_key);
		return $url;
	}

	/**
	 * uploadFiles
	 *
	 * @return	bool
	 **/
	private function uploadFiles()
	{
		//$this->Ftp->connect();

		$this->Ftp->appendMes( 'start uploading..<br />');
		$this->content.= _MI_XUPDATE_PROG_UPLOADING . '<br />';

		if ($this->target_type == 'TrustModule'){
			if (!empty($this->trust_dirname) && !empty($this->dirname) && $this->trust_dirname != $this->dirname){

				// copy xoops_trust_path
				$uploadPath = XOOPS_TRUST_PATH . '/' ;
				$unzipPath =  $this->exploredDirPath . '/xoops_trust_path';
				$result = $this->Ftp->uploadNakami($unzipPath, $uploadPath);
				if (! $this->_check_file_upload_result($result, 'xoops_trust_path')){
					return false;
				}

				// rename copy html module
				$uploadPath = XOOPS_ROOT_PATH . '/modules/' ;
				$unzipPath =  $this->exploredDirPath .'/html/modules';
				$result = $this->Ftp->uploadNakami_To_module($unzipPath, $uploadPath ,$this->trust_dirname,$this->dirname);
				if (! $this->_check_file_upload_result($result, 'html/modules')){
					return false;
				}

				// rename copy html module
				$uploadPath = XOOPS_ROOT_PATH . '/' ;
				$unzipPath =  $this->exploredDirPath .'/html';
				$result = $this->Ftp->uploadNakami_OtherThan_module($unzipPath, $uploadPath ,$this->trust_dirname,$this->dirname);
				if (! $this->_check_file_upload_result($result, 'html')){
					return false;
				}

			}else{

				// copy xoops_trust_path
				$uploadPath = XOOPS_TRUST_PATH . '/' ;
				$unzipPath =  $this->exploredDirPath . '/xoops_trust_path';
				$result = $this->Ftp->uploadNakami($unzipPath, $uploadPath);
				if (! $this->_check_file_upload_result($result, 'xoops_trust_path')){
					return false;
				}

				// copy html
				$uploadPath = XOOPS_ROOT_PATH . '/' ;
				$unzipPath =  $this->exploredDirPath .'/html';
				$result = $this->Ftp->uploadNakami($unzipPath, $uploadPath);
				if (! $this->_check_file_upload_result($result, 'html')){
					return false;
				}
			}
			
		}else{
			
			// copy xoops_trust_path if exists
			$uploadPath = XOOPS_TRUST_PATH . '/' ;
			$unzipPath =  $this->exploredDirPath . '/xoops_trust_path';
			if (file_exists($unzipPath)) {
				$result = $this->Ftp->uploadNakami($unzipPath, $uploadPath);
				if (! $this->_check_file_upload_result($result, 'xoops_trust_path')){
					return false;
				}
			}
			
			// copy html
			$uploadPath = XOOPS_ROOT_PATH . '/' ;
			$unzipPath =  $this->exploredDirPath .'/html';
			$result = $this->Ftp->uploadNakami($unzipPath, $uploadPath);
			if (! $this->_check_file_upload_result($result, 'html')){
				return false;
			}
			
			// for legacy core extra languages
			if ($this->dirname === 'legacy') {
				// copy extras languages
				$langs = array();
				if ($handle = opendir(XOOPS_ROOT_PATH . '/language')) {
					while (false !== ($name = readdir($handle))) {
						if ($name[0] !== '.' && is_dir(XOOPS_ROOT_PATH . '/language/' . $name)) {
							$langs[] = $name;
						}
					}
					closedir($handle);
				}
				//adump($langs);
				foreach ($langs as $lang) {
					$uploadPath = XOOPS_ROOT_PATH . '/' ;
					$unzipPath =  $this->exploredDirPath . '/extras/extra_languages/' . $lang;
					if (file_exists($unzipPath)) {
						$result = $this->Ftp->uploadNakami($unzipPath, $uploadPath);
						if (! $this->_check_file_upload_result($result, 'html')){
							return false;
						}
					}
				}
			}
		}

		$this->Ftp->appendMes( 'end uploaded success<br />');
		return true;
	}

	/**
	 * _get_nextlink
	 *
	 * @param   string  $dirname
	 *
	 * @return	string
	 **/
	private function _get_nextlink($dirname, $caller)
	{
		$ret ='';
		if ($caller == 'module') {
			$hModule = Xupdate_Utils::getXoopsHandler('module');
			$module =& $hModule->getByDirname($dirname) ;
			if (is_object($module)){
				if ($module->getVar('isactive') ) {
					$ret ='<a href="'.XOOPS_MODULE_URL.'/legacy/admin/index.php?action=ModuleUpdate&dirname='.$dirname.'">'._MI_XUPDATE_ADMENU_MODULE._MI_XUPDATE_UPDATE.'</a>';
				} else {
					$ret =_AD_LEGACY_LANG_BLOCK_INACTIVETOTAL;
				}
			} else if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $dirname)) {
				$ret ='<a href="'.XOOPS_MODULE_URL.'/legacy/admin/index.php?action=ModuleInstall&dirname='.$dirname.'">'._MI_XUPDATE_ADMENU_MODULE._INSTALL.'</a>';
			} else {
				$ret ='<a href="'.XOOPS_MODULE_URL.'/xupdate/admin/index.php?action=ModuleStore">'._AD_XUPDATE_LANG_MESSAGE_GETTING_FILES._AD_XUPDATE_LANG_MESSAGE_SUCCESS.'</a>';
			}

		} elseif ($caller == 'theme') {
			$ret ='<a href="'.XOOPS_MODULE_URL.'/legacy/admin/index.php?action=ThemeList">'._MI_XUPDATE_ADMENU_THEME._MI_XUPDATE_MANAGE.'</a>';
		}
		return $ret;
	}

	/**
	 * _exploredDirPath_DownDir
	 *
	 * @param   void
	 *
	 * @return	void
	 **/
	private function _exploredDirPath_DownDir()
	{
		$ret = false;
		$dir = $this->exploredDirPath;
		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..'){
				continue;
			}
			if ($item =='html' || $item =='xoops_trust_path') {
				$ret = false;
				break;
			}
			if (is_dir($dir.'/'.$item)) {
				$this->exploredDirPath = realpath($dir.'/'.$item);
				$this->Ftp->appendMes('down dir exploredDirPath: '.$this->exploredDirPath.'<br />');
				$ret = true;
				break;
			}
		}
		return $ret;
	}

	/**
	 * _exploredDirPath_UpDir
	 *
	 * @param   void
	 *
	 * @return	void
	 **/
	private function _exploredDirPath_UpDir()
	{
		$this->exploredDirPath = dirname($this->exploredDirPath);
		$this->Ftp->appendMes('up dir exploredDirPath: '.$this->exploredDirPath.'<br />');
	}

	/**
	 * _chmod_dir
	 *
	 * @param   string $directory
	 *
	 * @return	void
	 **/
	private function _chmod_dir( &$directory)
	{
		if(file_exists($directory) && is_dir($directory)){
			$this->Ftp->localChmod($directory, 0707);
		}
	}

	/**
	 * _chmod_file
	 *
	 * @param   string $directory
	 *
	 * @return	void
	 **/
	private function _chmod_file( &$directory)
	{
		if(file_exists($directory) && !is_dir($directory)){
			$this->Ftp->localChmod($directory, 0606);
		}
	}
	
	/**
	 * _check_file_upload_result
	 * 
	 * @param array  $result
	 * @param string $where
	 * @return boolean
	 */
	private function _check_file_upload_result($result, $where) {
		if (is_bool($result)) {
			$result = array('ok' => $result, 'ng' => array());
		}
		if (!$result['ok']){
			$this->Ftp->appendMes( 'fail upload '.$where.' uploadNakami<br />');
			return false;
		}
		if ($result['ng']) {
			$this->_set_error_log(_MI_XUPDATE_ERR_NOT_UPLOADED.': ' . join('<br />'._MI_XUPDATE_ERR_NOT_UPLOADED.': ', $result['ng']));
		}
		return true;
	}

	/**
	 * is_xupdate_excutable
	 * 
	 * @return boolean
	 */
	private function is_xupdate_excutable() {
		if (file_exists($this->lockfile) && filemtime($this->lockfile) + 600 > time()) {
			return false;
		}
		ignore_user_abort(true); // Ignore user aborts and allow the script
		touch($this->lockfile);  // make lock file
		return true;
	}
} // end class

?>