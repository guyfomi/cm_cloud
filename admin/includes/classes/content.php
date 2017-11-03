<?php

    if (!class_exists('osC_Roles_Admin')) {
        include('includes/classes/roles.php');
    }

if (!class_exists('osC_Users_Admin')) {
    require('includes/classes/users.php');
}

    class content
    {
        private $content_type;
        private $custom_properties;
        private $content_id;
        private $date_created;
        private $date_modified;
        private $date_published;
        private $created_by;
        private $modified_by;
        private $published_by;
        private $content_order;
        private $content_status;
        private $content_descriptions;
        private $can_modify;
        private $can_write;
        private $can_read;
        private $can_publish;
        private $parent_id;
        private $linked_contents; //associations

        public function setCanModify($can_modify)
        {
            $this->can_modify = $can_modify;
        }

        public function getCanModify()
        {
            return $this->can_modify;
        }

        public function setCanPublish($can_publish)
        {
            $this->can_publish = $can_publish;
        }

        public function getCanPublish()
        {
            return $this->can_publish;
        }

        public function setCanRead($can_read)
        {
            $this->can_read = $can_read;
        }

        public function getCanRead()
        {
            return $this->can_read;
        }

        public function setCanWrite($can_write)
        {
            $this->can_write = $can_write;
        }

        public function getCanWrite()
        {
            return $this->can_write;
        }

        public function setContentDescriptions($content_descriptions)
        {
            $this->content_descriptions = $content_descriptions;
        }

        public function getContentDescriptions()
        {
            return $this->content_descriptions;
        }

        public function setContentId($content_id)
        {
            $this->content_id = $content_id;
        }

        public function getContentId()
        {
            return $this->content_id;
        }

        public function setContentOrder($content_order)
        {
            $this->content_order = $content_order;
        }

        public function getContentOrder()
        {
            return $this->content_order;
        }

        public function setContentStatus($content_status)
        {
            $this->content_status = $content_status;
        }

        public function getContentStatus()
        {
            return $this->content_status;
        }

        public function setContentType($content_type)
        {
            $this->content_type = $content_type;
        }

        public function getContentType()
        {
            return $this->content_type;
        }

        public function setCreatedBy($created_by)
        {
            $this->created_by = $created_by;
        }

        public function getCreatedBy()
        {
            return $this->created_by;
        }

        public function setDateCreated($date_created)
        {
            $this->date_created = $date_created;
        }

        public function getDateCreated()
        {
            return $this->date_created;
        }

        public function setDateModified($date_modified)
        {
            $this->date_modified = $date_modified;
        }

        public function getDateModified()
        {
            return $this->date_modified;
        }

        public function setDatePublished($date_published)
        {
            $this->date_published = $date_published;
        }

        public function getDatePublished()
        {
            return $this->date_published;
        }

        public function setLinkedContents($linked_contents)
        {
            $this->linked_contents = $linked_contents;
        }

        public function getLinkedContents()
        {
            return $this->linked_contents;
        }

        public function setModifiedBy($modified_by)
        {
            $this->modified_by = $modified_by;
        }

        public function getModifiedBy()
        {
            return $this->modified_by;
        }

        public function setParentId($parent_id)
        {
            $this->parent_id = $parent_id;
        }

        public function getParentId()
        {
            return $this->parent_id;
        }

        public function setPublishedBy($published_by)
        {
            $this->published_by = $published_by;
        }

        public function getPublishedBy()
        {
            return $this->published_by;
        }

        public function setCustomProperties($custom_properties)
        {
            $this->custom_properties = $custom_properties;
        }

        public function getCustomProperties()
        {
            return $this->custom_properties;
        }

        public static function saveDocument($id = null, $data)
        {
            global $osC_Database;

            $osC_Database->startTransaction();
            $error = false;
            if ($data['documents_file']) {
                $file = new upload($data['documents_file']);

                if ($file->exists()) {
                    //remove old attachment file
                    if (is_numeric($id)) {
                        $Qfile = $osC_Database->query('select cache_filename from :table_content_documents where documents_id = :id');
                        $Qfile->bindTable(':table_content_documents', TABLE_CONTENT_DOCUMENTS);
                        $Qfile->bindInt(':id', $id);
                        $Qfile->execute();

                        if ($Qfile->numberOfRows() == 1) {
                            $file = DIR_FS_CACHE . 'documents/' . $Qfile->value('cache_filename');
                            if (file_exists($file)) {
                                @unlink($file);
                            }
                        }
                    }

                    $file->set_destination(realpath(DIR_FS_CACHE . '/documents'));

                    if ($file->parse() && $file->save()) {
                        $filename = $file->filename;

                        $cache_filename = $filename;
                        $cache_filename = str_replace(' ', "_", $cache_filename);
                        $cache_filename = str_replace("-", "_", $cache_filename);

                        @rename(DIR_FS_CACHE . 'documents/' . $file->filename, DIR_FS_CACHE . '/documents/' . $cache_filename);

                        if (is_numeric($id)) {
                            $Qdocument = $osC_Database->query('update :table_content_documents set documents_status = :documents_status,documents_last_modified = now() where documents_id = :documents_id');
                            $Qdocument->bindInt(':documents_id', $id);
                        } else {
                            $Qdocument = $osC_Database->query('insert into :table_content_documents (documents_status,filename,cache_filename,content_id,documents_date_added,content_type,documents_name,documents_description) values (:documents_status,:filename,:cache_filename,:content_id ,:documents_date_added,:content_type,:documents_name,:documents_description)');
                            $Qdocument->bindRaw(':documents_date_added', 'now()');
                        }

                        $Qdocument->bindTable(':table_content_documents', TABLE_CONTENT_DOCUMENTS);
                        $Qdocument->bindValue(':documents_status', $data['documents_status']);
                        $Qdocument->bindValue(':filename', $filename);
                        $Qdocument->bindValue(':cache_filename', $cache_filename);
                        $Qdocument->bindValue(':content_id', $data['content_id']);
                        $Qdocument->bindValue(':content_type', $data['content_type']);
                        $Qdocument->bindValue(':documents_name', $data['documents_name']);
                        $Qdocument->bindValue(':documents_description', $data['documents_description']);
                        $Qdocument->setLogging($_SESSION['module'], $id);
                        $Qdocument->execute();

                        if ($osC_Database->isError()) {
                            $error = true;
                        }
                    }
                }
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('sefu-documents');
                return true;
            }

            $osC_Database->rollbackTransaction();

            return false;
        }

        public static function saveComment($id = null, $data)
        {
            global $osC_Database;
            $filename = '';
            $cache_filename = '';

            $osC_Database->startTransaction();
            $error = false;
            if (isset($data['comment_file_name']) && !empty($data['comment_file_name'])) {
                $file = new upload($data['comment_file_name']);

                if ($file->exists()) {
                    if (is_numeric($id)) {
                        $Qfile = $osC_Database->query('select cache_filename from :table_content_comments where comments_id = :id');
                        $Qfile->bindTable(':table_content_comments', TABLE_CONTENT_COMMENTS);
                        $Qfile->bindInt(':id', $id);
                        $Qfile->execute();

                        if ($Qfile->numberOfRows() == 1) {
                            $file = DIR_FS_CACHE . 'comments/' . $Qfile->value('cache_filename');
                            if (file_exists($file)) {
                                @unlink($file);
                            }
                        }
                    }

                    $file->set_destination(realpath(DIR_FS_CACHE . '/comments'));

                    if ($file->parse() && $file->save()) {
                        $filename = $file->filename;

                        $cache_filename = $filename;
                        $cache_filename = str_replace(' ', "_", $cache_filename);
                        $cache_filename = str_replace("-", "_", $cache_filename);

                        @rename(DIR_FS_CACHE . 'comments/' . $file->filename, DIR_FS_CACHE . '/comments/' . $cache_filename);
                    }
                }
            }

            if (is_numeric($id)) {
                $Qcomment = $osC_Database->query('update :table_content_comments set comments_status = :comments_status where comments_id = :comments_id');
                $Qcomment->bindInt(':comments_id', $id);
            } else {
                $Qcomment = $osC_Database->query('insert into :table_content_comments (comments_date_added,comments_status,comments_description,created_by,content_id,parent_id,content_type,comment_file_name,cache_filename) values (now(),:comments_status,:comments_description,:created_by,:content_id ,:parent_id,:content_type,:comment_file_name,:cache_filename)');
            }

            $Qcomment->bindTable(':table_content_comments', TABLE_CONTENT_COMMENTS);
            $Qcomment->bindValue(':comments_status', isset($data['comments_status']) && !empty($data['comments_status']) ? $data['comments_status'] : 1);
            $Qcomment->bindValue(':comment_file_name', $filename);
            $Qcomment->bindValue(':cache_filename', $cache_filename);
            $Qcomment->bindInt(':content_id', $data['content_id']);
            $Qcomment->bindValue(':content_type', $data['content_type']);
            $Qcomment->bindValue(':comments_description', isset($data['comments_description']) && !empty($data['comments_description']) ? $data['comments_description'] : "Aucune description ...");
            $Qcomment->bindInt(':created_by', $_SESSION['admin']['id']);
            $Qcomment->bindInt(':parent_id', 0);
            $Qcomment->setLogging($_SESSION['module'], $id);
            $Qcomment->execute();

            if ($osC_Database->isError()) {
                $error = true;
                $_SESSION['error'] = $osC_Database->getError();
            }

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('sefu-documents');
                return true;
            }

            $osC_Database->rollbackTransaction();

            return false;
        }

        public static function saveLink($id = null, $data)
        {
            global $osC_Database;

            $osC_Database->startTransaction();
            if (is_numeric($id)) {
                $Qlink = $osC_Database->query('update :table_content_links set links_status = :links_status,links_last_modified = now() where links_id = :links_id');
                $Qlink->bindInt(':links_id', $id);
            } else {
                $Qlink = $osC_Database->query('insert into :table_content_links (links_status,links_target,links_name,links_description,links_url,links_date_added,content_type,content_id) values (:links_status,:links_target,:links_name,:links_description ,:links_url,now(),:content_type,:content_id)');
                $Qlink->bindRaw(':documents_date_added', 'now()');
            }

            $Qlink->bindTable(':table_content_links', TABLE_CONTENT_LINKS);
            $Qlink->bindValue(':links_status', $data['links_status']);
            $Qlink->bindValue(':content_id', $data['content_id']);
            $Qlink->bindValue(':content_type', $data['content_type']);
            $Qlink->bindValue(':links_name', $data['links_name']);
            $Qlink->bindValue(':links_description', $data['links_description']);
            $Qlink->bindValue(':links_url', $data['links_url']);
            $Qlink->bindValue(':links_target', $data['links_target']);
            $Qlink->setLogging($_SESSION['module'], $id);
            $Qlink->execute();

            if ($osC_Database->isError()) {
                $osC_Database->rollbackTransaction();
                return false;
            }

            $osC_Database->commitTransaction();
            osC_Cache::clear('sefu-links');
            return true;
        }

        public static function saveTicket($id = null, $data)
        {
            global $osC_Database;

            $osC_Database->startTransaction();
            if (is_numeric($id)) {
                $Qticket = $osC_Database->query("update delta_tickets set status_id = :status_id,isanswered = 1,lastupdate = now() where tickets_id = :tickets_id");
                $Qticket->bindInt(':tickets_id', $id);
                $Qticket->bindInt(':status_id', $data['status_id']);
            } else {
                $Qticket = $osC_Database->query("INSERT INTO delta_tickets(administrators_id, status_id, sla_id, source,created,customers_id, plants_id, lines_id, asset_id, component_id, sensors_id, description) VALUES (:administrators_id,1,1,'Cloud',now(),:customers_id, :plants_id, :lines_id, :asset_id, :component_id, :sensors_id, :description)");
                $Qticket->bindInt(':administrators_id', $data['administrators_id']);
                $Qticket->bindInt(':customers_id', $data['customers_id']);
                $Qticket->bindInt(':plants_id', $data['plants_id']);
                $Qticket->bindInt(':lines_id', $data['lines_id']);
                $Qticket->bindInt(':asset_id', $data['asset_id']);
                $Qticket->bindInt(':component_id', $data['component_id']);
                $Qticket->bindInt(':sensors_id', $data['sensors_id']);
                $Qticket->bindValue(':description', $data['description']);
            }

            $Qticket->execute();

            if ($osC_Database->isError()) {
                $_SESSION['error'] = $osC_Database->getError();
                $osC_Database->rollbackTransaction();
                return false;
            }


            $data['content_id'] = (!empty($id)) ? $id : $osC_Database->nextID();
            $data['content_type'] = 'ticket';

            if (content::saveComment(null, $data)
            ) {
                $osC_Database->commitTransaction();
                return true;

            } else {
                $_SESSION['error'] = $osC_Database->getError();
                $osC_Database->rollbackTransaction();
                return false;
            }
        }

        public static function saveContent($id = null, $content_id, $content_type, $data)
        {
            global $osC_Database;

            $osC_Database->startTransaction();
            if (is_numeric($id)) {
                $Qcontent = $osC_Database->query('update :table_content set date_modified = now(),modified_by = :user,content_order = :content_order,content_status = :content_status where content_id = :content_id and content_type = :content_type');
            } else {
                $Qcontent = $osC_Database->query('insert into :table_content (content_id,content_type,date_created,created_by,content_order,content_status,parent_id) values (:content_id,:content_type,now(),:user,:content_order,:content_status,:parent_id)');
            }

            $Qcontent->bindTable(':table_content', TABLE_CONTENT);
            $Qcontent->bindValue(':content_status', $data['content_status']);
            $Qcontent->bindValue(':content_id', $content_id);
            $Qcontent->bindValue(':user', $_SESSION[admin][id]);
            $Qcontent->bindValue(':content_type', $content_type);
            $Qcontent->bindValue(':content_order', $data['content_order']);
            $Qcontent->bindInt(':parent_id', 0);
            $Qcontent->setLogging($_SESSION['module'], $id);
            $Qcontent->execute();

            if ($osC_Database->isError()) {
                $osC_Database->rollbackTransaction();
                return false;
            }

            $osC_Database->commitTransaction();
            return true;
        }

        public static function getContentDescription($content_id, $content_type)
        {
            global $osC_Database;
            $data = array();

            $Qcd = $osC_Database->query('SELECT cd.* FROM :table_content_description cd where cd.content_id = :content_id and cd.content_type = :content_type');
            $Qcd->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
            $Qcd->bindInt(':content_id', $content_id);
            $Qcd->bindValue(':content_type', $content_type);
            $Qcd->execute();

            while ($Qcd->next()) {
                $data['content_name[' . $Qcd->valueInt('language_id') . ']'] = $Qcd->value('content_name');
                $data['content_url[' . $Qcd->valueInt('language_id') . ']'] = $Qcd->value('content_url');
                $data['content_description[' . $Qcd->valueInt('language_id') . ']'] = $Qcd->value('content_description');
                $data['page_title[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('page_title');
                $data['meta_keywords[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('meta_keywords');
                $data['meta_descriptions[' . $Qcd->ValueInt('language_id') . ']'] = $Qcd->Value('meta_descriptions');
            }

            $Qcd->freeResult();

            return $data;
        }

        public static function getContentCategories($content_id, $content_type)
        {
            global $osC_Database;

            $Qcategories = $osC_Database->query('select categories_id from :table_content_to_categories where content_id = :content_id and content_type = :content_type');
            $Qcategories->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qcategories->bindInt(':content_id', $content_id);
            $Qcategories->bindValue(':content_type', $content_type);
            $Qcategories->execute();

            $categories_array = array();
            while ($Qcategories->next()) {
                $categories_array[] = $Qcategories->valueInt('categories_id');
            }
            $Qcategories->freeResult();

            return $categories_array;
        }

        public static function saveContentDescription($id = null, $content_id, $content_type, $data)
        {
            global $osC_Database, $osC_Language;

            $osC_Database->startTransaction();

            foreach ($osC_Language->getAll() as $l) {
                if (is_numeric($id)) {
                    $Qad = $osC_Database->query('update :table_content_description set content_name = :content_name, content_url = :content_url, content_description = :content_description, page_title = :page_title, meta_keywords = :meta_keywords, meta_descriptions = :meta_descriptions where content_id = :content_id and content_type = :content_type and language_id = :language_id');
                } else {
                    $Qad = $osC_Database->query('insert into :table_content_description (content_id, content_type, content_description, language_id, content_name, meta_descriptions, meta_keywords, content_url,page_title) values (:content_id, :content_type, :content_description, :language_id, :content_name, :meta_descriptions, :meta_keywords, :content_url,:page_title)');
                }

                $Qad->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
                $Qad->bindInt(':content_id', $content_id);
                $Qad->bindValue(':content_type', $content_type);
                $Qad->bindInt(':language_id', $l['id']);
                $Qad->bindValue(':content_name', $data['content_name'][$l['id']]);
                $Qad->bindValue(':content_url', ($data['content_url'][$l['id']] == '')
                                                      ? $data['content_name'][$l['id']]
                                                      : $data['content_url'][$l['id']]);
                $Qad->bindValue(':content_description', $data['content_description'][$l['id']]);
                $Qad->bindValue(':page_title', $data['page_title'][$l['id']]);
                $Qad->bindValue(':meta_keywords', $data['meta_keywords'][$l['id']]);
                $Qad->bindValue(':meta_descriptions', $data['meta_descriptions'][$l['id']]);
                $Qad->setLogging($_SESSION['module'], $content_id);
                $Qad->execute();

                if ($osC_Database->isError()) {
                    $osC_Database->rollbackTransaction();
                    return false;
                }
            }

            $osC_Database->commitTransaction();
            return true;
        }

        public static function saveContentToCategories($id = null, $content_id, $content_type, $data)
        {
            global $osC_Database;

            $osC_Database->startTransaction();

            $Qcategories = $osC_Database->query('delete from :table_content_to_categories where content_id = :content_id and content_type = :content_type');
            $Qcategories->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qcategories->bindInt(':content_id', $content_id);
            $Qcategories->bindValue(':content_type', $content_type);
            $Qcategories->setLogging($_SESSION['module'], $content_id);
            $Qcategories->execute();

            if ($osC_Database->isError()) {
                $osC_Database->rollbackTransaction();
                return false;
            } else {
                if (isset($data['categories']) && !empty($data['categories'])) {
                    foreach ($data['categories'] as $category_id) {
                        $Qp2c = $osC_Database->query('insert into :table_content_to_categories (content_id, categories_id,content_type) values (:content_id, :categories_id,:content_type)');
                        $Qp2c->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
                        $Qp2c->bindInt(':content_id', $content_id);
                        $Qp2c->bindValue(':content_type', $content_type);
                        $Qp2c->bindInt(':categories_id', $category_id);
                        $Qp2c->setLogging($_SESSION['module'], $content_id);
                        $Qp2c->execute();

                        if ($osC_Database->isError()) {
                            $osC_Database->rollbackTransaction();
                            return false;
                        }
                    }
                }
            }

            $osC_Database->commitTransaction();
            return true;
        }

        public static function saveImages($content_id, $content_type)
        {
            global $osC_Session, $osC_Database, $osC_Image;
            $error = false;
            $images = array();
            $image_path = '../images/content/_upload/' . $osC_Session->getID() . '/';

            $osC_DirectoryListing = new osC_DirectoryListing($image_path, true);
            $osC_DirectoryListing->setIncludeDirectories(false);
            foreach (($osC_DirectoryListing->getFiles()) as $file) {
                @copy($image_path . $file['name'], '../images/content/originals/' . $file['name']);
                @unlink($image_path . $file['name']);

                $images[$file['name']] = -1;
            }
            osc_remove($image_path);

            foreach (array_keys($images) as $image) {
                $Qimage = $osC_Database->query('insert into :table_content_images (content_id, sort_order, date_added,content_type) values (:content_id, :sort_order, :date_added,:content_type)');
                $Qimage->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                $Qimage->bindInt(':content_id', $content_id);
                $Qimage->bindValue(':content_type', $content_type);
                $Qimage->bindInt(':sort_order', 0);
                $Qimage->bindRaw(':date_added', 'now()');
                $Qimage->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                } else {
                    $image_id = $osC_Database->nextID();
                    $images[$image] = $image_id;

                    $new_image_name = $content_id . '_' . $image_id . '_' . $image;
                    @rename('../images/content/originals/' . $image, '../images/content/originals/' . $new_image_name);

                    $Qupdate = $osC_Database->query('update :table_content_images set image = :image where id = :id');
                    $Qupdate->bindTable(':table_content_images', TABLE_CONTENT_IMAGES);
                    $Qupdate->bindValue(':image', $new_image_name);
                    $Qupdate->bindInt(':id', $image_id);
                    $Qupdate->setLogging($_SESSION['module'], $content_id);
                    $Qupdate->execute();
                    if ($osC_Database->isError()) {
                        $error = true;
                    }
                    else
                    {
                        foreach ($osC_Image->getGroups() as $group) {
                            if ($group['id'] != '1') {
                                $osC_Image->resize($new_image_name, $group['id'], 'content');
                            }
                        }
                    }
                }
            }

            return !$error;
        }

        public static function deleteDocument($id, $filename)
        {
            global $osC_Database;
            $error = false;

            $osC_Database->startTransaction();

            $Qad = $osC_Database->query('delete from :table_content_documents where documents_id = :documents_id');
            $Qad->bindTable(':table_content_documents', TABLE_CONTENT_DOCUMENTS);
            $Qad->bindInt(':documents_id', $id);
            $Qad->setLogging($_SESSION['module'], $id);
            $Qad->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                @unlink(DIR_FS_CACHE . 'documents/' . $filename);
                osC_Cache::clear('sefu-documents');
                return true;
            }

            $osC_Database->rollbackTransaction();
            return false;
        }

        public static function deleteContent($content_id, $content_type)
        {
            global $osC_Database;
            $error = false;

            $osC_Database->startTransaction();

            //categories
            $Qcategories = $osC_Database->query('delete from :table_content_to_categories where content_id = :content_id and content_type = :content_type');
            $Qcategories->bindTable(':table_content_to_categories', TABLE_CONTENT_TO_CATEGORIES);
            $Qcategories->bindInt(':content_id', $content_id);
            $Qcategories->bindValue(':content_type', $content_type);
            $Qcategories->setLogging($_SESSION['module'], $content_id);
            $Qcategories->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $Qcontent_description = $osC_Database->query('delete from :table_content_description where content_id = :content_id and content_type = :content_type');
                $Qcontent_description->bindTable(':table_content_description', TABLE_CONTENT_DESCRIPTION);
                $Qcontent_description->bindInt(':content_id', $content_id);
                $Qcontent_description->bindValue(':content_type', $content_type);
                $Qcontent_description->setLogging($_SESSION['module'], $content_id);
                $Qcontent_description->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }
            }

            if ($error === false) {
                $Qcontent = $osC_Database->query('delete from :table_content where content_id = :content_id and content_type = :content_type');
                $Qcontent->bindTable(':table_content', TABLE_CONTENT);
                $Qcontent->bindInt(':content_id', $content_id);
                $Qcontent->bindValue(':content_type', $content_type);
                $Qcontent->setLogging($_SESSION['module'], $content_id);
                $Qcontent->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }
            }

            if ($error == true) {
                $osC_Database->rollbackTransaction();
                return false;
            }

            $osC_Database->commitTransaction();
            return true;
        }

        public static function deleteLink($id)
        {
            global $osC_Database;
            $error = false;

            $osC_Database->startTransaction();

            $Qad = $osC_Database->query('delete from :table_content_links where links_id = :links_id');
            $Qad->bindTable(':table_content_links', TABLE_CONTENT_LINKS);
            $Qad->bindInt(':links_id', $id);
            $Qad->setLogging($_SESSION['module'], $id);
            $Qad->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $osC_Database->commitTransaction();
                return true;
            }

            $osC_Database->rollbackTransaction();
            return false;
        }

        public static function setStatus($content_id, $flag, $content_type)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_content set content_status= :content_status, date_modified = now() where content_id = :content_id and content_type = :content_type');
            $Qstatus->bindInt(':content_status', $flag);
            $Qstatus->bindInt(':content_id', $content_id);
            $Qstatus->bindValue(':content_type', $content_type);
            $Qstatus->bindTable(':table_content', TABLE_CONTENT);
            $Qstatus->setLogging($_SESSION['module'], $content_id);
            $Qstatus->execute();
            return true;
        }

        public static function setDocumentStatus($id, $flag)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_content_documents set documents_status= :documents_status, documents_last_modified = now() where documents_id = :documents_id');
            $Qstatus->bindInt(':documents_status', $flag);
            $Qstatus->bindInt(':documents_id', $id);
            $Qstatus->bindTable(':table_content_documents', TABLE_CONTENT_DOCUMENTS);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();
            return true;
        }

        public static function setCommentStatus($id, $flag)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_content_comments set comments_status= :comments_status where comments_id = :comments_id');
            $Qstatus->bindInt(':comments_status', $flag);
            $Qstatus->bindInt(':comments_id', $id);
            $Qstatus->bindTable(':table_content_comments', TABLE_CONTENT_COMMENTS);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();
            return true;
        }

        public static function setLinkStatus($id, $flag)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_content_links set comments_status= :comments_status where links_id = :links_id');
            $Qstatus->bindInt(':links_status', $flag);
            $Qstatus->bindInt(':links_id', $id);
            $Qstatus->bindTable(':table_content_links', TABLE_CONTENT_LINKS);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();
            return true;
        }

        public static function getPermissions($content_id, $content_type, $roles_id = null)
        {
            global $osC_Database;
            $Qpermissions = $osC_Database->query('select p.* from :table_permissions p where content_id = :content_id and content_type = :content_type');
            $Qpermissions->bindTable(':table_permissions', TABLE_CONTENT_PERMISSIONS);
            $Qpermissions->bindInt(':content_id', $content_id);
            $Qpermissions->bindValue(':content_type', $content_type);
            $Qpermissions->execute();

            $records = array();
            while ($Qpermissions->next()) {
                $records[] = array(
                    'can_read' => $Qpermissions->value('can_read'),
                    'can_write' => $Qpermissions->value('can_write'),
                    'can_modify' => $Qpermissions->value('can_modify'),
                    'can_publish' => $Qpermissions->value('can_publish')
                );
            }
            $Qpermissions->freeResult();

            $recs = array();
            $roles = array();

            if ($roles_id != null) {
                $roles[] = osC_Roles_Admin::getRole($roles_id);
            }
            else
            {
                $username = $_SESSION['admin']['username'];
                $id = $_SESSION['admin']['id'];

                if($username == 'admin')
                {
                    $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin " ';
                }
                else
                {
                    $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" and a.id in (select administrators_id from :table_users_roles where roles_id in (select roles_id from :table_customers where administrators_id = :administrators_id))';
                }

                if(isset($_REQUEST['customers_id']))
                {
                    $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" and a.id in (select administrators_id from :table_users_roles where roles_id in (select roles_id from :table_customers where customers_id = :customers_id))';
                }

                //$query = "select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name";
                $Qroles = $osC_Database->query($query);
                $Qroles->bindTable(':tables_users', TABLE_USERS);
                $Qroles->bindTable(':table_administrators', TABLE_ADMINISTRATORS);

                if($username != 'admin')
                {
                    $Qroles->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                    $Qroles->bindTable(':table_customers', TABLE_CUSTOMERS);
                    $Qroles->bindInt(':administrators_id', $id);
                }

                if(isset($_REQUEST['customers_id']))
                {
                    $Qroles->bindInt(':customers_id',$_REQUEST['customers_id']);
                }

                $Qroles->execute();
                //var_dump($Qroles);

                $roles[] = array(
                    'roles_id' => '-1',
                    'user_name' => 'everyone',
                    'email_address' => 'everyone',
                    'roles_name' => 'Tout le monde',
                    'roles_description' => 'Tout le monde',
                    'icon' => osc_icon('folder_account.png')
                );

                while ($Qroles->next()) {
                    $roles[] = array(
                        'roles_id' => $Qroles->valueInt('administrators_id'),
                        'user_name' => $Qroles->value('user_name'),
                        'email_address' => $Qroles->value('email_address'),
                        'roles_name' => $Qroles->value('user_name'),
                        'roles_description' => $Qroles->value('description'),
                        'icon' => osc_icon('folder_account.png')
                    );
                }
                $Qroles->freeResult();
            }

            if (count($records) > 0) {
                $permissions = $records[0];
            }
            else
            {
                $permissions = array(
                    'can_read' => '',
                    'can_write' => '',
                    'can_modify' => '',
                    'can_publish' => ''
                );
            }
            if (is_array($permissions)) {
                $read_permissions = explode(';', $permissions['can_read']);
                $write_permissions = explode(';', $permissions['can_write']);
                $modify_permissions = explode(';', $permissions['can_modify']);
                $publish_permissions = explode(';', $permissions['can_publish']);

                foreach ($roles as $role) {
                    if (is_array($read_permissions) && in_array($role['roles_id'], $read_permissions)) {
                        $role['can_read'] = '1';
                    }
                    else
                    {
                        $role['can_read'] = '0';
                    }

                    if (is_array($write_permissions) && in_array($role['roles_id'], $write_permissions)) {
                        $role['can_write'] = '1';
                    }
                    else
                    {
                        $role['can_write'] = '0';
                    }

                    if (is_array($modify_permissions) && in_array($role['roles_id'], $modify_permissions)) {
                        $role['can_modify'] = '1';
                    }
                    else
                    {
                        $role['can_modify'] = '0';
                    }

                    if (is_array($publish_permissions) && in_array($role['roles_id'], $publish_permissions)) {
                        $role['can_publish'] = '1';
                    }
                    else
                    {
                        $role['can_publish'] = '0';
                    }

                    $role['content_id'] = $content_id;
                    $role['content_type'] = $content_type;
                    $recs[] = $role;
                }
            }

            return $recs;
        }

        public static function getNotifications($content_id, $content_type,$events,$roles_id = null)
        {
            global $osC_Database;
            $Qnotifications = $osC_Database->query('select * from delta_asset_notifications n where content_id = :content_id and content_type = :content_type and event = :event');
            $Qnotifications->bindInt(':content_id', $content_id);
            $Qnotifications->bindValue(':event', $events);
            $Qnotifications->bindValue(':content_type', $content_type);
            $Qnotifications->execute();

            $records = array();
            while ($Qnotifications->next()) {
                $records[] = array(
                    'notify' => $Qnotifications->value('notify')
                );
            }
            $Qnotifications->freeResult();

            $recs = array();
            $roles = array();

            if ($roles_id != null) {
                $roles[] = osC_Roles_Admin::getRole($roles_id);
            }
            else
            {
                $username = $_SESSION['admin']['username'];
                $id = $_SESSION['admin']['id'];

                if($username == 'admin')
                {
                    $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin " ';
                }
                else
                {
                    $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" and a.id in (select administrators_id from :table_users_roles where roles_id in (select roles_id from :table_customers where administrators_id = :administrators_id))';
                }

                if(isset($_REQUEST['customers_id']))
                {
                    $query = 'SELECT u.*, a.* FROM :tables_users u INNER JOIN :table_administrators a ON (u.administrators_id = a.id) where a.user_name != "admin" and a.id in (select administrators_id from :table_users_roles where roles_id in (select roles_id from :table_customers where customers_id = :customers_id))';
                }

                //$query = "select r.*,a.* from :table_roles r INNER JOIN :table_administrators a ON (r.administrators_id = a.id) order by r.roles_name";
                $Qroles = $osC_Database->query($query);
                $Qroles->bindTable(':tables_users', TABLE_USERS);
                $Qroles->bindTable(':table_administrators', TABLE_ADMINISTRATORS);

                if($username != 'admin')
                {
                    $Qroles->bindTable(':table_users_roles', TABLE_USERS_ROLES);
                    $Qroles->bindTable(':table_customers', TABLE_CUSTOMERS);
                    $Qroles->bindInt(':administrators_id', $id);
                }

                if(isset($_REQUEST['customers_id']))
                {
                    $Qroles->bindInt(':customers_id',$_REQUEST['customers_id']);
                }

                $Qroles->execute();
                //var_dump($Qroles);

                while ($Qroles->next()) {
                    $roles[] = array(
                        'roles_id' => $Qroles->valueInt('administrators_id'),
                        'user_name' => $Qroles->value('user_name'),
                        'email_address' => $Qroles->value('email_address'),
                        'roles_name' => $Qroles->value('user_name'),
                        'roles_description' => $Qroles->value('description'),
                        'icon' => osc_icon('folder_account.png')
                    );
                }
                $Qroles->freeResult();
            }

            if (count($records) > 0) {
                $notifications = $records[0];
            }
            else
            {
                $notifications = array(
                    'notify' => ''
                );
            }
            if (is_array($notifications)) {
                $notify = explode(';', $notifications['notify']);

                foreach ($roles as $role) {
                    if (is_array($notify) && in_array($role['roles_id'], $notify)) {
                        $role['notify'] = '1';
                    }
                    else
                    {
                        $role['notify'] = '0';
                    }

                    $role['content_id'] = $content_id;
                    $role['content_type'] = $content_type;
                    $recs[] = $role;
                }
            }

            return $recs;
        }

        public static function getContentPermissions($content_id, $content_type)
        {
            global $osC_Database;

            $Qpermissions = $osC_Database->query('select p.* from :table_permissions p where content_id = :content_id and content_type = :content_type');
            $Qpermissions->bindTable(':table_permissions', TABLE_CONTENT_PERMISSIONS);
            $Qpermissions->bindInt(':content_id', $content_id);
            $Qpermissions->bindValue(':content_type', $content_type);
            $Qpermissions->execute();

            $records = array();
            while ($Qpermissions->next()) {
                $records[] = array(
                    'can_read' => $Qpermissions->value('can_read'),
                    'can_write' => $Qpermissions->value('can_write'),
                    'can_modify' => $Qpermissions->value('can_modify'),
                    'can_publish' => $Qpermissions->value('can_publish'),
                    'is_set' => true
                );
            }
            $Qpermissions->freeResult();

            if (count($records) > 0) {
                $permissions = $records[0];
            }
            else
            {
                $permissions = array(
                    'can_read' => '',
                    'can_write' => '',
                    'can_modify' => '',
                    'can_publish' => '',
                    'is_set' => false
                );
            }

            return $permissions;
        }

        public static function getContentNotifications($content_id, $content_type,$event)
        {
            global $osC_Database;

            $Qpermissions = $osC_Database->query('select p.* from delta_asset_notifications p where content_id = :content_id and content_type = :content_type and event = :event');
            $Qpermissions->bindInt(':content_id', $content_id);
            $Qpermissions->bindValue(':content_type', $content_type);
            $Qpermissions->bindValue(':event', $event);
            $Qpermissions->execute();

            $records = array();
            while ($Qpermissions->next()) {
                $records[] = array(
                    'notify' => $Qpermissions->value('notify'),
                    'is_set' => true
                );
            }
            $Qpermissions->freeResult();

            if (count($records) > 0) {
                $permissions = $records[0];
            }
            else
            {
                $permissions = array(
                    'notify' => '',
                    'is_set' => false
                );
            }

            return $permissions;
        }

        public static function setPermission($content_id, $content_type, $permission, $roles_id, $flag)
        {
            global $osC_Database;

            $permissions = content::getContentPermissions($content_id, $content_type);

            if (array_key_exists($permission, $permissions)) {
                $roles = explode(';', $permissions[$permission]);
                $new_roles = $permissions[$permission];
                if (in_array($roles_id, $roles) && $flag == '1') {
                    //nothing to do....
                }

                if (in_array($roles_id, $roles) && $flag == '0') {
                    $new_roles = '';
                    foreach ($roles as $role) {
                        if ($role != $roles_id) {
                            $new_roles = $new_roles . ';' . $role;
                        }
                    }
                }

                if (!in_array($roles_id, $roles) && $flag == '1') {
                    $new_roles = $new_roles . $roles_id . ';';
                }

                if (!in_array($roles_id, $roles) && $flag == '0') {
                    //nothing to do....
                }

                if ($permissions['is_set'] == true) {
                    $Qpermission = $osC_Database->query('update :table_categories_permissions set :permission = :roles where content_id = :content_id and content_type = :content_type');
                }
                else
                {
                    $Qpermission = $osC_Database->query('insert into :table_categories_permissions (content_id,content_type,:permission) values (:content_id,:content_type,:roles)');
                }

                $roles = explode(';', $new_roles);
                $new_roles = '';
                $set_roles = array();

                foreach ($roles as $id) {
                    if ($id != '' || $id == '-1') {
                        if (!in_array($id, $set_roles)) {
                            $new_roles = $new_roles . $id . ';';
                            $set_roles[] = $id;
                        }
                    }
                }

                $Qpermission->bindTable(':table_categories_permissions', TABLE_CONTENT_PERMISSIONS);
                $Qpermission->bindTable(":permission", $permission);
                $Qpermission->bindValue(":roles", $new_roles);
                $Qpermission->bindInt(':content_id', $content_id);
                $Qpermission->bindValue(':content_type', $content_type);
                $Qpermission->execute();

                if (!$osC_Database->isError()) {
                    osC_Cache::clear('categories');
                    osC_Cache::clear('category_tree');

                    return true;
                }
            }

            return false;
        }

        function sendNotification($content_id,$content_type,$event,$subject,$body)
        {
            $permissions = content::getContentNotifications($content_id, $content_type,$event);
            //var_dump($event);
            //var_dump($permissions);
            $notify = 'notify';

            if (array_key_exists($notify, $permissions)) {
                $roles = explode(';', $permissions[$notify]);

                foreach ($roles as $role) {
                    $user = osC_Users_Admin::getUserInfos($role);
                    if(isset($user['email_address']))
                    {
                        $config = array();
                        $config['to'] = $user['email_address'];
                        $config['body'] = $body;
                        $config['subject'] = $subject;
                        osC_Categories_Admin::sendMail($config);
                    }
                }
            }
        }

        public static function setNotification($content_id, $content_type, $notify, $roles_id, $flag,$event)
        {
            global $osC_Database;

            $permissions = content::getContentNotifications($content_id, $content_type,$event);

            if (array_key_exists($notify, $permissions)) {
                $roles = explode(';', $permissions[$notify]);
                $new_roles = $permissions[$notify];
                if (in_array($roles_id, $roles) && $flag == '1') {
                    //nothing to do....
                }

                if (in_array($roles_id, $roles) && $flag == '0') {
                    $new_roles = '';
                    foreach ($roles as $role) {
                        if ($role != $roles_id) {
                            $new_roles = $new_roles . ';' . $role;
                        }
                    }
                }

                if (!in_array($roles_id, $roles) && $flag == '1') {
                    $new_roles = $new_roles . $roles_id . ';';
                }

                if (!in_array($roles_id, $roles) && $flag == '0') {
                    //nothing to do....
                }

                if ($permissions['is_set'] == true) {
                    $Qpermission = $osC_Database->query('update delta_asset_notifications set notify = :roles where content_id = :content_id and content_type = :content_type and event = :event');
                }
                else
                {
                    $Qpermission = $osC_Database->query('insert into delta_asset_notifications (content_id,content_type,notify,event) values (:content_id,:content_type,:roles,:event)');
                }

                $roles = explode(';', $new_roles);
                $new_roles = '';
                $set_roles = array();

                foreach ($roles as $id) {
                    if ($id != '' || $id == '-1') {
                        if (!in_array($id, $set_roles)) {
                            $new_roles = $new_roles . $id . ';';
                            $set_roles[] = $id;
                        }
                    }
                }

                $Qpermission->bindValue(":roles", $new_roles);
                $Qpermission->bindInt(':content_id', $content_id);
                $Qpermission->bindValue(':content_type', $content_type);
                $Qpermission->bindValue(':event', $event);
                $Qpermission->execute();

                if (!$osC_Database->isError()) {
                    return true;
                }

                $_SESSION['error'] = $osC_Database->getError();
            }

            return false;
        }
    }
