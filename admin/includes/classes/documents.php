<?php
/*
  $Id: documents.php $
  Mefobe Cart Solutions
  http://www.mefobemarket.com

  Copyright (c) 2009 Wuxi Elootec Technology Co., Ltd

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

    class toC_Documents_Admin
    {
        function getData($id)
        {
            global $osC_Database, $osC_Language;

            $Qdocuments = $osC_Database->query('select a.*, ad.* from :table_documents a, :table_documents_description ad where a.documents_id = :documents_id and a.documents_id =ad.documents_id and ad.languages_id = :language_id');

            $Qdocuments->bindTable(':table_documents', TABLE_DOCUMENTS);
            $Qdocuments->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
            $Qdocuments->bindInt(':documents_id', $id);
            $Qdocuments->bindInt(':language_id', $osC_Language->getID());
            $Qdocuments->execute();

            $data = $Qdocuments->toArray();
            $data['html'] = '<a href="../cache/documents/' . $data['filename'] . '" target="_blank">' . $data['filename'] . '</a>';

            $Qdocuments->freeResult();

            return $data;
        }

        function setStatus($id, $flag)
        {
            global $osC_Database;
            $Qstatus = $osC_Database->query('update :table_documents set documents_status= :documents_status, documents_last_modified = now() where documents_id = :documents_id');
            $Qstatus->bindInt(':documents_status', $flag);
            $Qstatus->bindInt(':documents_id', $id);
            $Qstatus->bindTable(':table_documents', TABLE_DOCUMENTS);
            $Qstatus->setLogging($_SESSION['module'], $id);
            $Qstatus->execute();
            return true;
        }

        function save($id = null, $data)
        {
            global $osC_Database, $osC_Language;

            $osC_Database->startTransaction();
            $error = false;
            if ($data['documents_file']) {
                $file = new upload($data['documents_file']);

                if ($file->exists()) {
                    //remove old attachment file
                    if (is_numeric($id)) {
                        $Qfile = $osC_Database->query('select cache_filename from :table_documents where documents_id = :id');
                        $Qfile->bindTable(':table_documents', TABLE_DOCUMENTS);
                        $Qfile->bindInt(':id', $id);
                        $Qfile->execute();

                        if ($Qfile->numberOfRows() == 1) {
                            $file = DIR_FS_CACHE . 'documents/' . $Qfile->value('cache_filename');
                            if(file_exists($file))
                            {
                                @unlink($file);
                            }
                        }
                    }

                    $file->set_destination(realpath(DIR_FS_CACHE . '/documents'));

                    if ($file->parse() && $file->save()) {
                        $filename = $file->filename;

                        //$url = toc_format_friendly_url($data['documents_name']);

                        //$cache_filename = md5($filename . time());
                        $cache_filename = $filename;
                        $cache_filename = str_replace(' ', "_",$cache_filename);
                        $cache_filename = str_replace("-", "_",$cache_filename);

                        @rename(DIR_FS_CACHE . 'documents/' . $file->filename, DIR_FS_CACHE . '/documents/' . $cache_filename);

                        if (is_numeric($id)) {
                            $Qdocument = $osC_Database->query('update :table_documents set documents_status = :documents_status,documents_categories_id = :documents_categories_id,documents_last_modified = now() where documents_id = :documents_id');
                            $Qdocument->bindInt(':documents_id', $id);
                        } else {
                            $Qdocument = $osC_Database->query('insert into :table_documents (documents_status,filename,cache_filename,documents_categories_id,documents_date_added) values (:documents_status,:filename,:cache_filename,:documents_categories_id ,:documents_date_added)');
                            $Qdocument->bindRaw(':documents_date_added', 'now()');
                        }

                        $Qdocument->bindTable(':table_documents', TABLE_DOCUMENTS);
                        $Qdocument->bindValue(':documents_status', $data['documents_status']);
                        $Qdocument->bindValue(':filename', $filename);
                        $Qdocument->bindValue(':cache_filename', $cache_filename);
                        $Qdocument->bindValue(':documents_categories_id', $data['documents_categories']);
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
            } else {
                if (is_numeric($id)) {
                    $documents_id = $id;
                } else {
                    $documents_id = $osC_Database->nextID();
                }
            }

            //Process Languages
            //
            if ($error === false) {
                foreach ($osC_Language->getAll() as $l) {
                    if (is_numeric($id)) {
                        $Qad = $osC_Database->query('update :table_documents_description set documents_name = :documents_name, documents_description = :documents_description where documents_id = :documents_id and languages_id = :language_id');
                    } else {
                        $Qad = $osC_Database->query('insert into :table_documents_description (documents_id, languages_id, documents_name, documents_description) values (:documents_id, :language_id, :documents_name, :documents_description)');
                    }

                    $Qad->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
                    $Qad->bindInt(':documents_id', $documents_id);
                    $Qad->bindInt(':language_id', $l['id']);
                    $Qad->bindValue(':documents_name', $data['documents_name'][$l['id']]);
                    $Qad->bindValue(':documents_description', $data['documents_description'][$l['id']]);
                    $Qad->setLogging($_SESSION['module'], $documents_id);
                    $Qad->execute();

                    if ($osC_Database->isError()) {
                        $error = true;
                        break;
                    }
                }
            }

            if ($error === false) {
                $osC_Database->commitTransaction();

                osC_Cache::clear('sefu-documents');
                return true;
            }

            $osC_Database->rollbackTransaction();

            return false;
        }


        function delete($id, $filename)
        {
            global $osC_Database;
            $error = false;

            $osC_Database->startTransaction();

            $Qad = $osC_Database->query('delete from :table_documents_description where documents_id = :documents_id');
            $Qad->bindTable(':table_documents_description', TABLE_DOCUMENTS_DESCRIPTION);
            $Qad->bindInt(':documents_id', $id);
            $Qad->setLogging($_SESSION['module'], $id);
            $Qad->execute();

            if ($osC_Database->isError()) {
                $error = true;
            }

            if ($error === false) {
                $Qdocuments = $osC_Database->query('delete from :table_documents where documents_id = :documents_id');
                $Qdocuments->bindTable(':table_documents', TABLE_DOCUMENTS);
                $Qdocuments->bindInt(':documents_id', $id);
                $Qdocuments->setLogging($_SESSION['module'], $id);
                $Qdocuments->execute();

                if ($osC_Database->isError()) {
                    $error = true;
                }

                if ($error === false) {
                    $osC_Database->commitTransaction();

                    @unlink(DIR_FS_CACHE . 'documents/' . $filename);
                    osC_Cache::clear('sefu-documents');
                    return true;
                }
            }
            $osC_Database->rollbackTransaction();
            return false;
        }
    }

?>
