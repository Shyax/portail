<?php

/**
 * PluginGallery
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class PluginGallery extends BaseGallery
{


    public function setPhotoDefautById($photoId) {
        $this->removeDefault();

        Doctrine_Query::create()
                ->update('Photos p')
                ->set('p.is_default', '?', true)
                ->andWhere('p.id = ?', $photoId)
                ->execute();
    }
    public function setPhotoDefautByFilename($filename) {
        $this->removeDefault();

        Doctrine_Query::create()
                ->update('Photos p')
                ->set('p.is_default', '?', true)
                ->andWhere('p.filename = ?', $filename)
                ->execute();
    }

    public function removeDefault() {
        Doctrine_Query::create()
                ->update('Photos p')
                ->set('p.is_default', '?', false)
                ->where('p.gallery_id = ?', $this->getId())
                ->execute();
    }

    public function getPhotoDefault() {
        $default = Doctrine::getTable('Photos')->getDefault($this->getId());
        if (!$default instanceof Photos) {
            $default = new Photos();
        }

        return $default->getFilename() == "" ?
                sfConfig::get("app_gallerynePlugin_defaultPicture") :
                $default->getFullPath(true, sfConfig::get("app_gallerynePlugin_portfolio_thumbnails_size"));
    }

    public function preUpdate($event) {
        $preUdateObject = GalleryTable::getInstance()->find($this->getId());
        if($preUdateObject->getTitle() != $this->getTitle()){
            rename(sfConfig::get("app_gallerynePlugin_path_gallery").$preUdateObject->getSlug(), sfConfig::get("app_gallerynePlugin_path_gallery").  GalleryneUtils::slugify($this->getTitle()));
        }
        parent::preUpdate($event);
    }
    public function postSave($event) {
        $default = "";
        $files = $metas = $actions = array();
        try {
            $request = sfContext::getInstance()->getRequest();
            
            if ($request->hasParameter("Gallery_Photos")) {
                $files = $request->getParameter("Gallery_Photos");
            }
            $default = "";
            if ($request->hasParameter("Gallery_Photos_is_default")) {
                $default = $request->getParameter("Gallery_Photos_is_default");
            }
            if ($request->hasParameter("Gallery_Photos_meta")) {
                $metas = $request->getParameter("Gallery_Photos_meta");
            }
            if ($request->hasParameter("Gallery_Photos_action")) {
                $actions = $request->getParameter("Gallery_Photos_action");
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        $photos = $this->getPhotos();
        $photoIds = array();
        foreach($photos as $photo)
            $photoIds[$photo->getFilename()] = $photo;
        $mock = new Photos();
        $dir = sfConfig::get('sf_web_dir').$mock->getFullPath();
        /* check if the dir exists */
        if (is_dir($dir)) {
            /* open the dir */
            if ($dh = opendir($dir)) {
                /* browse every files  */
                while (($uploaded_file = readdir($dh)) !== false) {
                    /* check if the file is a file (not a folder, just in case) */
                    if (is_file($dir . $uploaded_file)) {
                        /* check if the file is expected (it could be uploaded for another object */
                        if (in_array($uploaded_file, $files) && !isset($photoIds[$uploaded_file])) {
                            /* create the object to persist and to get its destination path */
                            $file = new Photos();
                            $file->setFilename($uploaded_file);
                            /* we are dealing with the default picture in the end */
                            if($default == $uploaded_file)
                                $is_default = true;
                            else
                                $is_default = false;
                            $file->setIsDefault($is_default);
                            $file->setSize(filesize($dir . "/" . $uploaded_file));
                            if(isset($metas[$uploaded_file])){
                                $file->setTitle($metas[$uploaded_file]['title']);
                                $file->setDescription($metas[$uploaded_file]['description']);
                                unset($metas[$uploaded_file]);
                            }
                            $file->setGalleryId($this->getId());
                            /* get its destination path */
                            $path = sfConfig::get('sf_web_dir').$file->getFullPath(false);
                            /* check if the dir exists, else make it */
                            if (!file_exists($path))
                                mkdir($path, 0777, true);
                            /* to check if the upload_file doesn't have an namesake (homonym) in the destination path
                             * we assign the uploaded filename to the new filename */
                            $filename = $uploaded_file;
                            /* Check namesake (homonym) */
                            /* get base filename */
                            $filename = preg_split("/\.[a-z]+$/", $uploaded_file);
                            $filename = preg_replace("/^tmp_/", "", $filename[0]);
                            /* prepare filename vars
                             * get extension */
                            preg_match("/\.[a-z]+$/", $uploaded_file, $matches);
                            $ext = $matches[0];
                            if (file_exists($path . $filename . $ext)) {
                                /* add an integer in the filename while uploaded_file name exists in destination dir */
                                while (file_exists($path . $filename . $ext)) {
                                    $i = "";
                                    if (preg_match("/[0-9]*$/", $filename, $matches)) {
                                        /* create an index to add to the name */
                                        foreach ($matches as $match)
                                            $i .= $match;
                                        $i = intval($i) + 1;
                                        $base = substr($filename, 0, strlen($filename) - strlen($match));
                                        $filename = $base . $i;
                                    } else {
                                        $filename .= 2;
                                    }
                                }
                                /* set the correct and definitive filename */
                            }
                            $file->setFilename($filename . $ext);
                            rename($dir . "/" . $uploaded_file, $path . "/" . $filename . $ext);
                            /* save the file in db */
                            $file->save();
                            if ($is_default)
                                $default = $filename . $ext;
                            
                            foreach($actions as $name=>$action){
                                if(in_array($uploaded_file,$action)){
                                    $class = ucfirst($name);
                                    call_user_func($class.'::persist',$file);
                                    if($name == "delete")
                                        break;
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
        
        $this->setPhotoDefautByFilename(preg_replace("/tmp_/","",$default));
        parent::postSave($event);
        foreach($this->getPhotos() as $file){
            if(isset($metas[$file->getFilename()]) && ($metas[$file->getFilename()]['title']!= $file->getTitle() || 
                    $metas[$file->getFilename()]['description']!= $file->getDescription()) ){
                $file->setTitle($metas[$file->getFilename()]['title']);
                $file->setDescription($metas[$file->getFilename()]['description']);
                $file->save();
            }
            
            foreach($actions as $name=>$action){ 
                if(in_array($file->getFilename(),$action)){
                    $class = ucfirst($name);
                    call_user_func($class.'::persist',$file);
                    if($name == "delete")
                        break;
                    
                }
            }
        }
    }
}